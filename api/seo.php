<?php
declare(strict_types=1);
require_once __DIR__.'/content-core.php';
require_once __DIR__.'/post-renderer.php';

/** Canonical SEO metadata and deterministic application to static HTML. */

function seoExpectedCanonical(string $path): string {
    $base=rtrim((string)siteConfigValue('site','base_url',''),'/');
    if($path==='index.html')return $base.'/';
    if(str_ends_with($path,'/index.html'))return $base.'/'.substr($path,0,-strlen('index.html'));
    return $base.'/'.ltrim($path,'/');
}

function seoCanonicalAllowed(string $canonical): bool {
    $expected=normalizedOrigin((string)siteConfigValue('site','base_url',''));$actual=normalizedOrigin($canonical);
    return $expected!==''&&$actual!==''&&hash_equals($expected,$actual);
}

function seoTargets(string $root): array {
    $targets=[];
    foreach(cmsManagedPages($root) as $path=>$label)if(cmsSafePublicFile($root,$path)!==null)$targets[$path]=$label;
    foreach(publishedPosts() as $post){$path=writingArticlePath((string)$post['slug'];);}
    return $targets;
}
