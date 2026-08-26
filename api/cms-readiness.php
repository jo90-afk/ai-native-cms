<?php
declare(strict_types=1);
require_once __DIR__.'/readiness.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method!=='GET')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    runtimeJson(['ok'=>true,'readiness'=>readinessReport($root)]);
}catch(Throwable $e){runtimeError($e,'The readiness report could not be completed.',500);}
