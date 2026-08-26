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
    foreach(publishedPosts() as $post){$path=writingArticlePath((string)$post['slug']);if(cmsSafePublicFile($root,$path)!==null)$targets[$path]=(string)$post['title'];}
    ksort($targets,SORT_STRING);return $targets;
}

function seoHtmlTitle(string $html): string {return preg_match('/<title>(.*?)<\/title>/is',$html,$m)?trim(html_entity_decode(strip_tags($m[1]),ENT_QUOTES|ENT_HTML5,'UTF-8')):'';}
function seoMetaValue(string $html,string $attribute,string $name): string {
    $a=preg_quote($attribute,'/');$n=preg_quote($name,'/');
    if(preg_match('/<meta\b(?=[^>]*\b'.$a.'=["\']'.$n.'["\'])[^>]*\bcontent=["\']([^"\']*)["\'][^>]*>/is',$html,$m))return html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8');
    if(preg_match('/<meta\b(?=[^>]*\bcontent=["\']([^"\']*)["\'])[^>]*\b'.$a.'=["\']'.$n.'["\'][^>]*>/is',$html,$m))return html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8');
    return '';
}
function seoHtmlCanonical(string $html): string {return preg_match('/<link\b(?=[^>]*\brel=["\']canonical["\'])[^>]*\bhref=["\']([^"\']+)["\'][^>]*>/is',$html,$m)?html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'):'';}

function seoReadOverride(string $path): ?array {
    $stmt=db()->prepare('SELECT * FROM seo_overrides WHERE page_path=?');$stmt->execute([$path]);$row=$stmt->fetch();if(!is_array($row))return null;
    return [
        'title'=>(string)$row['title'],'description'=>(string)$row['description'],'canonical'=>(string)$row['canonical'],'robots'=>(string)$row['robots'],
        'ogTitle'=>(string)$row['og_title'],'ogDescription'=>(string)$row['og_description'],'twitterTitle'=>(string)$row['twitter_title'],'twitterDescription'=>(string)$row['twitter_description'],
        'controls'=>[
            'index'=>(bool)$row['indexable'],'follow'=>(bool)$row['follow_links'],'archive'=>(bool)$row['archive_allowed'],'snippetLimit'=>(int)$row['snippet_limit'],
            'imagePreview'=>(string)$row['image_preview'],'videoPreviewLimit'=>(int)$row['video_preview_limit'],'canonicalMode'=>(string)$row['canonical_mode'],'socialMode'=>(string)$row['social_mode'],
        ],
        'updatedAt'=>dbIso((string)$row['updated_at']),
    ];
}

function seoReadTarget(string $root,string $path,string $label): array {
    $full=cmsSafePublicFile($root,$path);if($full===null)throw new RuntimeException('SEO target is outside the public site boundary.');$html=(string)file_get_contents($full);$override=seoReadOverride($path);
    $base=[
        'title'=>seoHtmlTitle($html),'description'=>seoMetaValue($html,'name','description'),'canonical'=>seoHtmlCanonical($html),'robots'=>seoMetaValue($html,'name','robots'),
        'ogTitle'=>seoMetaValue($html,'property','og:title'),'ogDescription'=>seoMetaValue($html,'property','og:description'),
        'twitterTitle'=>seoMetaValue($html,'name','twitter:title'),'twitterDescription'=>seoMetaValue($html,'name','twitter:description'),
    ];
    if($override)$base=array_merge($base,array_diff_key($override,['controls'=>true,'updatedAt'=>true]));
    $controls=$override['controls']??seoControlsFromRobots($base['robots'],(string)$base['canonical'],seoExpectedCanonical($path),$base);
    return ['path'=>$path,'label'=>$label,'expectedCanonical'=>seoExpectedCanonical($path),'seo'=>$base,'controls'=>$controls,'updatedAt'=>$override['updatedAt']??null];
}

function seoControlsFromRobots(string $robots,string $canonical,string $expected,array $data=[]): array {
    $lower=strtolower($robots);$has=fn(string $token):bool=>preg_match('/(?:^|,)\s*'.preg_quote($token,'/').'\s*(?:,|$)/',$lower)===1;
    $snippet=-1;if(preg_match('/max-snippet\s*:\s*(-?\d+)/',$lower,$m))$snippet=(int)$m[1];$video=-1;if(preg_match('/max-video-preview\s*:\s*(-?\d+)/',$lower,$m))$video=(int)$m[1];$image='large';if(preg_match('/max-image-preview\s*:\s*(none|standard|large)/',$lower,$m))$image=$m[1];
    $socialCustom=trim((string)($data['ogTitle']??''))!==trim((string)($data['title']??''))||trim((string)($data['ogDescription']??''))!==trim((string)($data['description']??''))||trim((string)($data['twitterTitle']??''))!==trim((string)($data['title']??''))||trim((string)($data['twitterDescription']??''))!==trim((string)($data['description']??''));
    return ['index'=>!$has('noindex'),'follow'=>!$has('nofollow'),'archive'=>!$has('noarchive'),'snippetLimit'=>$snippet,'imagePreview'=>$image,'videoPreviewLimit'=>$video,'canonicalMode'=>($expected!==''&&$canonical===$expected)?'self':'custom','socialMode'=>$socialCustom?'custom':'inherit'];
}

function seoBuildRobots(array $controls): string {
    $parts=[!empty($controls['index'])?'index':'noindex',!empty($controls['follow'])?'follow':'nofollow'];if(empty($controls['archive']))$parts[]='noarchive';
    $snippet=max(-1,min(1000,(int)($controls['snippetLimit']??-1)));$video=max(-1,min(3600,(int)($controls['videoPreviewLimit']??-1)));$image=(string)($controls['imagePreview']??'large');if(!in_array($image,['none','standard','large'],true))$image='large';
    $parts[]='max-snippet:'.$snippet;$parts[]='max-image-preview:'.$image;$parts[]='max-video-preview:'.$video;return implode(',',$parts);
}

function seoNormalizePayload(string $path,array $data,array $controls): array {
    $canonicalMode=in_array((string)($controls['canonicalMode']??'self'),['self','custom'],true)?(string)$controls['canonicalMode']:'self';$socialMode=in_array((string)($controls['socialMode']??'inherit'),['inherit','custom'],true)?(string)$controls['socialMode']:'inherit';
    $out=[
        'title'=>trim((string)($data['title']??'')),'description'=>trim((string)($data['description']??'')),
        'canonical'=>$canonicalMode==='self'?seoExpectedCanonical($path):trim((string)($data['canonical']??'')),'robots'=>seoBuildRobots($controls),
        'ogTitle'=>trim((string)($data['ogTitle']??'')),'ogDescription'=>trim((string)($data['ogDescription']??'')),
        'twitterTitle'=>trim((string)($data['twitterTitle']??'')),'twitterDescription'=>trim((string)($data['twitterDescription']??'')),
    ];
    if($socialMode==='inherit'){$out['ogTitle']=$out['title'];$out['ogDescription']=$out['description'];$out['twitterTitle']=$out['title'];$out['twitterDescription']=$out['description'];}
    if($out['title']===''||$out['description']==='')throw new RuntimeException('Title and description are required.');
    $limits=['title'=>512,'description'=>4000,'canonical'=>1024,'robots'=>255,'ogTitle'=>512,'ogDescription'=>4000,'twitterTitle'=>512,'twitterDescription'=>4000];foreach($limits as $key=>$limit)if(strlen((string)$out[$key])>$limit)throw new RuntimeException('One SEO field is too long.');
    $scheme=strtolower((string)(parse_url($out['canonical'],PHP_URL_SCHEME)??''));if(!filter_var($out['canonical'],FILTER_VALIDATE_URL)||!in_array($scheme,['http','https'],true)||!seoCanonicalAllowed($out['canonical']))throw new RuntimeException('Canonical must be an absolute URL on the configured public origin.');
    $normalizedControls=['index'=>!empty($controls['index']),'follow'=>!empty($controls['follow']),'archive'=>!empty($controls['archive']),'snippetLimit'=>max(-1,min(1000,(int)($controls['snippetLimit']??-1))),'imagePreview'=>in_array((string)($controls['imagePreview']??'large'),['none','standard','large'],true)?(string)$controls['imagePreview']:'large','videoPreviewLimit'=>max(-1,min(3600,(int)($controls['videoPreviewLimit']??-1))),'canonicalMode'=>$canonicalMode,'socialMode'=>$socialMode];
    return ['seo'=>$out,'controls'=>$normalizedControls];
}

function seoWriteOverride(string $path,array $seo,array $controls): void {
    $stmt=db()->prepare('INSERT INTO seo_overrides (page_path,title,description,canonical,robots,og_title,og_description,twitter_title,twitter_description,indexable,follow_links,archive_allowed,snippet_limit,image_preview,video_preview_limit,canonical_mode,social_mode,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),canonical=VALUES(canonical),robots=VALUES(robots),og_title=VALUES(og_title),og_description=VALUES(og_description),twitter_title=VALUES(twitter_title),twitter_description=VALUES(twitter_description),indexable=VALUES(indexable),follow_links=VALUES(follow_links),archive_allowed=VALUES(archive_allowed),snippet_limit=VALUES(snippet_limit),image_preview=VALUES(image_preview),video_preview_limit=VALUES(video_preview_limit),canonical_mode=VALUES(canonical_mode),social_mode=VALUES(social_mode),updated_at=UTC_TIMESTAMP()');
    $stmt->execute([$path,$seo['title'],$seo['description'],$seo['canonical'],$seo['robots'],$seo['ogTitle'],$seo['ogDescription'],$seo['twitterTitle'],$seo['twitterDescription'],$controls['index']?1:0,$controls['follow']?1:0,$controls['archive']?1:0,$controls['snippetLimit'],$controls['imagePreview'],$controls['videoPreviewLimit'],$controls['canonicalMode'],$controls['socialMode']]);
}

function seoSetTitle(string $html,string $title): string {
    $tag='<title>'.postEscape($title).'</title>';if(preg_match('/<title>.*?<\/title>/is',$html))return preg_replace('/<title>.*?<\/title>/is',$tag,$html,1)??$html;return preg_replace('/<\/head>/i',$tag."\n</head>",$html,1)??$html;
}
function seoSetMeta(string $html,string $attribute,string $name,string $value): string {
    $attr=$attribute==='property'?'property':'name';$tag='<meta '.$attr.'="'.postEscape($name).'" content="'.postEscape($value).'">';$a=preg_quote($attr,'/');$n=preg_quote($name,'/');$pattern='/<meta\b(?=[^>]*\b'.$a.'=["\']'.$n.'["\'])[^>]*>/is';if(preg_match($pattern,$html))return preg_replace($pattern,$tag,$html,1)??$html;return preg_replace('/<\/head>/i',$tag."\n</head>",$html,1)??$html;
}
function seoSetCanonical(string $html,string $canonical): string {
    $tag='<link rel="canonical" href="'.postEscape($canonical).'">';$pattern='/<link\b(?=[^>]*\brel=["\']canonical["\'])[^>]*>/is';if(preg_match($pattern,$html))return preg_replace($pattern,$tag,$html,1)??$html;return preg_replace('/<\/head>/i',$tag."\n</head>",$html,1)??$html;
}
function seoApplyToHtml(string $html,array $seo): string {
    $html=seoSetTitle($html,(string)$seo['title']);$html=seoSetMeta($html,'name','description',(string)$seo['description']);$html=seoSetMeta($html,'name','robots',(string)$seo['robots']);$html=seoSetCanonical($html,(string)$seo['canonical']);
    $html=seoSetMeta($html,'property','og:title',(string)$seo['ogTitle']);$html=seoSetMeta($html,'property','og:description',(string)$seo['ogDescription']);$html=seoSetMeta($html,'property','og:url',(string)$seo['canonical']);
    $html=seoSetMeta($html,'name','twitter:title',(string)$seo['twitterTitle']);$html=seoSetMeta($html,'name','twitter:description',(string)$seo['twitterDescription']);return $html;
}

function seoProjectTarget(string $root,string $path): bool {
    $override=seoReadOverride($path);if(!$override)return false;$full=cmsSafePublicFile($root,$path);if($full===null)return false;$html=(string)file_get_contents($full);cmsAtomicWrite($full,seoApplyToHtml($html,$override));return true;
}
function seoProjectAll(string $root): int {$count=0;foreach(seoTargets($root) as $path=>$label)if(seoProjectTarget($root,$path))$count++;return $count;}
