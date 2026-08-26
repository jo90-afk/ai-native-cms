<?php
declare(strict_types=1);
require_once __DIR__.'/media.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET')runtimeJson(['ok'=>true,'items'=>mediaItems($root),'roots'=>mediaRoots(),'uploadRoot'=>mediaConfig('upload_root','assets/uploads'),'maxUploadBytes'=>(int)mediaConfig('max_upload_bytes',8388608)]);
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-media',120,3600,$root);$contentType=strtolower((string)($_SERVER['CONTENT_TYPE']??''));
    if(str_starts_with($contentType,'multipart/form-data')){
        if(!isset($_FILES['file']))runtimeJson(['ok'=>false,'error'=>'Image file is required.'],422);$item=mediaUpload($root,$_FILES['file'],['title'=>(string)($_POST['title']??''),'alt'=>(string)($_POST['alt']??''),'caption'=>(string)($_POST['caption']??'')]);cmsAudit('media','Uploaded media asset',['key'=>$item['key'],'path'=>$item['path']],$root);runtimeJson(['ok'=>true,'item'=>$item,'items'=>mediaItems($root)]);
    }
    $payload=readJsonBody(131072);$action=(string)($payload['action']??'update');
    if($action==='refresh'){$result=mediaRefreshLibrary($root);cmsAudit('media','Refreshed media catalog',$result,$root);runtimeJson(['ok'=>true,'result'=>$result,'items'=>mediaItems($root)]);}
    if($action==='update'){
        $key=trim((string)($payload['key']??''));$existing=mediaByKey($root,$key);if(!$existing)runtimeJson(['ok'=>false,'error'=>'Media item not found.'],404);$title=mediaSubstr(trim((string)($payload['title']??$existing['title'])),191);$alt=mediaSubstr(trim((string)($payload['alt']??$existing['alt'])),1000);$caption=(string)($payload['caption']??$existing['caption']);if(strlen($caption)>20000)runtimeJson(['ok'=>false,'error'=>'Caption is too long.'],422);$item=mediaUpsertRecord($root,(string)$existing['path'],$title,$alt,$caption,(string)$existing['source']);cmsAudit('media','Updated media metadata',['key'=>$key,'path'=>$item['path']],$root);runtimeJson(['ok'=>true,'item'=>$item]);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported media action.'],422);
}catch(Throwable $e){runtimeError($e,'The media operation could not be completed.',500);}
