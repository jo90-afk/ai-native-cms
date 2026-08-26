<?php
declare(strict_types=1);
require_once __DIR__.'/content-rebuild.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    $targets=seoTargets($root);
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));if($path!==''){if(!isset($targets[$path]))runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);runtimeJson(['ok'=>true,'page'=>seoReadTarget($root,$path,$targets[$path])]);}$items=[];foreach($targets as $targetPath=>$label)$items[]=seoReadTarget($root,$targetPath,$label);runtimeJson(['ok'=>true,'pages'=>$items]);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-seo-save',90,3600,$root);dbRequireSchemaVersion(8);$payload=readJsonBody(65536);$path=trim((string)($payload['path']??''));if(!isset($targets[$path]))runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);
    $normalized=seoNormalizePayload($path,(array)($payload['seo']??[]),(array)($payload['controls']??[]));$full=cmsSafePublicFile($root,$path);if($full===null)runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);$original=(string)file_get_contents($full);$pdo=db();$pdo->beginTransaction();$wrote=false;
    try{contentAuthorityBackupPage($path,$original,'seo');seoWriteOverride($path,$normalized['seo'],$normalized['controls']);cmsAtomicWrite($full,seoApplyToHtml($original,$normalized['seo']));$wrote=true;$pdo->commit();$projection=contentFinalizePublicProjections($root);cmsAudit('seo','Updated SEO metadata',['path'=>$path,'index'=>$normalized['controls']['index'],'canonicalMode'=>$normalized['controls']['canonicalMode']],$root);runtimeJson(['ok'=>true,'page'=>seoReadTarget($root,$path,$targets[$path]),'projection'=>$projection['core']]);}
    catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($wrote){try{cmsAtomicWrite($full,$original);}catch(Throwable $ignored){}}throw $e;}
}catch(Throwable $e){if($e instanceof RuntimeException&&str_contains($e->getMessage(),'Canonical must'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);if($e instanceof RuntimeException&&str_contains($e->getMessage(),'required'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);runtimeError($e,'The SEO update could not be completed.',500);}
