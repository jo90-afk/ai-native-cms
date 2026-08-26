<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';
require_once __DIR__.'/redirects.php';
secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');

function cmsRedirectPayload(): array {
    $rows=redirectAllRecords();$active=count(array_filter($rows,fn($row)=>!empty($row['active'])));$system=count(array_filter($rows,fn($row)=>!empty($row['readOnly'])));return ['ok'=>true,'redirects'=>$rows,'summary'=>['total'=>count($rows),'active'=>$active,'system'=>$system,'editable'=>count($rows)-$system]];
}
function cmsRedirectSafeError(Throwable $e): ?string {
    if(!$e instanceof RuntimeException)return null;$message=trim($e->getMessage());foreach(['Redirect source ','Redirect target ','Redirect status ','Redirect cycle ','Redirect changed ','This redirect ','Only manually ','A manually ','A system-managed ','Conflicting redirect ','Saved redirect ','Post redirect '] as $prefix)if(str_starts_with($message,$prefix))return $message;return null;
}
try{
    redirectRequireSchema();
    if($method==='GET')runtimeJson(cmsRedirectPayload());
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-redirects',80,3600,$root);$payload=readJsonBody(65536);$action=(string)($payload['action']??'');
    if($action==='save'){
        if(!is_array($payload['redirect']??null))runtimeJson(['ok'=>false,'error'=>'Redirect payload is required.'],422);$expected=trim((string)($payload['expectedHash']??''));$input=$payload['redirect'];if((int)($input['id']??0)>0&&$expected==='')runtimeJson(['ok'=>false,'error'=>'Expected redirect revision is required.'],422);
        $saved=redirectSaveRecord($root,$input,$expected);$projection=redirectProject($root);cmsAudit('redirects','Saved redirect record',['id'=>$saved['id'],'source'=>$saved['source'],'target'=>$saved['target'],'status'=>$saved['status'],'active'=>$saved['active']],$root);runtimeJson(cmsRedirectPayload()+['saved'=>$saved,'projection'=>$projection]);
    }
    if($action==='delete'){
        $id=(int)($payload['id']??0);$expected=trim((string)($payload['expectedHash']??''));if(($payload['confirm']??null)!=='DELETE'||$id<1||$expected==='')runtimeJson(['ok'=>false,'error'=>'Redirect ID, expected revision, and explicit delete confirmation are required.'],422);redirectDeleteRecord($id,$expected);$projection=redirectProject($root);cmsAudit('redirects','Deleted redirect record',['id'=>$id],$root);runtimeJson(cmsRedirectPayload()+['deleted'=>$id,'projection'=>$projection]);
    }
    runtimeJson(['ok'=>false,'error'=>'Unknown redirect action.'],422);
}catch(Throwable $e){
    if($e instanceof RuntimeException&&str_contains($e->getMessage(),'schema upgrade required'))runtimeJson(['ok'=>false,'error'=>'Redirect management requires schema v8. Run the explicit 7-to-8 migration from CLI.','migrationRequired'=>true],409);$safe=cmsRedirectSafeError($e);if($safe!==null)runtimeJson(['ok'=>false,'error'=>$safe],str_contains($safe,'changed since')?409:422);runtimeError($e,'The redirect operation could not be completed.',500);
}
