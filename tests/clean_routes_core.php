<?php
declare(strict_types=1);

function siteConfigValue(string $section,string $key,mixed $default=null): mixed {
    if($section==='projection'&&$key==='clean_managed_routes')return true;
    if($section==='site'&&$key==='base_url')return 'https://example.test';
    return $default;
}
require_once dirname(__DIR__).'/api/page-routes.php';

function expectRoute(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}

expectRoute(pageRoutesEnabled(),'clean routes should be enabled by config');
expectRoute(pageRouteNormalizeKey('about.html')==='about.html','flat managed key should normalize');
expectRoute(pageRouteNormalizeKey('docs/start.html')==='docs/start.html','nested managed key should normalize');
expectRoute(pageRouteNormalizeKey('../escape.html')===null,'parent traversal must fail closed');
expectRoute(pageRouteKeyToRoute('index.html')==='/','home route should remain root');
expectRoute(pageRouteKeyToRoute('about.html')==='/about/','flat page should project to clean route');
expectRoute(pageRouteKeyToRoute('docs/start.html')==='/docs/start/','nested page should project cleanly');
expectRoute(pageRouteKeyToRoute('docs/index.html')==='/docs/','nested index should collapse to directory route');
expectRoute(pageRouteProjectionRelativePath('about.html')==='about/index.html','projection path should use directory index');

$managed=pageRouteManagedKeySet(['index.html','about.html','docs/start.html']);
expectRoute(pageRoutePathToManagedKey('/about/',$managed)==='about.html','clean route should resolve to canonical key');
expectRoute(pageRoutePathToManagedKey('/about.html',$managed)==='about.html','legacy key should still resolve internally');
expectRoute(pageRouteRewriteManagedUrl('/about.html?x=1#top','',$managed)==='/about/?x=1#top','query and fragment should survive rewrite');
expectRoute(pageRouteRewriteManagedUrl('https://example.test/about.html','',$managed)==='https://example.test/about/','same-site absolute URL should rewrite');
expectRoute(pageRouteRewriteManagedUrl('https://other.test/about.html','',$managed)==='https://other.test/about.html','external absolute URL should not rewrite');

$collision=false;
try{pageRouteManagedKeySet(['docs.html','docs/index.html']);}catch(RuntimeException $e){$collision=true;}
expectRoute($collision,'public route collisions must fail closed');

echo "clean route core behavior ok\n";
