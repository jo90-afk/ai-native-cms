<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/content-sync.php';

function check(bool $ok,string $message): void {if(!$ok){fwrite(STDERR,'FAIL: '.$message.PHP_EOL);exit(1);}}

$html='<main><h1 data-cms-id="hero.title">Hello</h1><p data-cms-id="hero.body">Body <em>copy</em>.</p></main>';
$blocks=cmsExtractEditableBlocks($html);
check(count($blocks)===2,'editable block extraction count');
check($blocks[0]['id']==='hero.title'&&$blocks[0]['html']==='Hello','editable block identity/content');

$dirty='<strong onclick="alert(1)">Hi</strong><script>alert(2)</script><a href="javascript:alert(3)" onclick="x">bad</a><a href="/safe" data-x="1">safe</a>';
$clean=cmsSanitizeRichHtml($dirty);
check(!str_contains(strtolower($clean),'<script'),'script tag survives sanitization');
check(!str_contains(strtolower($clean),'onclick'),'event handler survives sanitization');
check(!str_contains(strtolower($clean),'javascript:'),'scriptable link survives sanitization');
check(str_contains($clean,'<strong>Hi</strong>'),'strong markup was not preserved');
check(str_contains($clean,'<a>bad</a>'),'unsafe link was not stripped to a safe anchor');
check(str_contains($clean,'<a href="/safe">safe</a>'),'safe relative link was not preserved');

$hash=hash('sha256','Hello');
$replaced=cmsReplaceEditableBlock($html,'hero.title','New <em>title</em>',$hash);
check(str_contains($replaced,'<h1 data-cms-id="hero.title">New <em>title</em></h1>'),'editable block replacement failed');
$conflicted=false;
try{cmsReplaceEditableBlock($html,'hero.title','Other','deadbeef');}catch(RuntimeException $e){$conflicted=str_contains($e->getMessage(),'Page changed since it was opened');}
check($conflicted,'optimistic block hash conflict was not enforced');

$standing=[['standing'=>true,'changes'=>[
    ['kind'=>'block','page'=>'index.html','block'=>'hero.title','old'=>'Old','new'=>'Accepted'],
    ['kind'=>'document-replace','key'=>'content/site.json','old'=>'old-value','new'=>'new-value'],
]]];
check(contentSyncTransformBlockCandidate($standing,'index.html','hero.title','Old')==='Accepted','standing block transform failed');
check(contentSyncTransformBlockCandidate($standing,'index.html','hero.title','Different')==='Different','standing block transform changed unrelated content');
check(contentSyncTransformDocumentCandidate($standing,'content/site.json','{"value":"old-value"}')==='{"value":"new-value"}','standing document transform failed');

$set=['id'=>'release:test','origin'=>'release','changes'=>[['kind'=>'block','page'=>'index.html','block'=>'hero.title','old'=>'A','new'=>'B']]];
check(contentSyncUpdateSetHash($set)===contentSyncUpdateSetHash($set),'update-set hash is not deterministic');

echo "PASS: content core behavior\n";
