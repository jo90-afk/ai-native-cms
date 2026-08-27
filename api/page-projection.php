<?php
declare(strict_types=1);
require_once __DIR__.'/presentation-core.php';
require_once __DIR__.'/page-routes.php';

function pageProjectionEnsureDirectory(string $target): void {
    $dir=dirname($target);
    if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('Could not create page projection directory: '.$dir);
}
function pageProjectionWrite(string $path,string $content): void {
    pageProjectionEnsureDirectory($path);
    cmsAtomicWrite($path,$content);
}
function pageProjectionNormalizeRelative(string $baseDir,string $path): string {
    $parts=[];
    foreach(explode('/',trim($baseDir.'/'.$path,'/')) as $part){if($part===''||$part==='.')continue;if($part==='..'){array_pop($parts);continue;}$parts[]=$part;}
    return implode('/',$parts);
}
function pageProjectionRebaseUrl(string $value,string $sourceRel,string $root,array $managedKeys): string {
    $value=trim(html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    if($value===''||$value[0]==='#'||preg_match('#^(?:data:|mailto:|tel:|javascript:|//)#i',$value))return $value;
    if(preg_match('#^https?://#i',$value))return pageRouteRewriteManagedUrl($value,$root,$managedKeys);
    $fragment='';$query='';$path=$value;
    $hash=strpos($path,'#');if($hash!==false){$fragment=substr($path,$hash);$path=substr($path,0,$hash);}
    $q=strpos($path,'?');if($q!==false){$query=substr($path,$q);$path=substr($path,0,$q);}if($path==='')return $value;
    $trailingSlash=str_ends_with($path,'/');
    $resolved=str_starts_with($path,'/')?ltrim($path,'/'):pageProjectionNormalizeRelative(dirname($sourceRel)==='.'?'':dirname($sourceRel),$path);
    $managed=pageRouteRewriteManagedUrl('/'.$resolved,$root,$managedKeys);if($managed!=='/'.$resolved)return $managed.$query.$fragment;
    return '/'.$resolved.($trailingSlash&&$resolved!==''?'/':'').$query.$fragment;
}
function pageProjectionRewriteManagedReference(string $value,string $sourceRel,string $root,array $managedKeys): string {
    $raw=trim(html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8'));
    if($raw===''||$raw[0]==='#'||preg_match('#^(?:data:|mailto:|tel:|javascript:|//)#i',$raw))return $raw;
    if(preg_match('#^https?://#i',$raw))return pageRouteRewriteManagedUrl($raw,$root,$managedKeys);
    $fragment='';$query='';$path=$raw;
    $hash=strpos($path,'#');if($hash!==false){$fragment=substr($path,$hash);$path=substr($path,0,$hash);}
    $q=strpos($path,'?');if($q!==false){$query=substr($path,$q);$path=substr($path,0,$q);}
    if($path==='')return $raw;
    $resolved=str_starts_with($path,'/')?ltrim($path,'/'):pageProjectionNormalizeRelative(dirname($sourceRel)==='.'?'':dirname($sourceRel),$path);
    $key=pageRoutePathToManagedKey('/'.$resolved,$managedKeys);
    return $key===null?$raw:pageRouteKeyToRoute($key).$query.$fragment;
}
function pageProjectionRewriteHtml(string $html,string $sourceRel,string $root,array $managedKeys,bool $relocated): string {
    if($relocated){
        $html=preg_replace_callback('/\b(href|src|action|poster)=(["\'])([^"\']+)\2/i',function($m)use($sourceRel,$root,$managedKeys){$next=pageProjectionRebaseUrl((string)$m[3],$sourceRel,$root,$managedKeys);return $m[1].'='.$m[2].htmlspecialchars($next,ENT_QUOTES|ENT_HTML5,'UTF-8').$m[2];},$html)??$html;
        $html=preg_replace_callback('/\bsrcset=(["\'])([^"\']+)\1/i',function($m)use($sourceRel,$root,$managedKeys){$items=[];foreach(explode(',',(string)$m[2]) as $part){$part=trim($part);if($part==='')continue;$bits=preg_split('/\s+/', $part,2);$url=pageProjectionRebaseUrl((string)($bits[0]??''),$sourceRel,$root,$managedKeys);$items[]=$url.(isset($bits[1])?' '.$bits[1]:'');}return 'srcset='.$m[1].htmlspecialchars(implode(', ',$items),ENT_QUOTES|ENT_HTML5,'UTF-8').$m[1];},$html)??$html;
    } else {
        $html=preg_replace_callback('/\b(href|action)=(["\'])([^"\']+)\2/i',function($m)use($sourceRel,$root,$managedKeys){$next=pageProjectionRewriteManagedReference((string)$m[3],$sourceRel,$root,$managedKeys);return $m[1].'='.$m[2].htmlspecialchars($next,ENT_QUOTES|ENT_HTML5,'UTF-8').$m[2];},$html)??$html;
    }
    $html=preg_replace_callback('/(<meta\b(?=[^>]*(?:property|name)=["\'](?:og:url|twitter:url)["\'])[^>]*\bcontent=)(["\'])([^"\']+)(\2)/i',function($m)use($root,$managedKeys){$raw=html_entity_decode((string)$m[3],ENT_QUOTES|ENT_HTML5,'UTF-8');$next=pageRouteRewriteManagedUrl($raw,$root,$managedKeys);return $m[1].$m[2].htmlspecialchars($next,ENT_QUOTES|ENT_HTML5,'UTF-8').$m[4];},$html)??$html;
    $base=pageRouteBaseUrl($root);if($base!=='')foreach(array_keys($managedKeys) as $key)$html=str_replace($base.'/'.ltrim($key,'/'),$base.pageRouteKeyToRoute($key),$html);
    return $html;
}
function pageProjectionRewriteManagedReferences(string $root,array $managedKeys): int {
    $changed=0;
    foreach(presentationPublicHtmlFiles($root) as $rel=>$full){
        if($rel!=='index.html'&&isset($managedKeys[$rel]))continue;
        $before=(string)file_get_contents($full);$after=pageProjectionRewriteHtml($before,$rel,$root,$managedKeys,false);
        if($after!==$before){pageProjectionWrite($full,$after);$changed++;}
    }
    return $changed;
}
function pageProjectionRewriteStructured(mixed $value,string $root,array $managedKeys): mixed {
    if(is_string($value))return pageRouteRewriteManagedUrl($value,$root,$managedKeys);if(!is_array($value))return $value;foreach($value as $key=>$child)$value[$key]=pageProjectionRewriteStructured($child,$root,$managedKeys);return $value;
}
function pageProjectionCanonicalizeSitemapText(string $text,string $root,array $managedKeys): string {
    $seen=[];$lines=[];
    foreach(preg_split('/\R/',trim($text))?:[] as $line){$line=trim($line);if($line==='')continue;$next=pageRouteRewriteManagedUrl($line,$root,$managedKeys);if(isset($seen[$next]))continue;$seen[$next]=true;$lines[]=$next;}
    return $lines?implode("\n",$lines)."\n":$text;
}
function pageProjectionCanonicalizeSitemapXml(string $xml,string $root,array $managedKeys): string {
    $seen=[];
    return preg_replace_callback('#<url>\s*.*?</url>#s',function($match)use($root,$managedKeys,&$seen){$block=(string)$match[0];if(!preg_match('#<loc>(.*?)</loc>#s',$block,$locMatch))return $block;$loc=html_entity_decode(trim((string)$locMatch[1]),ENT_QUOTES|ENT_XML1,'UTF-8');$next=pageRouteRewriteManagedUrl($loc,$root,$managedKeys);if(isset($seen[$next]))return '';$seen[$next]=true;$escaped=htmlspecialchars($next,ENT_QUOTES|ENT_XML1,'UTF-8');return preg_replace_callback('#(<loc>).*?(</loc>)#s',fn($m)=>$m[1].$escaped.$m[2],$block,1)??$block;},$xml)??$xml;
}
function pageProjectionRewriteMetadata(string $root,array $managedKeys): int {
    $changed=0;$jsonPath=$root.'/site-index.json';
    if(is_file($jsonPath)){$data=json_decode((string)file_get_contents($jsonPath),true);if(is_array($data)){$next=pageProjectionRewriteStructured($data,$root,$managedKeys);$json=json_encode($next,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(is_string($json)){$json.="\n";$before=(string)file_get_contents($jsonPath);if($json!==$before){pageProjectionWrite($jsonPath,$json);$changed++;}}}}
    foreach(['sitemap.xml','sitemap.txt','llms.txt','llms-full.txt','feed.xml'] as $rel){$path=$root.'/'.$rel;if(!is_file($path))continue;$before=(string)file_get_contents($path);if($rel==='sitemap.xml')$after=pageProjectionCanonicalizeSitemapXml($before,$root,$managedKeys);elseif($rel==='sitemap.txt')$after=pageProjectionCanonicalizeSitemapText($before,$root,$managedKeys);else{$after=$before;$base=pageRouteBaseUrl($root);foreach(array_keys($managedKeys) as $key){$route=pageRouteKeyToRoute($key);$legacy='/'.$key;if($base!=='')$after=str_replace($base.$legacy,$base.$route,$after);$after=str_replace($legacy,$route,$after);}}if($after!==$before){pageProjectionWrite($path,$after);$changed++;}}
    return $changed;
}
function pageProjectionProjectManagedCleanRoutes(string $root,array $keys): array {
    $managedKeys=pageRouteManagedKeySet($keys);if(!$managedKeys)return ['managed'=>0,'projected'=>0,'rewritten'=>0,'references'=>0,'metadata'=>0];
    $projected=0;$rewritten=0;
    foreach(array_keys($managedKeys) as $key){$source=$root.'/'.$key;if(!is_file($source))continue;$before=(string)file_get_contents($source);$targetRel=pageRouteProjectionRelativePath($key);$target=$root.'/'.$targetRel;$relocated=$targetRel!==$key;$after=pageProjectionRewriteHtml($before,$key,$root,$managedKeys,$relocated);if($relocated){pageProjectionWrite($target,$after);$projected++;}elseif($after!==$before){pageProjectionWrite($target,$after);$rewritten++;}}
    $references=pageProjectionRewriteManagedReferences($root,$managedKeys);$metadata=pageProjectionRewriteMetadata($root,$managedKeys);
    return ['managed'=>count($managedKeys),'projected'=>$projected,'rewritten'=>$rewritten,'references'=>$references,'metadata'=>$metadata];
}
