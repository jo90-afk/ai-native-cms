<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/seo.php';

function checkPublishing(bool $ok,string $message): void {if(!$ok){fwrite(STDERR,'FAIL: '.$message.PHP_EOL);exit(1);}}

$markdown="## Heading\n\nHello **strong** and *emphasis*.\n\n<script>alert(1)</script>\n\n[bad](javascript:alert(2)) [good](/safe)\n\n```\n<b>code</b>\n```";
$html=renderPostMarkdown($markdown);
checkPublishing(str_contains($html,'<h2 id="heading">Heading</h2>'),'heading rendering failed');
checkPublishing(str_contains($html,'<strong>strong</strong>'),'strong rendering failed');
checkPublishing(str_contains($html,'&lt;script&gt;alert(1)&lt;/script&gt;'),'raw HTML was not escaped');
checkPublishing(!str_contains(strtolower($html),'javascript:'),'unsafe Markdown URL survived');
checkPublishing(str_contains($html,'href="/safe"'),'safe Markdown URL was not retained');
checkPublishing(str_contains($html,'&lt;b&gt;code&lt;/b&gt;'),'code fence content was not escaped');

$post=normalizePost(['slug'=>' Hello, World! ','title'=>' Example ','status'=>'unknown','tags'=>[' one ','one','two'],'related'=>[' Other Post '],'body'=>'body']);
checkPublishing($post['slug']==='hello-world','slug normalization failed');
checkPublishing($post['status']==='draft','status normalization failed closed');
checkPublishing($post['tags']===['one','two'],'tag normalization/deduplication failed');
checkPublishing($post['related']===['other-post'],'related slug normalization failed');
checkPublishing(postRevisionHash($post)===postRevisionHash($post),'post revision hash is not deterministic');

$baseHtml='<!doctype html><html><head><title>Old</title><meta name="description" content="Old desc"><link rel="canonical" href="https://example.com/old/"></head><body>Body</body></html>';
$seo=['title'=>'New title','description'=>'New description','canonical'=>'https://example.com/about.html','robots'=>'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1','ogTitle'=>'Social title','ogDescription'=>'Social description','twitterTitle'=>'Tweet title','twitterDescription'=>'Tweet description'];
$next=seoApplyToHtml($baseHtml,$seo);
checkPublishing(str_contains($next,'<title>New title</title>'),'SEO title projection failed');
checkPublishing(str_contains($next,'content="New description"'),'SEO description projection failed');
checkPublishing(str_contains($next,'href="https://example.com/about.html"'),'SEO canonical projection failed');
checkPublishing(str_contains($next,'property="og:title" content="Social title"'),'Open Graph projection failed');

$normalized=seoNormalizePayload('about.html',['title'=>'About','description'=>'About page','canonical'=>''],['index'=>true,'follow'=>true,'archive'=>true,'canonicalMode'=>'self','socialMode'=>'inherit','snippetLimit'=>-1,'imagePreview'=>'large','videoPreviewLimit'=>-1]);
checkPublishing($normalized['seo']['canonical']==='https://example.com/about.html','self-canonical did not use configured public origin');
checkPublishing($normalized['seo']['ogTitle']==='About'&&$normalized['seo']['twitterDescription']==='About page','social inheritance failed');
$blocked=false;try{seoNormalizePayload('about.html',['title'=>'About','description'=>'About page','canonical'=>'https://other.example/about'],['canonicalMode'=>'custom','socialMode'=>'inherit']);}catch(RuntimeException $e){$blocked=str_contains($e->getMessage(),'configured public origin');}
checkPublishing($blocked,'cross-origin canonical was not rejected');

echo "PASS: publishing and SEO core behavior\n";
