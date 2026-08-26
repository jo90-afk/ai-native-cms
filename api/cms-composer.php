<?php
declare(strict_types=1);
require_once __DIR__.'/composition-store.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));$pages=[];foreach(cmsManagedPages($root) as $p=>$label)$pages[]=['path'=>$p,'label'=>$label,'composed'=>compositionExists($p)];
        $response=['ok'=>true,'pages'=>$pages,'templates'=>composerTemplates(),'media'=>mediaItems($root)];if($path!=='')$response['page']=compositionPayload($root,$path);runtimeJson($response);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-composer',90,3600,$root);$payload=readJsonBody(1000000);$action=(string)($payload['action']??'save');
    if($action==='refresh'){$templates=composerRefreshTemplates($root);$media=mediaRefreshLibrary($root);cmsAudit('composer','Refreshed template and media catalogs',['templates'=>$templates['templates'],'media'=>$media['assets']],$root);runtimeJson(['ok'=>true,'templates'=>composerTemplates(),'media'=>mediaItems($root)]);}
    if($action==='save'){
        $path=trim((string)($payload['path']??''));$items=$payload['items']??null;$expected=trim((string)($payload['expectedHash']??''));if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Composition blocks are required.'],422);
        $saved=compositionSave($root,$path,$items,$expected);runtimeJson(['ok'=>true,'page'=>compositionPayload($root,$path),'composition'=>$saved]);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported composer action.'],422);
}catch(Throwable $e){
    if($e instanceof RuntimeException&&str_contains($e->getMessage(),'changed since it was opened'))runtimeJson(['ok'=>false,'error'=>'Page composition changed since it was opened. Refresh before saving.'],409);
    if($e instanceof RuntimeException&&preg_match('/^(Only configured pages|A composition needs|Invalid composition|Duplicate composition|A composed public page|A referenced page template|A selected template image|A template link)/',$e->getMessage()))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);
    runtimeError($e,'The page composition update could not be completed.',500);
}
