<?php
declare(strict_types=1);

require_once dirname(__DIR__).'/api/seo-quality.php';
require_once dirname(__DIR__).'/api/seo-projection.php';

function checkSeo(bool $ok,string $message): void {if(!$ok){fwrite(STDERR,"FAIL: $message\n");exit(1);}}
function issueCodes(array $page): array {return array_map(fn($i)=>(string)($i['code']??''),(array)($page['issues']??[]));}
function removeSeoTree(string $path): void {if(!file_exists($path))return;if(is_file($path)||is_link($path)){@unlink($path);return;}foreach(scandir($path)?:[] as $name){if($name==='.'||$name==='..')continue;removeSeoTree($path.'/'.$name);}@rmdir($path);}

$base='https://example.com';
checkSeo(seoQualityLocalPathFromUrl($base,'https://example.com/')==='index.html','root canonical did not resolve to index.html');
checkSeo(seoQualityLocalPathFromUrl($base,'https://example.com/writing/post/')==='writing/post/index.html','directory canonical did not resolve to index.html');
checkSeo(seoQualityLocalPathFromUrl($base,'https://other.example/page')===null,'foreign canonical was accepted');
checkSeo(seoQualityResolveHref($base,'about.html','/writing.html')==='writing.html','root-relative internal link did not resolve');
checkSeo(seoQualityResolveHref($base,'writing/post/index.html','../../about.html')==='about.html','relative internal link did not normalize');

$source='<!doctype html><html><head><title>A useful example page</title><meta name="description" content="A sufficiently descriptive summary for a portable SEO projection behavior test that stays useful."><link rel="canonical" href="https://example.com/example.html"><meta property="og:title" content="Old social"><meta property="og:description" content="Old social description"><meta name="twitter:title" content="Old tweet"><meta name="twitter:description" content="Old tweet description"></head><body><h1>Example</h1></body></html>';
$inherit=seoProjectEnhancements('example.html',$source,true);
checkSeo(seoMetaValue($inherit,'property','og:title')==='A useful example page','inherit mode did not synchronize Open Graph title');
checkSeo(seoMetaValue($inherit,'name','twitter:title')==='A useful example page','inherit mode did not synchronize Twitter title');
checkSeo(seoMetaValue($inherit,'name','twitter:card')!=='','projection did not add Twitter card type');
checkSeo(str_contains($inherit,'application/ld+json'),'projection did not add fallback JSON-LD');
$custom=seoProjectEnhancements('example.html',$source,false);
checkSeo(seoMetaValue($custom,'property','og:title')==='Old social','custom social mode was overwritten');
checkSeo(seoMetaValue($custom,'name','twitter:title')==='Old tweet','custom Twitter title was overwritten');

$root=dirname(__DIR__);$folder='seo-quality-fixture-'.bin2hex(random_bytes(4));$fixture=$root.'/'.$folder;mkdir($fixture,0777,true);
try{
    $path=$folder.'/page.html';
    $html='<!doctype html><html><head><title>Short</title><meta name="description" content="Tiny"><link rel="canonical" href="https://example.com/'.$folder.'/page.html"></head><body><h1>One</h1><h1>Two</h1><a href="missing.html">Broken</a><img src="x.png"></body></html>';
    file_put_contents($fixture.'/page.html',$html);
    $page=seoQualityPage($root,$base,$path,'Fixture',false,false);$codes=issueCodes($page);
    foreach(['short_title','short_description','h1_count','open_graph_incomplete','twitter_incomplete','schema_missing','image_alt_missing'] as $code)checkSeo(in_array($code,$codes,true),'quality audit missed '.$code);
    checkSeo(in_array('missing.html',$page['hrefs']??[],true),'quality audit did not retain href inventory');
    $projected=seoProjectEnhancements($path,$html,true);file_put_contents($fixture.'/page.html',$projected);
    $page=seoQualityPage($root,$base,$path,'Fixture',false,false);$codes=issueCodes($page);
    checkSeo(!in_array('open_graph_incomplete',$codes,true),'projection did not repair Open Graph metadata');
    checkSeo(!in_array('twitter_incomplete',$codes,true),'projection did not repair Twitter metadata');
    checkSeo(!in_array('schema_missing',$codes,true),'projection did not repair structured data');
} finally {removeSeoTree($fixture);}

fwrite(STDOUT,"PASS: site-wide SEO quality and projection primitives\n");
