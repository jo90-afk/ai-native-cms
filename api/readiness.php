<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';
require_once __DIR__.'/content-authority.php';
require_once dirname(__DIR__).'/database/bootstrap-core.php';

/** Read-only portable readiness checks plus bounded adopter-owned host adapters. */

function readinessCheck(string $id,string $label,string $status,string $message,bool $blocking=true,string $scope='core'): array {
    if(!in_array($status,['pass','warn','fail'],true))$status='fail';
    return ['id'=>substr($id,0,191),'label'=>substr($label,0,191),'status'=>$status,'message'=>substr($message,0,1000),'blocking'=>$blocking,'scope'=>substr($scope,0,64)];
}
function readinessSafeRelativePath(string $path): ?string {
    $path=trim(str_replace('\\','/',$path));if($path===''||str_starts_with($path,'/')||str_contains($path,"\0"))return null;foreach(explode('/',$path) as $part)if($part===''||$part==='.'||$part==='..')return null;return $path;
}
function readinessWritableTarget(string $root,string $path,bool $mustExist=false): bool {
    $path=readinessSafeRelativePath($path);if($path===null)return false;$full=rtrim($root,'/\\').'/'.$path;
    if(file_exists($full)){if(!pathInside($full,$root))return false;return is_writable($full);}
    if($mustExist)return false;$parent=dirname($full);while(!file_exists($parent)&&dirname($parent)!==$parent)$parent=dirname($parent);return is_dir($parent)&&pathInside($parent,$root)&&is_writable($parent);
}
function readinessFilesystemChecks(string $root): array {
    $checks=[];$pages=cmsConfiguredPages($root);$pageFailures=[];foreach($pages as $path=>$label)if(!readinessWritableTarget($root,$path,true))$pageFailures[]=$path;
    $checks[]=readinessCheck('filesystem.repository-pages','Repository page projection','pass','All configured repository pages are writable inside the public root.');
    if($pageFailures)$checks[array_key_last($checks)]=readinessCheck('filesystem.repository-pages','Repository page projection','fail',count($pageFailures).' configured repository page(s) are missing, outside the public root, or not writable.');
    $outputs=siteConfigValue('projection','outputs',[]);$outputFailures=[];if(is_array($outputs))foreach($outputs as $path)if(!readinessWritableTarget($root,(string)$path,false))$outputFailures[]=(string)$path;
    $checks[]=readinessCheck('filesystem.projection-outputs','Generated projection targets',$outputFailures?'fail':'pass',$outputFailures?count($outputFailures).' configured projection target(s) cannot be written safely.':'Configured projection targets have a writable in-root file or parent directory.');
    $uploadRoot=trim(str_replace('\\','/',(string)siteConfigValue('media','upload_root','assets/uploads')),'/');$uploadOk=$uploadRoot!==''&&readinessWritableTarget($root,$uploadRoot.'/readiness-probe.png',false);
    $checks[]=readinessCheck('filesystem.media-upload','Media upload root',$uploadOk?'pass':'fail',$uploadOk?'The configured media upload root has a writable in-root directory or parent.':'The configured media upload root cannot be created or written safely.');
    $stylesheet=trim((string)siteConfigValue('branding','stylesheet',''));if($stylesheet!==''){$ok=readinessWritableTarget($root,$stylesheet,true);$checks[]=readinessCheck('filesystem.branding-stylesheet','Branding stylesheet',$ok?'pass':'fail',$ok?'The configured branding stylesheet exists and is writable.':'The configured branding stylesheet is missing, outside the public root, or not writable.');}
    return $checks;
}
function readinessDatabaseChecks(string $root): array {
    $checks=[];
    if(!extension_loaded('pdo_mysql'))return [readinessCheck('database.driver','PDO MySQL driver','fail','PHP extension pdo_mysql is required before database readiness can be checked.')];
    if(!dbConfigured())return [readinessCheck('database.configuration','Database configuration','fail','Database credentials are not configured. Configure AINCMS_DB_* values outside the public root.')];
    $checks[]=readinessCheck('database.configuration','Database configuration','pass','Required database connection values are present.');
    try{$pdo=db();$pdo->query('SELECT 1')->fetchColumn();$checks[]=readinessCheck('database.connection','Database connection','pass','MySQL accepted a bounded application connection.');}
    catch(Throwable $e){$checks[]=readinessCheck('database.connection','Database connection','fail','MySQL connection failed. Review the private database configuration and server availability.');return $checks;}
    try{$state=bootstrapState($pdo,$root);$status=(string)$state['status'];
        if($status==='ready')$checks[]=readinessCheck('database.bootstrap','Schema and owner bootstrap','pass','Database schema '.$state['schemaVersion'].' is current and at least one CMS owner exists.');
        elseif($status==='empty')$checks[]=readinessCheck('database.bootstrap','Schema and owner bootstrap','fail','The database is empty. Run php database/bootstrap.php.');
        elseif($status==='foreign')$checks[]=readinessCheck('database.bootstrap','Schema and owner bootstrap','fail','The configured database is non-empty but does not look like AI Native CMS. Use a dedicated database.');
        else $checks[]=readinessCheck('database.bootstrap','Schema and owner bootstrap','fail','The database is a partial AI Native CMS installation. Inspect it before using database/bootstrap.php --repair.');
        $schemaOk=(int)$state['schemaVersion']>=(int)$state['expectedSchemaVersion'];$checks[]=readinessCheck('database.schema','Schema version',$schemaOk?'pass':'fail',$schemaOk?'Database schema matches or exceeds the repository schema version.':'Database schema is older than the repository schema version.');
        $owners=(int)$state['ownerCount'];$checks[]=readinessCheck('database.owner','Persisted CMS owner',$owners>0?'pass':'fail',$owners>0?'At least one CMS owner is persisted in MySQL.':'No CMS owner is persisted yet. Run database/bootstrap.php after configuring bootstrap credentials.');
    }catch(Throwable $e){$checks[]=readinessCheck('database.bootstrap','Schema and owner bootstrap','fail','Database structure could not be classified safely.');return $checks;}
    $cfg=dbConfig();
    if(!$cfg['remote'])$checks[]=readinessCheck('database.transport','Database transport','pass','Database uses localhost or a Unix socket; remote TLS is not required by the CMS.');
    else{
        $cipher='';try{$stmt=$pdo->query("SHOW STATUS LIKE 'Ssl_cipher'");$row=$stmt->fetch();if(is_array($row))$cipher=(string)($row['Value']??array_values($row)[1]??'');}catch(Throwable $e){}
        if($cfg['requireTls'])$checks[]=readinessCheck('database.transport','Database transport',$cipher!==''?'pass':'fail',$cipher!==''?'Remote MySQL reports an active TLS cipher.':'Remote MySQL is configured as TLS-required but no active TLS cipher was reported.');
        else $checks[]=readinessCheck('database.transport','Database transport','warn','Remote MySQL is using an explicitly allowed insecure-network exception.',false);
    }
    try{$rows=$pdo->query('SHOW GRANTS FOR CURRENT_USER')->fetchAll(PDO::FETCH_NUM);$checks[]=readinessCheck('database.grants','Database grants visibility','pass','Current-user grants are readable ('.count($rows).' grant row(s)); grant contents are not exposed by readiness.');}
    catch(Throwable $e){$checks[]=readinessCheck('database.grants','Database grants visibility','warn','The runtime user cannot read its grant metadata; verify least privilege separately.',false);}
    try{$authority=contentAuthorityStatus($root);$ready=(bool)($authority['ready']??false);$checks[]=readinessCheck('content.authority','Canonical content authority',$ready?'pass':'fail',$ready?'Configured repository page sources are initialized in canonical SQL.':'Canonical repository content is not fully initialized. Run php database/reconcile.php initial-import.');}
    catch(Throwable $e){$checks[]=readinessCheck('content.authority','Canonical content authority','fail','Canonical content authority could not be inspected.');}
    return $checks;
}
function readinessCoreChecks(string $root): array {
    $checks=[];$phpOk=version_compare(PHP_VERSION,'8.1.0','>=');$checks[]=readinessCheck('runtime.php','PHP runtime',$phpOk?'pass':'fail',$phpOk?'PHP '.PHP_VERSION.' satisfies the 8.1+ runtime requirement.':'PHP 8.1 or newer is required.');
    $siteConfig=is_file(rtrim($root,'/\\').'/config/site.php');$checks[]=readinessCheck('config.site','Adopter site configuration',$siteConfig?'pass':'fail',$siteConfig?'config/site.php is present.':'Create config/site.php from config/site.example.php before production use.');
    $origin=publicOrigin();$originOk=$origin!==''&&(runtimeIsDevelopment()||str_starts_with($origin,'https://'));$checks[]=readinessCheck('config.origin','Public origin',$originOk?'pass':'fail',$originOk?'A valid '.(str_starts_with($origin,'https://')?'HTTPS ':'').'public origin is configured.':'Production requires a valid HTTPS public origin.');
    $rate=siteSecret('AINCMS_RATE_LIMIT_SECRET');$rateStatus=$rate===''?'fail':(strlen($rate)<32?'warn':'pass');$checks[]=readinessCheck('config.rate-secret','Rate-limit secret',$rateStatus,$rate===''?'AINCMS_RATE_LIMIT_SECRET is required.':(strlen($rate)<32?'The rate-limit secret is configured but shorter than the recommended 32 characters.':'A rate-limit secret is configured.'),$rateStatus==='fail');
    if(runtimeIsDevelopment())$checks[]=readinessCheck('runtime.environment','Runtime environment','warn','AINCMS_ENV is development; production hardening should be verified under production mode.',false);else $checks[]=readinessCheck('runtime.environment','Runtime environment','pass','Runtime is using production-mode security defaults.');
    if(siteSecret('AINCMS_TRUST_PROXY_HEADERS','0')==='1')$checks[]=readinessCheck('runtime.proxy-trust','Proxy header trust','warn','Proxy header trust is enabled. Confirm the trusted proxy overwrites client-supplied forwarding headers.',false);
    foreach(readinessDatabaseChecks($root) as $check)$checks[]=$check;foreach(readinessFilesystemChecks($root) as $check)$checks[]=$check;
    try{$owners=0;if(dbConfigured()&&extension_loaded('pdo_mysql')){$pdo=db();$tables=bootstrapExistingTables($pdo);$owners=bootstrapOwnerCount($pdo,$tables);}if($owners>0&&siteSecret('AINCMS_CMS_PASSWORD_HASH')!=='')$checks[]=readinessCheck('security.bootstrap-hash','Bootstrap password hash','warn','A persisted owner exists while the bootstrap password hash remains configured. Removing the initializer after verifying login reduces recovery credential exposure.',false);}
    catch(Throwable $e){}
    return $checks;
}
function readinessAdapters(string $root): array {
    $configured=siteConfigValue('readiness','adapters',[]);if(!is_array($configured))return [];$out=[];
    foreach($configured as $item){if(!is_array($item))continue;$id=trim((string)($item['id']??''));$script=str_replace('\\','/',trim((string)($item['script']??'')));$callable=trim((string)($item['callable']??''));
        if(!preg_match('/^[A-Za-z0-9._:-]{2,191}$/',$id)||$script===''||str_starts_with($script,'/')||!preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\:]*$/',$callable))throw new RuntimeException('Invalid readiness adapter configuration.');foreach(explode('/',$script) as $part)if($part===''||$part==='.'||$part==='..')throw new RuntimeException('Readiness adapter path is invalid.');$full=realpath(rtrim($root,'/\\').'/'.$script);if($full===false||!is_file($full)||!pathInside($full,$root))throw new RuntimeException('Readiness adapter script is unavailable or outside the site root.');$out[]=['id'=>$id,'path'=>$full,'callable'=>$callable];
    }return $out;
}
function readinessAdapterChecks(string $root,array $core): array {
    $out=[];
    try{$adapters=readinessAdapters($root);}catch(Throwable $e){return [readinessCheck('adapter.configuration','Host readiness adapters','fail','One or more readiness adapters are configured unsafely or cannot be resolved.',true,'adapter')];}
    foreach($adapters as $adapter){
        try{require_once $adapter['path'];if(!is_callable($adapter['callable']))throw new RuntimeException('Adapter callable is unavailable.');$result=call_user_func($adapter['callable'],$root,['core'=>$core]);if(!is_array($result))throw new RuntimeException('Adapter must return an array of checks.');
            foreach(array_slice($result,0,50) as $i=>$check){if(!is_array($check))continue;$status=(string)($check['status']??'fail');$id='adapter.'.$adapter['id'].'.'.preg_replace('/[^A-Za-z0-9._:-]+/','-',(string)($check['id']??($i+1)));$out[]=readinessCheck($id,(string)($check['label']??$adapter['id']),$status,(string)($check['message']??''),!array_key_exists('blocking',$check)||(bool)$check['blocking'],'adapter');}
        }catch(Throwable $e){$out[]=readinessCheck('adapter.'.$adapter['id'],$adapter['id'],'fail','The configured host readiness adapter could not complete.',true,'adapter');}
    }return $out;
}
function readinessReport(string $root): array {
    $checks=readinessCoreChecks($root);foreach(readinessAdapterChecks($root,$checks) as $check)$checks[]=$check;$blockingFailures=0;$warnings=0;$passes=0;
    foreach($checks as $check){if($check['status']==='pass')$passes++;elseif($check['status']==='warn')$warnings++;elseif($check['blocking'])$blockingFailures++;}
    return ['ready'=>$blockingFailures===0,'generatedAt'=>gmdate('c'),'summary'=>['pass'=>$passes,'warn'=>$warnings,'blockingFailures'=>$blockingFailures,'total'=>count($checks)],'checks'=>$checks];
}
