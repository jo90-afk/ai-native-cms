<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';
require_once __DIR__.'/content-rebuild.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    $managed=contentAuthorityManagedPages($root);$authority=contentAuthorityStatus($root);
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));
        if($path===''){$items=[];foreach($managed as $p=>$label){$full=cmsSafePublicFile($root,$p);if($full!==null)$items[]=cmsPublicPageInfo($root,$p,$label);}runtimeJson(['ok'=>true,'pages'=>$items,'contentAuthority'=>$authority]);}
        if(!isset($managed[$path]))runtimeJson(['ok'=>false,'error'=>'Page not found.'],404);$full=cmsSafePublicFile($root,$path);if($full===null)runtimeJson(['ok'=>false,'error'=>'Page not found.'],404);$html=(string)file_get_contents($full);$ready=(bool)($authority['ready']??false);if($ready)$html=contentAuthorityOverlayBlocks($html,$path);$blocks=$ready?array_values(contentAuthorityPageBlocks($path)):cmsExtractEditableBlocks($html);runtimeJson(['ok'=>true,'page'=>cmsPublicPageInfo($root,$path,$managed[$path]),'blocks'=>$blocks,'contentAuthority'=>$authority,'composed'=>compositionExists($path)]);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-page-save',120,3600,$root);dbRequireSchemaVersion(8);if(!(bool)($authority['ready']??false))runtimeJson(['ok'=>false,'error'=>'Canonical repository content is not initialized. Run database/reconcile.php from CLI before editing.'],409);
    $payload=readJsonBody(1500000);$path=trim((string)($payload['path']??''));if(!isset($managed[$path]))runtimeJson(['ok'=>false,'error'=>'Page not found.'],404);$changes=$payload['changes']??[];if(!is_array($changes)||count($changes)>300)runtimeJson(['ok'=>false,'error'=>'Invalid page changes.'],422);$validated=[];
    foreach($changes as $change){if(!is_array($change))continue;$id=trim((string)($change['id']??''));$replacement=(string)($change['html']??'');$hash=(string)($change['hash']??'');if($id===''||strlen($id)>191||!preg_match('/^[A-Za-z0-9._:-]+$/',$id))runtimeJson(['ok'=>false,'error'=>'Invalid editable block identifier.'],422);if(strlen($replacement)>100000)runtimeJson(['ok'=>false,'error'=>'One edited text block is too large.'],413);$validated[]=['id'=>$id,'html'=>$replacement,'hash'=>$hash];}
    if(!$validated)runtimeJson(['ok'=>true,'saved'=>0,'page'=>cmsPublicPageInfo($root,$path,$managed[$path]),'contentAuthority'=>$authority]);
    $full=cmsSafePublicFile($root,$path);if($full===null)runtimeJson(['ok'=>false,'error'=>'Page not found.'],404);$original=(string)file_get_contents($full);$canonicalBefore=contentAuthorityOverlayBlocks($original,$path);$pdo=db();$pdo->beginTransaction();$wrote=false;
    try{contentAuthorityBackupPage($path,$canonicalBefore,'content');$changed=contentAuthorityStoreBlockChanges($path,$validated);projectCmsPage($root,$path);$wrote=true;$pdo->commit();$projection=contentFinalizePublicProjections($root);cmsAudit('content','Edited page text',['page'=>$path,'blocks'=>$changed,'authority'=>'mysql','composed'=>compositionExists($path)],$root);runtimeJson(['ok'=>true,'saved'=>$changed,'page'=>cmsPublicPageInfo($root,$path,$managed[$path]),'contentAuthority'=>contentAuthorityStatus($root),'composed'=>compositionExists($path),'projection'=>$projection['core']]);}
    catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($wrote){try{cmsAtomicWrite($full,$canonicalBefore);}catch(Throwable $ignored){}}throw $e;}
}catch(Throwable $e){if($e instanceof RuntimeException&&str_contains($e->getMessage(),'Page changed since it was opened'))runtimeJson(['ok'=>false,'error'=>'Page changed since it was opened. Refresh before saving.'],409);runtimeError($e,'The page edit could not be saved.',500);}
