<?php
declare(strict_types=1);
require_once __DIR__.'/content-rebuild.php';
require_once __DIR__.'/media.php';
secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    dbRequireSchemaVersion(9);
    if($method==='GET'){
        $path=trim((string)($_GET['path']??''));$pages=[];$configured=cmsConfiguredPages($root);
        foreach(cmsManagedPages($root) as $p=>$label){$record=compositionRecord($p);$pages[]=['path'=>$p,'label'=>$label,'composed'=>$record!==null,'parentPath'=>$record['parentPath']??null,'dynamic'=>!isset($configured[$p])];}
        $response=['ok'=>true,'pages'=>$pages,'presets'=>blockPresets(),'media'=>mediaItems($root)];if($path!=='')$response['page']=compositionPayload($root,$path);runtimeJson($response);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-composer',150,3600,$root);$payload=readJsonBody(1500000);$action=(string)($payload['action']??'save');
    if($action==='refresh'){$media=mediaRefreshLibrary($root);cmsAudit('composer','Refreshed media catalog',['media'=>$media['assets']],$root);runtimeJson(['ok'=>true,'presets'=>blockPresets(),'media'=>mediaItems($root)]);}
    if($action==='bootstrapPresets'){$result=blockPresetBootstrap($root);cmsAudit('composer','Imported repository block presets',$result,$root);runtimeJson(['ok'=>true,'presets'=>blockPresets(),'media'=>mediaItems($root)]);}
    if($action==='previewItem'){
        $item=$payload['item']??null;if(!is_array($item))runtimeJson(['ok'=>false,'error'=>'A typed block item is required.'],422);$rendered=compositionRenderBlock($root,$item);
        runtimeJson(['ok'=>true,'html'=>$rendered['html'],'values'=>$rendered['values'],'presetKey'=>$rendered['presetKey'],'instanceId'=>$rendered['instanceId']]);
    }
    if($action==='save'){
        $path=trim((string)($payload['path']??''));$items=$payload['items']??null;$expected=trim((string)($payload['expectedHash']??''));if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Composition blocks are required.'],422);
        $metadata=[];foreach(['label','title','shellPath','parentPath'] as $key)if(array_key_exists($key,$payload))$metadata[$key]=$payload[$key]!==null?(string)$payload[$key]:null;$metadata['expectedSourceHash']=trim((string)($payload['expectedSourceHash']??''));
        $saved=compositionSave($root,$path,$items,$expected,$metadata);$projection=contentFinalizePublicProjections($root);runtimeJson(['ok'=>true,'page'=>compositionPayload($root,$path),'composition'=>$saved,'projection'=>$projection['core']]);
    }
    if($action==='create'){
        $items=$payload['items']??null;if(!is_array($items))runtimeJson(['ok'=>false,'error'=>'Composition blocks are required.'],422);$path=trim((string)($payload['path']??''));$label=trim((string)($payload['label']??''));$title=trim((string)($payload['title']??''));$shell=trim((string)($payload['shellPath']??''));$parent=array_key_exists('parentPath',$payload)&&$payload['parentPath']!==null?(string)$payload['parentPath']:null;
        $saved=compositionCreatePage($root,$path,$label,$title,$shell,$parent,$items);if(!empty($payload['includeInNavigation']))navigationAddPage($root,$path,trim((string)($payload['navigationLabel']??$label))?:$label);$projection=contentFinalizePublicProjections($root);runtimeJson(['ok'=>true,'page'=>compositionPayload($root,$path),'composition'=>$saved,'navigation'=>navigationState($root),'projection'=>$projection['core']],201);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported composer action.'],422);
}catch(Throwable $e){
    if($e instanceof RuntimeException&&str_contains($e->getMessage(),'schema upgrade required'))runtimeJson(['ok'=>false,'error'=>'Schema v9 is required before using Page Composer. Run database/migrations/8-to-9.php --apply from the CLI.','migrationRequired'=>true],409);
    if($e instanceof RuntimeException&&(str_contains($e->getMessage(),'changed since the composer was opened')||str_contains($e->getMessage(),'changed since it was opened')))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],409);
    if($e instanceof RuntimeException&&preg_match('/^(Page is not CMS-managed|New page|That page path|Choose a valid|Page hierarchy|Composition page|A composition needs|Invalid composition|Duplicate composition|A composed public page|A referenced block preset|A selected block image|A block button|A typed block item)/',$e->getMessage()))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);
    runtimeError($e,'The page composition update could not be completed.',500);
}
