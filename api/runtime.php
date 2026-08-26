<?php
declare(strict_types=1);

/** Shared HTTP/security boundaries and MySQL-backed CMS runtime state. */
require_once __DIR__ . '/database.php';

function runtimeRoot(): string { return dirname(__DIR__); }
function runtimeIsDevelopment(): bool { return strtolower(trim(siteSecret('AINCMS_ENV','production'))) === 'development'; }

function requestIsHttps(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if (siteSecret('AINCMS_TRUST_PROXY_HEADERS','0') === '1') {
        return strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))) === 'https';
    }
    return false;
}

function publicOrigin(): string {
    $configured=trim(siteSecret('AINCMS_PUBLIC_ORIGIN'));
    if($configured==='') $configured=trim((string)siteConfigValue('site','base_url',''));
    if($configured==='') return '';
    $parts=parse_url($configured);
    if(!is_array($parts)||empty($parts['scheme'])||empty($parts['host'])) return '';
    $scheme=strtolower((string)$parts['scheme']);
    if(!in_array($scheme,['http','https'],true)) return '';
    $origin=$scheme.'://'.strtolower((string)$parts['host']);
    if(isset($parts['port'])) $origin.=':'.(int)$parts['port'];
    return $origin;
}

function normalizedOrigin(string $url): string {
    $parts=parse_url(trim($url));
    if(!is_array($parts)||empty($parts['scheme'])||empty($parts['host'])) return '';
    $scheme=strtolower((string)$parts['scheme']);
    if(!in_array($scheme,['http','https'],true)) return '';
    $origin=$scheme.'://'.strtolower((string)$parts['host']);
    if(isset($parts['port'])) $origin.=':'.(int)$parts['port'];
    return $origin;
}

function applyTransportHeaders(): void {
    if(requestIsHttps()) header('Strict-Transport-Security: max-age=31536000');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-Permitted-Cross-Domain-Policies: none');
}

function secureJsonHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
    header('Cross-Origin-Resource-Policy: same-origin');
    applyTransportHeaders();
}
function secureCmsHeaders(): void {
    header('Cache-Control: no-store, private');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self'; script-src-attr 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src 'self'; media-src 'self'; worker-src 'self'");
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    applyTransportHeaders();
}
function runtimeJson(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function runtimeError(Throwable $e,string $publicMessage='The request could not be completed.',int $status=500): never {
    $incident=bin2hex(random_bytes(6));
    error_log('[ai-native-cms '.$incident.'] '.get_class($e).': '.$e->getMessage());
    $payload=['ok'=>false,'error'=>$publicMessage,'incident'=>$incident];
    if(runtimeIsDevelopment() && siteSecret('AINCMS_DEBUG_ERRORS','0')==='1') $payload['debug']=$e->getMessage();
    runtimeJson($payload,$status);
}

function cmsHttpsRequired(): bool { return !runtimeIsDevelopment() || siteSecret('AINCMS_CMS_REQUIRE_HTTPS','1') !== '0'; }
function enforceCmsHttps(bool $json=false): void {
    if(!cmsHttpsRequired() || requestIsHttps()) return;
    if($json){ secureJsonHeaders(); runtimeJson(['ok'=>false,'error'=>'HTTPS is required for CMS access.'],403); }
    $origin=publicOrigin();
    if(str_starts_with($origin,'https://')){
        $uri=(string)($_SERVER['REQUEST_URI']??'/cms/');
        if(!str_starts_with($uri,'/')) $uri='/cms/';
        header('Location: '.$origin.$uri, true, 302); exit;
    }
    secureCmsHeaders(); http_response_code(403); echo 'HTTPS is required for CMS access.'; exit;
}

function requireSameOrigin(bool $requireSignal = true): void {
    $fetchSite=strtolower(trim((string)($_SERVER['HTTP_SEC_FETCH_SITE']??'')));
    if($fetchSite!=='' && !in_array($fetchSite,['same-origin','none'],true)) runtimeJson(['ok'=>false,'error'=>'Cross-origin request rejected.'],403);
    $expected=publicOrigin();
    $origin=trim((string)($_SERVER['HTTP_ORIGIN']??''));
    $referer=trim((string)($_SERVER['HTTP_REFERER']??''));
    if($expected!==''){
        if($origin!=='' && !hash_equals($expected,normalizedOrigin($origin))) runtimeJson(['ok'=>false,'error'=>'Origin mismatch.'],403);
        if($origin==='' && $referer!=='' && !hash_equals($expected,normalizedOrigin($referer))) runtimeJson(['ok'=>false,'error'=>'Referrer mismatch.'],403);
    }
    $browserSignal=($fetchSite==='same-origin') || ($origin!=='' && ($expected==='' || hash_equals($expected,normalizedOrigin($origin)))) || ($referer!=='' && ($expected==='' || hash_equals($expected,normalizedOrigin($referer))));
    if($requireSignal && !$browserSignal && !(runtimeIsDevelopment() && siteSecret('AINCMS_ALLOW_ORIGINLESS_REQUESTS','0')==='1')) runtimeJson(['ok'=>false,'error'=>'Request origin could not be verified.'],403);
}
function requireJsonRequest(): void {
    $contentType=strtolower(trim((string)($_SERVER['CONTENT_TYPE']??'')));
    $mediaType=trim(explode(';',$contentType,2)[0]);
    if($mediaType!=='application/json') runtimeJson(['ok'=>false,'error'=>'This endpoint accepts application/json only.'],415);
}
function readJsonBody(int $maxBytes=262144): array {
    requireJsonRequest();
    $maxBytes=max(1024,$maxBytes);
    $declared=(int)($_SERVER['CONTENT_LENGTH']??0);
    if($declared>$maxBytes) runtimeJson(['ok'=>false,'error'=>'Request body is too large.'],413);
    $stream=fopen('php://input','rb');
    if($stream===false) runtimeJson(['ok'=>false,'error'=>'Request body could not be read.'],400);
    $raw=stream_get_contents($stream,$maxBytes+1);fclose($stream);
    if($raw===false) runtimeJson(['ok'=>false,'error'=>'Request body could not be read.'],400);
    if(strlen($raw)>$maxBytes) runtimeJson(['ok'=>false,'error'=>'Request body is too large.'],413);
    try { $decoded=json_decode($raw,true,512,JSON_THROW_ON_ERROR); }
    catch(JsonException $e){ runtimeJson(['ok'=>false,'error'=>'Invalid JSON payload.'],400); }
    if(!is_array($decoded)) runtimeJson(['ok'=>false,'error'=>'Invalid JSON payload.'],400);
    return $decoded;
}

function cmsExpectedSessionName(): string { return requestIsHttps()?'__Host-aincms':'aincms_dev'; }
function cmsSessionStart(): void {
    if(session_status()===PHP_SESSION_ACTIVE) return;
    enforceCmsHttps(false);
    $secure=requestIsHttps();
    ini_set('session.use_strict_mode','1');
    ini_set('session.use_only_cookies','1');
    ini_set('session.use_trans_sid','0');
    ini_set('session.cookie_httponly','1');
    ini_set('session.cookie_samesite','Strict');
    ini_set('session.gc_maxlifetime',(string)max(7200,(int)siteSecret('AINCMS_CMS_ABSOLUTE_TIMEOUT','43200')));
    session_cache_limiter('nocache');
    session_name(cmsExpectedSessionName());
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

function cmsPasswordAlgorithm(): string|int {
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
}
function cmsHashPassword(string $password): string {
    $hash=password_hash($password,cmsPasswordAlgorithm());
    if($hash===false) throw new RuntimeException('Password could not be secured.');
    return $hash;
}

function cmsValidUsername(string $username): bool { return preg_match('/^[A-Za-z0-9._-]{3,64}$/',$username)===1; }
function cmsBootstrapConfigured(): bool {
    return siteSecret('AINCMS_CMS_ENABLED')==='1' && trim(siteSecret('AINCMS_CMS_USER'))!=='' && trim(siteSecret('AINCMS_CMS_PASSWORD_HASH'))!=='';
}
function cmsUserFromRow(array $row): array {
    return [
        'id'=>(int)$row['id'],'username'=>(string)$row['username'],'passwordHash'=>(string)$row['password_hash'],
        'displayName'=>(string)$row['display_name'],'email'=>(string)($row['email']??''),'role'=>(string)$row['role'],
        'sessionVersion'=>(int)($row['session_version']??1),'createdAt'=>dbIso((string)$row['created_at']),'updatedAt'=>dbIso((string)($row['updated_at']??'')),
    ];
}
function cmsReadUser(?string $root=null,?string $username=null): ?array {
    if(!dbConfigured()) return null;
    try{
        dbRequireSchemaVersion(3);
        if($username!==null&&$username!==''){$stmt=db()->prepare('SELECT * FROM cms_users WHERE username=? LIMIT 1');$stmt->execute([$username]);}
        else{$stmt=db()->query("SELECT * FROM cms_users ORDER BY CASE WHEN role='Owner' THEN 0 ELSE 1 END,id ASC LIMIT 1");}
        $row=$stmt->fetch(); if(is_array($row)) return cmsUserFromRow($row);
        // The bootstrap identity is a one-time initializer, not a permanent fallback credential.
        if((int)db()->query('SELECT COUNT(*) FROM cms_users')->fetchColumn()>0) return null;
    }catch(Throwable $e){
        // Authentication fails closed when MySQL or the required schema is unavailable.
        return null;
    }
    if(!cmsBootstrapConfigured()) return null;
    $bootstrapUser=siteSecret('AINCMS_CMS_USER');
    if($username!==null&&$username!==''&&!hash_equals($bootstrapUser,$username)) return null;
    return [
        'id'=>0,
        'username'=>$bootstrapUser,
        'passwordHash'=>siteSecret('AINCMS_CMS_PASSWORD_HASH'),
        'displayName'=>(string)siteConfigValue('site','owner_display_name','Site Owner'),
        'email'=>'',
        'role'=>'Owner',
        'sessionVersion'=>1,
        'createdAt'=>gmdate('c'),
        'updatedAt'=>null,
        'source'=>'environment'
    ];
}
function cmsAuthConfigured(?string $root=null): bool {
    if(!dbConfigured()) return false;
    try{dbRequireSchemaVersion(3);if((int)db()->query('SELECT COUNT(*) FROM cms_users')->fetchColumn()>0)return true;}catch(Throwable $e){return false;}
    return cmsBootstrapConfigured();
}
function cmsWriteUser(array $user,?string $root=null): void {
    $username=trim((string)($user['username']??''));
    if(!cmsValidUsername($username)) throw new RuntimeException('CMS username is invalid.');
    $passwordHash=(string)($user['passwordHash']??''); if($passwordHash==='') throw new RuntimeException('CMS password hash is required.');
    $role=(string)($user['role']??'Owner'); if($role!=='Owner') throw new RuntimeException('Unsupported CMS role.');
    $stmt=db()->prepare('INSERT INTO cms_users (username,password_hash,display_name,email,role,session_version,created_at,updated_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),display_name=VALUES(display_name),email=VALUES(email),role=VALUES(role),session_version=GREATEST(session_version,VALUES(session_version)),updated_at=UTC_TIMESTAMP()');
    $stmt->execute([$username,$passwordHash,substr(trim((string)($user['displayName']??'')),0,191),substr(trim((string)($user['email']??'')),0,254),$role,max(1,(int)($user['sessionVersion']??1))]);
}
function cmsUpdatePasswordHash(int $userId,string $hash,bool $incrementVersion=false): int {
    if($userId<1) throw new RuntimeException('CMS user is unavailable.');
    $sql=$incrementVersion?'UPDATE cms_users SET password_hash=?,session_version=session_version+1,updated_at=UTC_TIMESTAMP() WHERE id=?':'UPDATE cms_users SET password_hash=?,updated_at=UTC_TIMESTAMP() WHERE id=?';
    $stmt=db()->prepare($sql);$stmt->execute([$hash,$userId]);
    $v=db()->prepare('SELECT session_version FROM cms_users WHERE id=?');$v->execute([$userId]);return max(1,(int)$v->fetchColumn());
}
function cmsAuthenticate(string $username,string $password,?string $root=null): bool {
    if(!cmsValidUsername($username)||$password===''||strlen($password)>1024) return false;
    $user=cmsReadUser($root,$username); if(!$user||($user['role']??'')!=='Owner') return false;
    $valid=hash_equals((string)$user['username'],$username)&&password_verify($password,(string)$user['passwordHash']); if(!$valid)return false;
    if(($user['source']??'')==='environment'){
        unset($user['source']);
        if(password_needs_rehash((string)$user['passwordHash'],cmsPasswordAlgorithm())) $user['passwordHash']=cmsHashPassword($password);
        cmsWriteUser($user,$root);$user=cmsReadUser($root,$username)??$user;
    }elseif(password_needs_rehash((string)$user['passwordHash'],cmsPasswordAlgorithm())){
        $user['passwordHash']=cmsHashPassword($password);cmsUpdatePasswordHash((int)$user['id'],$user['passwordHash'],false);
    }
    cmsSessionStart();session_regenerate_id(true);
    $_SESSION['cms_user']=(string)$user['username'];$_SESSION['cms_user_id']=(int)($user['id']??0);$_SESSION['cms_session_version']=(int)($user['sessionVersion']??1);
    $_SESSION['cms_login_at']=time();$_SESSION['cms_last_seen']=time();$_SESSION['cms_rotated_at']=time();$_SESSION['cms_csrf']=bin2hex(random_bytes(32));
    if(siteSecret('AINCMS_CMS_BIND_USER_AGENT','1')==='1') $_SESSION['cms_ua']=hash('sha256',(string)($_SERVER['HTTP_USER_AGENT']??''));
    cmsAudit('login','Authenticated CMS session',[],$root);return true;
}
function cmsDestroySession(): void {
    if(session_status()!==PHP_SESSION_ACTIVE) cmsSessionStart();
    $_SESSION=[];
    if(ini_get('session.use_cookies')){
        $p=session_get_cookie_params();
        setcookie(session_name(),'', ['expires'=>time()-42000,'path'=>$p['path']?:'/','secure'=>(bool)$p['secure'],'httponly'=>true,'samesite'=>'Strict']);
    }
    session_destroy();
}
function cmsCurrentUser(?string $root=null): ?array {
    // Do not create a server-side session for anonymous scanners or the initial login-status check.
    if(session_status()!==PHP_SESSION_ACTIVE && !isset($_COOKIE[cmsExpectedSessionName()])) return null;
    cmsSessionStart();$sessionUser=(string)($_SESSION['cms_user']??'');if($sessionUser==='')return null;
    $now=time();$last=(int)($_SESSION['cms_last_seen']??0);$login=(int)($_SESSION['cms_login_at']??0);
    $idle=max(300,(int)siteSecret('AINCMS_CMS_IDLE_TIMEOUT','7200'));$absolute=max($idle,(int)siteSecret('AINCMS_CMS_ABSOLUTE_TIMEOUT','43200'));
    if(($last>0&&$now-$last>$idle)||($login>0&&$now-$login>$absolute)){cmsDestroySession();return null;}
    if(siteSecret('AINCMS_CMS_BIND_USER_AGENT','1')==='1'){
        $expected=(string)($_SESSION['cms_ua']??'');$actual=hash('sha256',(string)($_SERVER['HTTP_USER_AGENT']??''));
        if($expected===''||!hash_equals($expected,$actual)){cmsDestroySession();return null;}
    }
    $user=cmsReadUser($root,$sessionUser);
    if(!$user||($user['role']??'')!=='Owner'||!hash_equals((string)$user['username'],$sessionUser)||(int)($user['sessionVersion']??1)!==(int)($_SESSION['cms_session_version']??0)){cmsDestroySession();return null;}
    if($now-(int)($_SESSION['cms_rotated_at']??0)>1800){session_regenerate_id(true);$_SESSION['cms_rotated_at']=$now;}
    $_SESSION['cms_last_seen']=$now;$copy=$user;unset($copy['passwordHash'],$copy['source']);return $copy;
}
function requireCmsAuth(bool $json=false): array {
    enforceCmsHttps($json);$user=cmsCurrentUser(runtimeRoot());if($user)return $user;
    if($json){secureJsonHeaders();runtimeJson(['ok'=>false,'error'=>'Authentication required.'],401);}secureCmsHeaders();header('Location: /cms/');exit;
}
function cmsCsrfToken(): string {cmsSessionStart();if(empty($_SESSION['cms_csrf']))$_SESSION['cms_csrf']=bin2hex(random_bytes(32));return (string)$_SESSION['cms_csrf'];}
function requireCmsCsrf(): void {cmsSessionStart();$expected=(string)($_SESSION['cms_csrf']??'');$got=(string)($_SERVER['HTTP_X_CMS_CSRF']??'');if($expected===''||$got===''||!hash_equals($expected,$got))runtimeJson(['ok'=>false,'error'=>'Security token mismatch. Refresh the CMS and try again.'],403);}
function cmsLogout(?string $root=null): void {if(cmsCurrentUser($root))cmsAudit('logout','CMS session ended',[],$root);cmsDestroySession();header('Clear-Site-Data: "cookies"');}
function cmsAudit(string $type,string $message,array $context=[],?string $root=null): void {try{$userId=(int)($_SESSION['cms_user_id']??0);$stmt=db()->prepare('INSERT INTO cms_activity (user_id,event_type,message,context_json,created_at) VALUES (NULLIF(?,0),?,?,?,UTC_TIMESTAMP())');$stmt->execute([$userId,substr($type,0,64),substr($message,0,500),dbJsonEncode($context)]);}catch(Throwable $e){error_log('[ai-native-cms audit] '.$e->getMessage());}}
function cmsRecentActivity(int $limit=20,?string $root=null): array {$limit=max(1,min(100,$limit));$stmt=db()->query('SELECT event_type,message,context_json,created_at FROM cms_activity ORDER BY id DESC LIMIT '.(int)$limit);$out=[];foreach($stmt->fetchAll() as $row)$out[]=['at'=>dbIso((string)$row['created_at']),'type'=>$row['event_type'],'message'=>$row['message'],'context'=>dbJsonDecode($row['context_json']??null)];return $out;}

function rateSecret(): string {$secret=siteSecret('AINCMS_RATE_LIMIT_SECRET');if($secret==='')throw new RuntimeException('Rate-limit secret is not configured.');return $secret;}
function rateKeyForValue(string $value): string {return hash_hmac('sha256',$value,rateSecret());}
function clientRateKey(): string {return rateKeyForValue((string)($_SERVER['REMOTE_ADDR']??'unknown'));}
function enforceRateLimitKey(string $bucket,string $key,int $limit,int $windowSeconds): void {
    $bucket=preg_replace('/[^a-z0-9_-]+/i','-',substr($bucket,0,80));$windowSeconds=max(1,$windowSeconds);$windowId=(int)floor(time()/$windowSeconds);$expires=gmdate('Y-m-d H:i:s',($windowId+1)*$windowSeconds);$pdo=db();
    $stmt=$pdo->prepare('INSERT INTO rate_limit_counters (bucket,rate_key,window_id,attempts,expires_at,updated_at) VALUES (?,?,?,1,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts=attempts+1,updated_at=UTC_TIMESTAMP()');$stmt->execute([$bucket,$key,$windowId,$expires]);
    $check=$pdo->prepare('SELECT attempts FROM rate_limit_counters WHERE bucket=? AND rate_key=? AND window_id=?');$check->execute([$bucket,$key,$windowId]);$attempts=(int)$check->fetchColumn();
    if(random_int(1,50)===1)$pdo->exec("DELETE FROM rate_limit_counters WHERE expires_at < UTC_TIMESTAMP() - INTERVAL 1 DAY");
    if($attempts>$limit){header('Retry-After: '.max(1,($windowId+1)*$windowSeconds-time()));runtimeJson(['ok'=>false,'error'=>'Too many requests. Try again later.'],429);}
}
function enforceRateLimit(string $bucket,int $limit,int $windowSeconds,?string $root=null): void {enforceRateLimitKey($bucket,clientRateKey(),$limit,$windowSeconds);}
