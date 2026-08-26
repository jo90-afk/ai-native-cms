<?php
declare(strict_types=1);
require_once __DIR__.'/post-renderer.php';
require_once __DIR__.'/seo.php';

secureJsonHeaders();requireCmsAuth(true);$root=dirname(__DIR__);$method=(string)($_SERVER['REQUEST_METHOD']??'GET');
try{
    if($method==='GET'){
        $slug=cleanSlug((string)($_GET['slug']??''));
        if($slug==='')runtimeJson(['ok'=>true,'posts'=>array_map('postPublicMeta',allPosts())]);
        $post=postFind($slug);if(!$post)runtimeJson(['ok'=>false,'error'=>'Post not found.'],404);
        runtimeJson(['ok'=>true,'post'=>$post,'revisions'=>postRevisions($slug,30),'projectionPath'=>writingArticlePath($slug)]);
    }
    if($method!=='POST')runtimeJson(['ok'=>false,'error'=>'Method not allowed.'],405);
    requireSameOrigin(true);requireCmsCsrf();enforceRateLimit('cms-writing-save',90,3600,$root);$payload=readJsonBody(4500000);$action=(string)($payload['action']??'save');
    if($action==='save'){
        $input=$payload['post']??null;if(!is_array($input))runtimeJson(['ok'=>false,'error'=>'Post data is required.'],422);
        $original=cleanSlug((string)($payload['originalSlug']??($input['slug']??'')));$expected=trim((string)($payload['expectedHash']??''));$before=$original!==''?postFind($original):null;
        $saved=savePostToDatabase($input,$original,$expected!==''?$expected:null);
        if($before&&$before['slug']!==$saved['slug']&&$before['status']==='published')removePostProjection($root,(string)$before['slug']);
        if($saved['status']==='published'){$path=projectPost($root,$saved);seoProjectTarget($root,$path);}else removePostProjection($root,(string)$saved['slug']);
        rebuildPostIndex($root);cmsAudit('writing','Saved long-form post',['slug'=>$saved['slug'],'status'=>$saved['status']],$root);
        runtimeJson(['ok'=>true,'post'=>$saved,'revisions'=>postRevisions((string)$saved['slug'],30),'projectionPath'=>writingArticlePath((string)$saved['slug'])]);
    }
    if($action==='restore'){
        $slug=cleanSlug((string)($payload['slug']??''));$revisionId=(int)($payload['revisionId']??0);$expected=trim((string)($payload['expectedHash']??''));
        if($slug===''||$revisionId<1||$expected==='')runtimeJson(['ok'=>false,'error'=>'Revision, post, and expected hash are required.'],422);
        $before=postFind($slug);$saved=restorePostRevision($slug,$revisionId,$expected);
        if($before&&$before['slug']!==$saved['slug']&&$before['status']==='published')removePostProjection($root,(string)$before['slug']);
        if($saved['status']==='published'){$path=projectPost($root,$saved);seoProjectTarget($root,$path);}else removePostProjection($root,(string)$saved['slug']);
        rebuildPostIndex($root);cmsAudit('writing','Restored long-form post revision',['slug'=>$saved['slug'],'revisionId'=>$revisionId],$root);
        runtimeJson(['ok'=>true,'post'=>$saved,'revisions'=>postRevisions((string)$saved['slug'],30),'projectionPath'=>writingArticlePath((string)$saved['slug'])]);
    }
    runtimeJson(['ok'=>false,'error'=>'Unsupported writing action.'],422);
}catch(Throwable $e){
    if($e instanceof RuntimeException&&str_contains($e->getMessage(),'Post changed since it was opened'))runtimeJson(['ok'=>false,'error'=>'Post changed since it was opened. Refresh before saving.'],409);
    if($e instanceof RuntimeException&&in_array($e->getMessage(),['That slug is already in use.','A valid slug is required.'],true))runtimeJson(['ok'=>false,'error'=>$e->getMessage()],422);
    runtimeError($e,'The writing update could not be completed.',500);
}
