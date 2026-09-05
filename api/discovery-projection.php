<?php
declare(strict_types=1);
require_once __DIR__.'/page-projection.php';

/** Derived public discovery. Published HTML remains reader-facing authority. */
function discoveryOneLine(mixed $value): string {
    $text=trim(html_entity_decode((string)$value,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    return trim(preg_replace('/\s+/u',' ',$text)??$text);
}
function discoveryHtmlAttribute(string $tag,string $attribute): string {
    $q=preg_quote($attribute,'/');
    if(preg_match('/\b'.$q.'\s*=\s*(["\'])(.*?)\1/is',$tag,$m))return discoveryOneLine($m[2]);
    if(preg_match('/\b'.$q.'\s*=\s*([^\s>]+)/i',$tag,$m))return discoveryOneLine($m[1]);
    return '';
}
function discoveryMetaContent(string $html,string $name): string {
    if(!preg_match_all('/<meta\b[^>]*>/i',$html,$tags))return '';
    foreach($tags[0] as $tag){$candidate=strtolower(discoveryHtmlAttribute((string)$tag,'name'));if($candidate==='')$candidate=strtolower(discoveryHtmlAttribute((string)$tag,'property'));if($candidate===strtolower($name))return discoveryHtmlAttribute((string)$tag,'content');}
    return '';
}
function discoveryCanonical(string $html): string {
    if(!preg_match_all('/<link\b[^>]*>/i',$html,$tags))return '';
    foreach($tags[0] as $tag){$rel=strtolower(discoveryHtmlAttribute((string)$tag,'rel'));if(in_array('canonical',preg_split('/\s+/',$rel)?:[],true))return discoveryHtmlAttribute((string)$tag,'href');}
    return '';
}
function discoveryTitle(string $html): string {
    return preg_match('/<title\b[^>]*>(.*?)<\/title>/is',$html,$m)?discoveryOneLine(strip_tags((string)$m[1])):'';
}
function discoveryBaseUrl(?array $site=null): string {
    $base=rtrim(trim((string)($site['base_url']??siteConfigValue('site','base_url',''))),'/');
    if($base===''||filter_var($base,FILTER_VALIDATE_URL)===false)throw new RuntimeException('A valid public site base_url is required for discovery projection.');
    $scheme=strtolower((string)parse_url($base,PHP_URL_SCHEME));if(!in_array($scheme,['http','https'],true))throw new RuntimeException('Public discovery requires an HTTP(S) base URL.');
    $parts=parse_url($base);if(!is_array($parts)||isset($parts['user'])||isset($parts['pass'])||isset($parts['query'])||isset($parts['fragment']))throw new RuntimeException('Public discovery base URL must not contain credentials, query, or fragment.');
    return $base;
}
function discoveryUrlForRelative(string $base,string $rel): string {
    $rel=str_replace('\\','/',$rel);if($rel==='index.html')return $base.'/';if(str_ends_with($rel,'/index.html'))return $base.'/'.substr($rel,0,-10);return $base.'/'.ltrim($rel,'/');
}
function discoverySameSite(string $base,string $url): bool {
    $a=parse_url($base);$b=parse_url($url);
    if(!is_array($a)||!is_array($b)||isset($b['user'])||isset($b['pass'])||isset($b['query'])||isset($b['fragment']))return false;
    $scheme=strtolower((string)($a['scheme']??''));$other=strtolower((string)($b['scheme']??''));
    if(!in_array($scheme,['http','https'],true)||$scheme!==$other||strtolower((string)($a['host']??''))!==strtolower((string)($b['host']??'')))return false;
    if(($a['port']??($scheme==='https'?443:80))!==($b['port']??($other==='https'?443:80)))return false;
    $prefix=rtrim((string)($a['path']??''),'/');$path=(string)($b['path']??'/');
    if($prefix!==''&&$path!==$prefix&&!str_starts_with($path,$prefix.'/'))return false;
    $relative=ltrim(substr($path,strlen($prefix)),'/');
    return $relative===''||discoveryPublicRelativePath(rtrim($relative,'/'));
}
/** No operational/source trees or encoded traversal become public discovery. */
function discoveryPublicRelativePath(string $relative): bool {
    if($relative===''||str_starts_with($relative,'/')||str_contains($relative,'\\')||preg_match('/[\x00-\x20%?#\x7f]/',$relative))return false;
    $parts=explode('/',$relative);
    $private=['api','cms','setup','database','tests','tools','scripts','dist','runtime','config','docs','templates','adapters','uploads','vendor','node_modules','private','drafts'];
    if(in_array(strtolower($parts[0]),$private,true))return false;
    foreach($parts as $part)if($part===''||str_starts_with($part,'.')||in_array(strtolower($part),['private','drafts'],true))return false;
    return true;
}
function discoveryContainedFile(string $root,string $relative): ?string {
    if(!discoveryPublicRelativePath($relative))return null;
    $real=realpath($root);if($real===false)return null;
    $path=$real;foreach(explode('/',$relative) as $part){$path.='/'.$part;if(is_link($path))return null;}
    return is_file($path)&&realpath($path)===$path?$path:null;
}
function discoveryPublicHtmlFiles(string $root): array {
    $files=[];foreach(presentationPublicHtmlFiles($root) as $relative=>$unused){$path=discoveryContainedFile($root,(string)$relative);if($path!==null)$files[$relative]=$path;}
    return $files;
}
function discoveryPublicPages(string $root,string $base): array {
    $byUrl=[];
    foreach(discoveryPublicHtmlFiles($root) as $rel=>$full){
        $html=(string)file_get_contents($full);$robots=strtolower(discoveryMetaContent($html,'robots'));if(preg_match('/(?:^|[,\s])noindex(?:$|[,\s])/',$robots))continue;
        $canonical=discoveryCanonical($html);$url=$canonical!==''&&filter_var($canonical,FILTER_VALIDATE_URL)!==false?$canonical:discoveryUrlForRelative($base,$rel);if(!discoverySameSite($base,$url))continue;
        $title=discoveryTitle($html);if($title==='')$title=basename($rel)==='index.html'?(dirname($rel)==='.'?'Home':basename(dirname($rel))):pathinfo($rel,PATHINFO_FILENAME);
        $record=['title'=>$title,'url'=>$url,'description'=>discoveryMetaContent($html,'description'),'sourcePath'=>$rel];
        if(!isset($byUrl[$url])||str_ends_with($rel,'/index.html'))$byUrl[$url]=$record;
    }
    ksort($byUrl,SORT_STRING);return array_values($byUrl);
}
function discoveryBuildIndex(string $root,?array $site=null): array {
    $site=$site??['name'=>siteConfigValue('site','name','Site'),'description'=>siteConfigValue('site','description',''),'base_url'=>siteConfigValue('site','base_url','')];$base=discoveryBaseUrl($site);$name=discoveryOneLine($site['name']??'Site')?:'Site';$description=discoveryOneLine($site['description']??'');$pages=discoveryPublicPages($root,$base);
    if($description==='')foreach($pages as $page)if(($page['url']??'')===$base.'/'){$description=discoveryOneLine($page['description']??'');break;}
    $stable=['version'=>1,'site'=>['name'=>$name,'description'=>$description,'baseUrl'=>$base],'pages'=>$pages];$stable['revision']=hash('sha256',(string)json_encode($stable,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));return $stable;
}
function discoveryProjectSitemaps(string $root,array $index): array {
    $urls=[];foreach($index['pages']??[] as $page){$url=trim((string)($page['url']??''));if($url!=='')$urls[$url]=true;}$urls=array_keys($urls);sort($urls,SORT_STRING);
    $txt=$urls?implode("\n",$urls)."\n":"";pageProjectionWrite($root.'/sitemap.txt',$txt);
    $xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";foreach($urls as $url)$xml.='  <url><loc>'.htmlspecialchars($url,ENT_XML1|ENT_QUOTES,'UTF-8')."</loc></url>\n";$xml.="</urlset>\n";pageProjectionWrite($root.'/sitemap.xml',$xml);return ['urls'=>count($urls)];
}
function discoveryProject(string $root,?array $site=null): array {
    $index=discoveryBuildIndex($root,$site);$json=json_encode($index,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(!is_string($json))throw new RuntimeException('Could not encode public discovery index.');pageProjectionWrite($root.'/site-index.json',$json."\n");$sitemaps=discoveryProjectSitemaps($root,$index);return ['pages'=>count($index['pages']??[]),'revision'=>$index['revision'],'sitemapUrls'=>$sitemaps['urls']];
}
