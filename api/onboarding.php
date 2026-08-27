<?php
declare(strict_types=1);
require_once __DIR__.'/readiness.php';
require_once __DIR__.'/branding.php';
require_once __DIR__.'/navigation.php';

/** Read-only onboarding state derived from durable CMS/config/readiness state. */

function onboardingStarterFiles(string $root): array {
    $required=[
        'index.html'=>'Home starter',
        'about.html'=>'About starter',
        'writing.html'=>'Writing starter',
        'assets/styles.css'=>'Starter design system',
        'assets/site.js'=>'Static writing index behavior',
        'templates/article.html'=>'Writing article template',
    ];
    $out=[];
    foreach($required as $path=>$label){$full=rtrim($root,'/\\').'/'.$path;$out[]=['path'=>$path,'label'=>$label,'present'=>is_file($full)&&pathInside($full,$root)];}
    return $out;
}
function onboardingCheckMap(array $readiness): array {$out=[];foreach(($readiness['checks']??[]) as $check)if(is_array($check)&&isset($check['id']))$out[(string)$check['id']]=$check;return $out;}
function onboardingCheckPassed(array $checks,string $id): bool {return (($checks[$id]['status']??'fail')==='pass');}
function onboardingSiteIdentity(): array {
    $name=trim((string)siteConfigValue('site','name','Example Site'));$base=trim((string)siteConfigValue('site','base_url','https://example.com'));
    $placeholderName=strcasecmp($name,'Example Site')===0||$name==='';$host=(string)(parse_url($base,PHP_URL_HOST)??'');$placeholderOrigin=$host===''||strcasecmp($host,'example.com')===0;
    return ['name'=>$name?:'Example Site','baseUrl'=>$base,'customized'=>!$placeholderName&&!$placeholderOrigin];
}
function onboardingDatabaseSummary(string $root,array $readiness): array {
    $out=['available'=>false,'schemaCurrent'=>false,'schemaVersion'=>0,'brandingConfigured'=>false,'navigationConfigured'=>false,'publishedPosts'=>0,'managedPages'=>count(cmsConfiguredPages($root))];
    try{
        if(!dbConfigured()||!extension_loaded('pdo_mysql'))return $out;$health=dbHealth();$out['available']=true;$out['schemaVersion']=(int)($health['schemaVersion']??0);$out['schemaCurrent']=$out['schemaVersion']>=8;if(!$out['schemaCurrent'])return $out;
        $out['brandingConfigured']=(bool)(brandingState()['configured']??false);$out['navigationConfigured']=(bool)(navigationState($root)['configured']??false);$out['managedPages']=count(cmsManagedPages($root));
        $stmt=db()->query("SELECT COUNT(*) FROM posts WHERE status='published'");$out['publishedPosts']=(int)$stmt->fetchColumn();
    }catch(Throwable $e){}
    return $out;
}
function onboardingStep(string $id,string $label,string $state,string $message,string $href,string $action,bool $required=true): array {
    if(!in_array($state,['complete','ready','blocked','optional'],true))$state='blocked';
    return compact('id','label','state','message','href','action','required');
}
function onboardingState(string $root): array {
    $starter=onboardingStarterFiles($root);$starterReady=!array_filter($starter,fn($row)=>empty($row['present']));$identity=onboardingSiteIdentity();
    $readiness=readinessReport($root);$checks=onboardingCheckMap($readiness);$db=onboardingDatabaseSummary($root,$readiness);$siteConfig=is_file(rtrim($root,'/\\').'/config/site.php');
    $bootstrap=onboardingCheckPassed($checks,'database.bootstrap');$authority=onboardingCheckPassed($checks,'content.authority');$schemaLabel=$db['schemaVersion']>0?'Schema v'.$db['schemaVersion']:'The database schema';
    $steps=[];
    $steps[]=onboardingStep('repository','Name and configure the site',($siteConfig&&$identity['customized'])?'complete':'ready',$siteConfig?($identity['customized']?'Repository site identity is configured.':'Replace the Example Site identity/origin in config/site.php before launch.'):'Create config/site.php from the shipped example and set the public site name/origin.','/docs/REPOSITORY-OPERATIONS.md','Open repository guide');
    $steps[]=onboardingStep('starter','Start from a coherent site',$starterReady?'complete':'blocked',$starterReady?'Home, About, Writing, styles, and article structure are present.':'One or more starter-site files are missing from this installation.','/','View starter site');
    $steps[]=onboardingStep('bootstrap','Initialize secure CMS state',$bootstrap?'complete':'blocked',$bootstrap?$schemaLabel.' and a persisted CMS owner are ready.':'Finish the CLI database/owner bootstrap before browser authoring.','/cms/readiness.php','See readiness');
    $steps[]=onboardingStep('content','Initialize editable page content',$authority?'complete':($bootstrap?'ready':'blocked'),$authority?'Configured repository pages are initialized in canonical SQL.':($bootstrap?'Run the explicit initial repository reconciliation, then edit pages in Composer.':'Canonical content initialization waits for database bootstrap.'),'/cms/composer.php','Open Composer');
    $steps[]=onboardingStep('pages','Shape the starter pages',$authority?'ready':'blocked',$authority?'Edit copy in the live layout, adopt repository blocks into canonical composition, or create additional pages from saved blocks.':'Initialize canonical page content first.','/cms/composer.php','Open Composer',false);
    $steps[]=onboardingStep('branding','Make the visual identity yours',$db['brandingConfigured']?'complete':($bootstrap?'ready':'blocked'),$db['brandingConfigured']?'Canonical branding has been customized.':'Set the site name/mark and bounded starter design tokens.','/cms/branding.php','Open Branding',false);
    $steps[]=onboardingStep('navigation','Confirm how people move through the site',$db['navigationConfigured']?'complete':($bootstrap?'ready':'blocked'),$db['navigationConfigured']?'Primary navigation is canonical and configured.':'The starter works with derived navigation; save the order/labels when you are ready.','/cms/navigation.php','Open Navigation',false);
    $steps[]=onboardingStep('writing','Publish when you have something to say',$db['publishedPosts']>0?'complete':($bootstrap?'optional':'blocked'),$db['publishedPosts']>0?$db['publishedPosts'].' published item(s) are available.':'Writing is optional for launch; the starter already handles an empty index cleanly.','/cms/writing.php','Open Writing',false);
    $steps[]=onboardingStep('readiness','Finish with evidence',$readiness['ready']?'complete':'ready',$readiness['ready']?'All blocking core/adapter readiness checks pass.':($readiness['summary']['blockingFailures']??0).' blocking readiness check(s) remain.','/cms/readiness.php','Review Readiness');
    $required=array_values(array_filter($steps,fn($step)=>$step['required']));$complete=count(array_filter($required,fn($step)=>$step['state']==='complete'));
    $next=null;foreach($required as $step)if($step['state']!=='complete'){$next=$step['id'];break;}
    return [
        'ready'=>(bool)$readiness['ready']&&$siteConfig&&$identity['customized']&&$starterReady&&$bootstrap&&$authority,
        'generatedAt'=>gmdate('c'),
        'identity'=>$identity,
        'starter'=>$starter,
        'database'=>$db,
        'progress'=>['complete'=>$complete,'required'=>count($required),'next'=>$next],
        'steps'=>$steps,
        'readiness'=>$readiness,
        'principles'=>[
            'Repository-owned code, rendering behavior, and configuration change through Git branches/review.',
            'Accepted page composition, reusable block definitions, and authored content stay canonical in MySQL.',
            'Generated public files are outputs, not reverse source authority.',
            'Credentials, migrations, and deployment remain outside browser onboarding.',
        ],
    ];
}
