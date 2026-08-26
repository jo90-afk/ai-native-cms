<?php
declare(strict_types=1);

require_once dirname(__DIR__).'/setup/site.php';
require_once dirname(__DIR__).'/api/onboarding.php';

function check(bool $ok,string $message): void { if(!$ok){fwrite(STDERR,"FAIL: $message\n");exit(1);} }
function expectThrow(callable $fn,string $message): void { try{$fn();}catch(Throwable $e){return;}check(false,$message); }
function removeTree(string $path): void { if(!file_exists($path))return;if(is_file($path)||is_link($path)){@unlink($path);return;}foreach(scandir($path)?:[] as $name){if($name==='.'||$name==='..')continue;removeTree($path.'/'.$name);}@rmdir($path); }

check(siteSetupUrl('https://example.org')==='https://example.org','HTTPS site origin was not accepted');
check(siteSetupUrl('http://localhost:8080')==='http://localhost:8080','localhost HTTP development origin was not accepted');
expectThrow(fn()=>siteSetupUrl('http://example.org'),'remote HTTP origin was accepted');
expectThrow(fn()=>siteSetupUrl('https://example.org/path'),'origin with a path was accepted');
expectThrow(fn()=>siteSetupUrl('https://user:pass@example.org'),'origin with credentials was accepted');
expectThrow(fn()=>siteSetupUrl('https://example.org?x=1'),'origin with query was accepted');

$base=[
    'site'=>['name'=>'Example Site','base_url'=>'https://example.com','owner_display_name'=>'Site Owner'],
    'cms'=>['editable_pages'=>['index.html'=>'Home']],
    'branding'=>['mark'=>'','tokens'=>['accent'=>['css'=>'--accent','type'=>'color','default'=>'#3366ff']]],
    'seo'=>['author'=>'Site Owner','social_image'=>'/assets/share-card.svg','locale'=>'en_US','language'=>'en-US'],
];
$config=siteSetupConfig($base,'Acme Knowledge','https://docs.example.org','Editor');
check($config['site']['name']==='Acme Knowledge','site name was not updated');
check($config['site']['base_url']==='https://docs.example.org','site origin was not updated');
check($config['site']['owner_display_name']==='Editor','owner display name was not updated');
check($config['branding']['mark']==='AK','derived brand mark is wrong');
check(($config['seo']['author']??'')==='Editor','SEO author did not follow configured owner identity');
check(($config['seo']['social_image']??'')==='/assets/share-card.svg','site initializer discarded SEO projection defaults');
check(isset($config['cms']['editable_pages']['index.html']),'site initializer discarded unrelated public configuration');
check(isset($config['branding']['tokens']['accent']),'site initializer discarded branding token definitions');
$ownerFallback=siteSetupConfig($base,'Acme Knowledge','https://docs.example.org','');
check(($ownerFallback['seo']['author']??'')==='Acme Knowledge','SEO author did not fall back to site identity');

$tmp=sys_get_temp_dir().'/aincms-onboarding-'.bin2hex(random_bytes(5));
mkdir($tmp.'/config',0777,true);
try{
    $target=siteSetupWrite($tmp,$config,false);
    check($target===$tmp.'/config/site.php','site initializer wrote an unexpected path');
    check(is_file($target),'site initializer did not write config/site.php');
    $loaded=require $target;
    check(is_array($loaded)&&($loaded['site']['name']??'')==='Acme Knowledge','written site configuration could not be loaded');
    expectThrow(fn()=>siteSetupWrite($tmp,$config,false),'site initializer overwrote existing configuration without --force semantics');
    $changed=siteSetupConfig($config,'Acme Docs','https://docs.example.org','Editor');
    siteSetupWrite($tmp,$changed,true);
    $loaded=require $target;
    check(($loaded['site']['name']??'')==='Acme Docs','forced public-config replacement did not take effect');

    foreach(['index.html','about.html','writing.html','assets/styles.css','assets/site.js','templates/article.html'] as $path){$full=$tmp.'/'.$path;if(!is_dir(dirname($full)))mkdir(dirname($full),0777,true);file_put_contents($full,'starter');}
    $starter=onboardingStarterFiles($tmp);
    check(count($starter)===6,'starter file inventory changed unexpectedly');
    check(count(array_filter($starter,fn($row)=>!empty($row['present'])))===6,'complete starter site was not recognized');
    unlink($tmp.'/about.html');
    $starter=onboardingStarterFiles($tmp);
    check(count(array_filter($starter,fn($row)=>empty($row['present'])))===1,'missing starter file was not detected');
} finally { removeTree($tmp); }

$map=onboardingCheckMap(['checks'=>[
    ['id'=>'database.bootstrap','status'=>'pass'],
    ['id'=>'content.authority','status'=>'fail'],
]]);
check(onboardingCheckPassed($map,'database.bootstrap'),'onboarding readiness map lost a passing check');
check(!onboardingCheckPassed($map,'content.authority'),'onboarding readiness map treated a failure as passing');
$step=onboardingStep('writing','Writing','optional','Optional for launch.','/cms/writing.php','Open Writing',false);
check($step['state']==='optional'&&!$step['required'],'optional onboarding step semantics are wrong');

fwrite(STDOUT,"PASS: onboarding and public site setup primitives\n");
