<?php
declare(strict_types=1);
require_once __DIR__.'/content-rebuild.php';
require_once __DIR__.'/seo-quality.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
function cmsSeoDecorateQuality(array $page,?array $quality): array {if(!is_array($quality))return $page;foreach(['issues','score','schemaTypes','inSitemap','indexable'] as $key)if(array_key_exists($key,$quality))$page[$key]=$quality[$key];return $page;}
try{
    dbRequireSchemaVersion(8);$targets=seoTargets($root);$quality=seoQualitySite($root,$targets);
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));
        if($path!==''){if(!isset($targets[$path]))runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);runtimeJson(['ok'=>true,'page'=>cmsSeoDecorateQuality(seoReadTarget($root,$path,$targets[$path]),$quality['pages'][$path]??null),'summary'=>$quality['summary'],'siteFindings'=>$quality['siteIssues']]);}
        $items=[];foreach($targets as $targetPath=>$label)$items[]=cmsSeoDecorateQuality(seoReadTarget($root,$targetPath,$label),$quality['pages'][$targetPath]??null);runtimeJson(['ok'=>true,'pages'=>$items,'issueCount'=>(int)$quality['summary']['errors']+(int)$quality['summary']['warnings'],'summary'=>$quality['summary'],'siteFindings'=>$quality['siteIssues']]);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-seo-save',90,3600,$root);$payload=readJsonBody(65536);$path=trim((string)($payload['path']??''));if(!isset($targets[$path]))runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);
    $normalized=seoNormalizePayload($path,(array)($payload['seo']??[]),(array)($payload['controls']??[]));$full=cmsSafePublicFile($root,$path);if($full===null)runtimeJson(['ok'=>false,'error'=>'SEO target not found.'],404);$original=(string)file_get_contents($full);$pdo=db();$pdo->beginTransaction();$wrote=false;
    try{contentAuthorityBackupPage($path,$original,'seo');seoWriteOverride($path,$normalized['seo'],$normalized['controls']);cmsAtomicWrite($full,seoApplyToHtml($original,$normalized['seo']));$wrote=true;$pdo->commit();$projection=contentFinalizePublicProjections($root);$targets=seoTargets($root);$quality=seoQualitySite($root,$targets);cmsAudit('seo','Updated SEO metadata',['path'=>$path,'index'=>$normalized['controls']['index'],'canonicalMode'=>$normalized['controls']['canonicalMode'],'seoErrors'=>$quality['summary']['errors'],'seoWarnings'=>$quality['summary']['warnings']],$root);runtimeJson(['ok'=>true,'page'=>cmsSeoDecorateQuality(seoReadTarget($root,$path,$targets[$path]),$quality['pages'][$path]??null),'projection'=>$projection['core'],'summary'=>$quality['summary'],'siteFindings'=>$quality['siteIssues']]);}
    catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($wrote){try{cmsAtomicWrite($full,$original);}catch(Throwable $ignored){}}throw $e;}
}catch(Throwable $e){if($e instanceof RuntimeException&&str_contains($e->getMessage(),'Canonical must'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);if($e instanceof RuntimeException&&str_contains($e->getMessage(),'required'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);runtimeError($e,'The SEO update could not be completed.',500);}
