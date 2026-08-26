<?php
declare(strict_types=1);
require_once __DIR__.'/content-rebuild.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET')runtimeJson(['ok'=>true,'navigation'=>navigationState($root),'pages'=>array_map(fn($path,$label)=>['path'=>$path,'label'=>$label,'href'=>$path==='index.html'?'/':'/'.$path],array_keys(cmsManagedPages($root)),array_values(cmsManagedPages($root)))]);
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-navigation',60,3600,$root);dbRequireSchemaVersion(8);$payload=readJsonBody(131072);$items=$payload['items']??null;$expected=trim((string)($payload['expectedHash']??''));if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Navigation items are required.'],422);$navigation=navigationSave($root,$items,$expected);$projection=contentFinalizePublicProjections($root);runtimeJson(['ok'=>true,'navigation'=>$navigation,'projection'=>$projection['core']]);
}catch(Throwable $e){if($e instanceof RuntimeException&&str_contains($e->getMessage(),'changed since it was opened'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],409);if($e instanceof RuntimeException&&str_starts_with($e->getMessage(),'Navigation'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);runtimeError($e,'The navigation update could not be completed.',500);}
