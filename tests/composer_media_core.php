<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/composer.php';

function t(bool $condition,string $message): void {if(!$condition){fwrite(STDERR,"FAIL: $message\n");exit(1);}}

$block='<section class="hero"><h1 data-cms-id="hero.title">Hello <strong>world</strong></h1><p data-cms-id="hero.dek">Dek</p><a href="/about.html">Read</a><img src="" alt="Old"></section>';
$vars=composerTemplateVariables($block);
t(count($vars)===4,'expected two copy variables, one image, and one link');
$types=array_column($vars,'type');
t($types===['richtext','richtext','media','link'],'typed variable order changed');

$input=[];
foreach($vars as $v){
    if($v['type']==='richtext')$input[$v['key']]='<strong>Safe</strong><script>alert(1)</script>';
    elseif($v['type']==='media')$input[$v['key']]=['path'=>'','alt'=>'New alt'];
    elseif($v['type']==='link')$input[$v['key']]='/next.html';
}
$normalized=composerNormalizeValues(dirname(__DIR__),$vars,$input);
foreach($vars as $v)if($v['type']==='richtext')t(!str_contains((string)$normalized[$v['key']],'<script'),'rich text sanitizer allowed a script tag');
$applied=composerApplyValues(dirname(__DIR__),$block,$vars,$normalized);
t(str_contains($applied,'href="/next.html"'),'link value was not applied');
t(str_contains($applied,'alt="New alt"'),'media alt value was not applied');
t(!str_contains($applied,'<script'),'unsafe rich text reached rendered template');

$rekeyed=composerRekeyEditableIds($applied,'instance-01');
t(str_contains($rekeyed,'data-cms-id="cmp-instance-01-hero.title"'),'editable IDs were not namespaced to the composition instance');
$annotated=composerAnnotateBlock($rekeyed,'instance-01');
t(str_contains($annotated,'data-composer-instance="instance-01"'),'composition instance annotation missing');

$shell='<!doctype html><html><body><header>Header</header><main><section>Old</section></main><footer>Footer</footer></body></html>';
$next=composerReplaceMain($shell,[$annotated]);
t(str_contains($next,'<header>Header</header>')&&str_contains($next,'<footer>Footer</footer>'),'composer damaged shell outside main');
t(!str_contains($next,'<section>Old</section>'),'composer failed to replace main structure');

t(mediaAllowedPath('assets/example.png'),'configured asset root rejected image');
t(!mediaAllowedPath('../secret.png'),'media traversal path accepted');
t(!mediaAllowedPath('assets/file.php'),'non-image media path accepted');
t(cleanSlug(' Example File Name ')==='example-file-name','shared slug primitive changed unexpectedly');

$bad=$input;foreach($vars as $v)if($v['type']==='link')$bad[$v['key']]='javascript:alert(1)';
$threw=false;try{composerNormalizeValues(dirname(__DIR__),$vars,$bad);}catch(RuntimeException $e){$threw=true;}
t($threw,'unsafe template link was accepted');

fwrite(STDOUT,"PASS: composer and media primitives\n");
