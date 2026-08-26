<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/navigation.php';
require_once dirname(__DIR__).'/api/branding.php';

function t(bool $condition,string $message): void {if(!$condition){fwrite(STDERR,"FAIL: $message\n");exit(1);}}

t(compositionSafeNewPagePath('services.html'),'valid new page path rejected');
t(!compositionSafeNewPagePath('index.html'),'index page overwrite path accepted');
t(!compositionSafeNewPagePath('../services.html'),'new page traversal path accepted');
t(!compositionSafeNewPagePath('Services.html'),'uppercase new page path accepted');

$items=navigationValidate([
    ['id'=>'home','label'=>'Home','href'=>'/','enabled'=>true],
    ['id'=>'docs','label'=>'Docs','href'=>'/docs.html','enabled'=>true],
    ['id'=>'external','label'=>'External','href'=>'https://example.org/','enabled'=>true],
]);
t(count($items)===3,'valid navigation items changed');
t(navigationSafeHref('/about.html#team'),'same-site navigation href rejected');
t(!navigationSafeHref('//evil.example/'),'protocol-relative navigation href accepted');
t(!navigationSafeHref('/../secret'),'navigation traversal href accepted');
$navHtml='<nav id="site-nav"><a href="/old">Old</a></nav>';$projected=navigationApply($navHtml,$items,'docs.html');
t(str_contains($projected,'aria-current="page"'),'active navigation state not projected');
t(str_contains($projected,'target="_blank" rel="noopener noreferrer"'),'external navigation safety attributes missing');

$identity=['identity'=>['mark'=>'AC','name'=>'Acme Co'],'tokens'=>[]];$html='<a class="brand"><span class="brand-mark">XX</span><span class="brand-name">Old</span></a>';$branded=brandingApplyIdentity($html,$identity);
t(str_contains($branded,'>AC<')&&str_contains($branded,'>Acme Co<'),'brand identity was not projected');
t(brandingValidateToken('#aabbcc',['type'=>'color','min'=>0,'max'=>0])==='#aabbcc','valid brand color rejected');
$bad=false;try{brandingValidateToken('red',['type'=>'color','min'=>0,'max'=>0]);}catch(RuntimeException $e){$bad=true;}t($bad,'non-hex brand color accepted');
t(brandingValidateToken(80,['type'=>'length','min'=>0,'max'=>48])===48,'bounded brand number was not clamped');

$shell='<!doctype html><html><head><title>Old</title><link rel="canonical" href="https://example.com/old.html"><meta property="og:url" content="https://example.com/old.html"></head><body><main><h1>Old</h1></main></body></html>';
$retargeted=compositionRetargetShell($shell,'services.html','Services');
t(str_contains($retargeted,'<title>Services</title>'),'new page title was not retargeted');
t(str_contains($retargeted,'https://example.com/services.html'),'new page canonical was not retargeted');

fwrite(STDOUT,"PASS: site-wide presentation primitives\n");
