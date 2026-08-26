<?php
declare(strict_types=1);
require_once __DIR__.'/presentation-core.php';

/** Deterministic site-wide SEO/social/schema projection. Canonical authored fields stay in seo_overrides. */

function seoProjectionConfig(): array {
    $base=rtrim((string)siteConfigValue('site','base_url',''),'/');
    if($base===''||normalizedOrigin($base)==='')throw new RuntimeException('SEO projection requires a configured public origin.');
    $site=trim((string)siteConfigValue('site','name','Site'));if($site==='')$site='Site';
    $author=trim((string)siteConfigValue('seo','author',''));if($author==='')$author=trim((string)siteConfigValue('site','owner_display_name',$site));if($author==='')$author=$site;
    $social=trim((string)siteConfigValue('seo','social_image',''));if($social!==''&&str_starts_with($social,'/'))$social=$base.$social;
    if($social!==''&&!filter_var($social,FILTER_VALIDATE_URL))$social='';
    $locale=trim((string)siteConfigValue('seo','locale','en_US'));if($locale==='')$locale='en_US';
    $language=trim((string)siteConfigValue('seo','language','en-US'));if($language==='')$language='en-US';
    return ['base'=>$base,'site'=>$site,'author'=>$author,'socialImage'=>$social,'locale'=>$locale,'language'=>$language];
}
function seoProjectionHasValidSchema(string $html): bool {
    if(!preg_match_all('/<script\b[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',$html,$m))return false;
    foreach($m[1] as $raw){$decoded=json_decode(html_entity_decode((string)$raw,ENT_QUOTES|ENT_HTML5,'UTF-8'),true);if(is_array($decoded))return true;}return false;
}
function seoProjectionSchemaType(string $path): string {
    if($path==='writing.html')return 'CollectionPage';
    if(str_starts_with($path,'writing/')&&str_ends_with($path,'/index.html'))return 'Article';
    return 'WebPage';
}
function seoProjectionFallbackSchema(string $path,string $html): string {
    if(seoProjectionHasValidSchema($html))return $html;$cfg=seoProjectionConfig();$canonical=seoHtmlCanonical($html);if($canonical==='')$canonical=seoExpectedCanonical($path);$title=seoHtmlTitle($html);$description=seoMetaValue($html,'name','description');if($canonical===''||$title==='')return $html;
    $schema=['@context'=>'https://schema.org','@type'=>seoProjectionSchemaType($path),'@id'=>$canonical.'#webpage','url'=>$canonical,'name'=>$title,'description'=>$description,'inLanguage'=>$cfg['language'],'isPartOf'=>['@type'=>'WebSite','@id'=>$cfg['base'].'/#website','url'=>$cfg['base'].'/','name'=>$cfg['site']]];
    if($cfg['author']!=='')$schema['author']=['@type'=>'Person','name'=>$cfg['author']];
    $json=json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if($json===false)throw new RuntimeException('SEO fallback schema could not be encoded.');
    return preg_replace('/<\/head>/i','<script type="application/ld+json">'.$json.'</script>'."\n</head>",$html,1)??$html;
}
function seoProjectEnhancements(string $path,string $html,bool $inheritSocial=true): string {
    $cfg=seoProjectionConfig();$canonical=seoHtmlCanonical($html);if($canonical==='')$canonical=seoExpectedCanonical($path);$title=seoHtmlTitle($html);$description=seoMetaValue($html,'name','description');
    if($cfg['author']!=='')$html=seoSetMeta($html,'name','author',$cfg['author']);
    $html=seoSetMeta($html,'property','og:type',str_starts_with($path,'writing/')?'article':'website');
    $html=seoSetMeta($html,'property','og:locale',$cfg['locale']);$html=seoSetMeta($html,'property','og:site_name',$cfg['site']);
    $ogTitle=$inheritSocial?$title:(seoMetaValue($html,'property','og:title')?:$title);$ogDescription=$inheritSocial?$description:(seoMetaValue($html,'property','og:description')?:$description);
    $html=seoSetMeta($html,'property','og:title',$ogTitle);$html=seoSetMeta($html,'property','og:description',$ogDescription);$html=seoSetMeta($html,'property','og:url',$canonical);
    $ogImage=seoMetaValue($html,'property','og:image');if($ogImage==='')$ogImage=$cfg['socialImage'];if($ogImage!==''){$html=seoSetMeta($html,'property','og:image',$ogImage);$html=seoSetMeta($html,'property','og:image:alt',seoMetaValue($html,'property','og:image:alt')?:$title);}
    $html=seoSetMeta($html,'name','twitter:card',$ogImage!==''?'summary_large_image':'summary');
    $html=seoSetMeta($html,'name','twitter:title',$inheritSocial?$title:(seoMetaValue($html,'name','twitter:title')?:$title));$html=seoSetMeta($html,'name','twitter:description',$inheritSocial?$description:(seoMetaValue($html,'name','twitter:description')?:$description));
    if($ogImage!==''){$html=seoSetMeta($html,'name','twitter:image',seoMetaValue($html,'name','twitter:image')?:$ogImage);$html=seoSetMeta($html,'name','twitter:image:alt',seoMetaValue($html,'name','twitter:image:alt')?:$title);}
    return seoProjectionFallbackSchema($path,$html);
}
function seoProjectionPublicHtmlFiles(string $root): array {return presentationPublicHtmlFiles($root);}
function seoProjectionRelativePath(string $root,string $full): string {
    $base=rtrim(str_replace('\\','/',realpath($root)?:$root),'/');$normalized=str_replace('\\','/',$full);return ltrim(substr($normalized,strlen($base)),'/');
}
function seoProjectAllPublicPages(string $root): array {
    $processed=0;$changed=0;$overrides=0;
    foreach(seoProjectionPublicHtmlFiles($root) as $full){$path=seoProjectionRelativePath($root,$full);if($path==='')continue;$html=(string)file_get_contents($full);$next=$html;$override=seoReadOverride($path);$inherit=true;if($override){$next=seoApplyToHtml($next,$override);$inherit=(string)($override['controls']['socialMode']??'inherit')!=='custom';$overrides++;}$next=seoProjectEnhancements($path,$next,$inherit);if($next!==$html){cmsAtomicWrite($full,$next);$changed++;}$processed++;}
    return ['processed'=>$processed,'changed'=>$changed,'overrides'=>$overrides];
}
