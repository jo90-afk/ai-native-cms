<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';

secureJsonHeaders();enforceCmsHttps(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET'){
        $user=cmsCurrentUser($root);runtimeJson(['ok'=>true,'configured'=>cmsAuthConfigured($root),'authenticated'=>$user!==null,'user'=>$user,'csrf'=>$user?cmsCsrfToken():null]);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);$payload=readJsonBody(16384);$action=(string)($payload['action']??'login');
    if($action==='login'){
        enforceRateLimit('cms-login',20,900,$root);$username=trim((string)($payload['username']??''));$password=(string)($payload['password']??'');
        if(!cmsAuthenticate($username,$password,$root))runtimeJson(['ok'=>false,'error'=>'Invalid username or password.'],401);
        $user=cmsCurrentUser($root);runtimeJson(['ok'=>true,'authenticated'=>true,'user'=>$user,'csrf'=>cmsCsrfToken()]);
    }
    if($action==='logout'){
        requireCmsAuth(true);requireCmsCsrf();cmsLogout($root);runtimeJson(['ok'=>true,'authenticated'=>false]);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported authentication action.'],422);
}catch(Throwable $e){runtimeError($e,'Authentication request failed.',500);}
