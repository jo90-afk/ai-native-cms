<?php
declare(strict_types=1);
require_once __DIR__.'/composition-store.php';
require_once __DIR__.'/navigation.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));$pages=[];foreach(cmsManagedPages($root) as $p=>$label){$record=compositionRecord($p);$pages[]=['path'=>$p,'label'=>$label,'composed'=>$record!==null,'parentPath'=>$record['parentPath']??null,'dynamic'=>!isset(cmsConfiguredPages($root)[$p])];}
        $response=['ok'=>true,'pages'=>$pages,'templates'=>composerTemplates(),'media'=>mediaItems($root)];if($path!=='')$response['page']=compositionPayload($root,$path);runtimeJson($response);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-composer',90,3600,$root);$payload=readJsonBody(1000000);$action=(string)($payload['action']??'save');
    if($action==='refresh'){$templates=composerRefreshTemplates($root);$media=mediaRefreshLibrary($root);cmsAudit('composer','Refreshed template and media catalogs',['templates'=>$templates['templates'],'media'=>$media['assets']],$root);runtimeJson(['ok'=>true,'templates'=>composerTemplates(),'media'=>mediaItems($root)]);}
    if($action==='save'){
        $path=trim((string)($payload['path']??''));$items=$payload['items']??null;$expected=trim((string)($payload['expectedHash']??''));if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Composition blocks are required.'],422);
        $metadata=['label'=>(string)($payload['label']??''),'title'=>(string)($payload['title']??''),'shellPath'=>(string)($payload['shellPath']??''),'parentPath'=>array_key_exists('parentPath',$payload)?($payload['parentPath']!==null?(string)$payload['parentPath']:null):null];
        $saved=compositionSave($root,$path,$items,$expected,$metadata);navigationProject($root);runtimeJson(['ok'=>true,'page'=>compositionPayload($root,$path),'composition'=>$saved]);
    }
    if($action==='create'){
        $items=$payload['items']??null;if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Composition blocks are required.'],422);$path=trim((string)($payload['path']??''));$label=trim((string)($payload['label']??''));$title=trim((string)($payload['title']??''));$shell=trim((string)($payload['shellPath']??''));$parent=array_key_exists('parentPath',$payload)&&$payload['parentPath']!==null?(string)$payload['parentPath']:null;
        $saved=compositionCreatePage($root,$path,$label,$title,$shell,$parent,$items);if(!empty($payload['includeInNavigation']))navigationAddPage($root,$path,trim((string)($payload['navigationLabel']??$label))?:$label);else navigationProject($root);runtimeJson(['ok'=>true,'page'=>compositionPayload($root,$path),'composition'=>$saved,'navigation'=>navigationState($root)],201);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported composer action.'],422);
}catch(Throwable $e){
    if($e instanceof RuntimeException&&str_contains($e->getMessage(),'changed since it was opened'))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],409);
    if($e instanceof RuntimeException&&preg_match('/^(Page is not CMS-managed|New page|That page path|Choose a valid|Page hierarchy|Composition page|A composition needs|Invalid composition|Duplicate composition|A composed public page|A referenced page template|A selected template image|A template link)/',$e->getMessage()))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);
    runtimeError($e,'The page composition update could not be completed.',500);
}
