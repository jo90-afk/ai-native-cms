<?php
declare(strict_types=1);
require_once __DIR__.'/content-rebuild.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET')runtimeJson(['ok'=>true,'branding'=>brandingState()]);
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-branding',60,3600,$root);dbRequireSchemaVersion(8);$payload=readJsonBody(131072);$settings=$payload['settings']??null;$expected=trim((string)($payload['expectedHash']??''));if(!is_array($settings))runtimeJson(['ok'=>false,'error'=>'Branding settings are required.'],422);$branding=brandingSave($root,$settings,$expected);$projection=contentFinalizePublicProjections($root);runtimeJson(['ok'=>true,'branding'=>$branding,'projection'=>$projection['core']]);
}catch(Throwable $e){if($e instanceof RuntimeException&&str_contains($e->getMessage(),'changed since it was opened'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],409);if($e instanceof RuntimeException&&str_starts_with($e->getMessage(),'Brand'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);runtimeError($e,'The branding update could not be completed.',500);}
