<?php
declare(strict_types=1);
require_once __DIR__.'/onboarding.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method!=='GET')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    runtimeJson(['ok'=>true,'onboarding'=>onboardingState($root)]);
}catch(Throwable $e){runtimeError($e,'Onboarding state could not be inspected.',500);}
