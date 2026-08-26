<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__).'/api/content-sync.php';
require_once dirname(__DIR__).'/api/content-rebuild.php';

$root=dirname(__DIR__);$sourceRef=trim((string)($argv[1]??'repository'))?:'repository';
try{
    dbRequireSchemaVersion(8);
    $status=contentAuthorityStatus($root);
    if(!(bool)($status['ready']??false))contentAuthorityImport($root,false,$sourceRef.':bootstrap');
    $reconcile=contentSyncRepository($root,$sourceRef);
    $updates=contentSyncApplyUpdateDirectory($root,$sourceRef);
    $rebuild=contentRebuild($root);
    echo json_encode(['ok'=>true,'reconcile'=>$reconcile,'updates'=>$updates,'rebuild'=>$rebuild,'status'=>contentAuthorityStatus($root)],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'Content reconciliation failed: '.$e->getMessage().PHP_EOL);exit(1);}
