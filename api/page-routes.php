<?php
declare(strict_types=1);

/**
 * Clean public-route helpers for CMS-managed pages.
 *
 * Canonical page keys remain repository/database identifiers such as about.html.
 * Translation happens only at the deterministic public projection boundary.
 */
function pageRoutesEnabled(): bool {
    return (bool)siteConfigValue('projection','clean_managed_routes',false);
}

function pageRouteNormalizeKey(string $key): ?string {
    $key=ltrim(str_replace('\\','/',trim($key)),'/');
    if($key===''||str_contains($key,"\0")||!str_ends_with(strtolower($key),'.html'))return null;
    $parts=explode('/',$key);
    foreach($parts as $part){
        if($part===''||$part==='.'||$part==='..')return null;
        if(!preg_match('/^[a-z0-9][a-z0-9-]{0,79}(?:\.html)?$/',$part))return null;
    }
    return $key;
}

function pageRouteKeyToRoute(string $key): string {
    $key=pageRouteNormalizeKey($key)??$key;
    if($key==='index.html')return '/';
    if(str_ends_with($key,'/index.html'))return '/'.substr($key,0,-strlen('index.html'));
    if(str_ends_with($key,'.html'))return '/'.substr($key,0,-5).'/';
    return '/'.trim($key,'/').'/';
}

function pageRouteProjectionRelativePath(string $key): string {
    $route=pageRouteKeyToRoute($key);
    return $route==='/'?'index.html':trim($route,'/').'/index.html';
}

function pageRouteProjectionPath(string $root,string $key): string {
    return rtrim($root,'/\\').'/'.pageRouteProjectionRelativePath($key);
}

function pageRouteBaseUrl(string $root=''): string {
    return rtrim((string)siteConfigValue('site','base_url',''),'/');
}

function pageRoutePublicUrl(string $root,string $key): string {
    $base=pageRouteBaseUrl($root);$route=pageRouteKeyToRoute($key);
    return $base!==''?$base.$route:$route;
}

function pageRouteManagedKeySet(array $keys): array {
    $out=[];$routes=[];
    foreach($keys as $key){
        $normalized=pageRouteNormalizeKey((string)$key);if($normalized===null)continue;
        $route=pageRouteKeyToRoute($normalized);
        if(isset($routes[$route])&&!hash_equals($routes[$route],$normalized))throw new RuntimeException('Managed page keys collide on public route '.$route.'.');
        $routes[$route]=$normalized;$out[$normalized]=true;
    }
    return $out;
}

function pageRoutePathToManagedKey(string $path,array $managedKeys): ?string {
    $path=(string)(parse_url($path,PHP_URL_PATH)??$path);
    if($path===''||$path==='/')return isset($managedKeys['index.html'])?'index.html':null;
    $trim=ltrim($path,'/');
    if(isset($managedKeys[$trim]))return $trim;
    foreach(array_keys($managedKeys) as $key)if(pageRouteKeyToRoute($key)==='/'.rtrim($trim,'/').'/')return $key;
    return null;
}

function pageRouteRewriteManagedUrl(string $value,string $root,array $managedKeys): string {
    $value=trim($value);if($value==='')return $value;
    if($value[0]==='#'||preg_match('#^(?:data:|mailto:|tel:|javascript:|//)#i',$value))return $value;
    $fragment='';$query='';$baseValue=$value;
    $hashPos=strpos($baseValue,'#');if($hashPos!==false){$fragment=substr($baseValue,$hashPos);$baseValue=substr($baseValue,0,$hashPos);}
    $queryPos=strpos($baseValue,'?');if($queryPos!==false){$query=substr($baseValue,$queryPos);$baseValue=substr($baseValue,0,$queryPos);}
    $siteBase=pageRouteBaseUrl($root);$absoluteBase='';$path=$baseValue;
    if(preg_match('#^https?://#i',$baseValue)){
        $parsed=parse_url($baseValue);if(!is_array($parsed))return $value;
        $origin=(string)($parsed['scheme']??'').'://'.(string)($parsed['host']??'');if(isset($parsed['port']))$origin.=':'.(int)$parsed['port'];
        $siteOrigin=$siteBase!==''?(string)(parse_url($siteBase,PHP_URL_SCHEME)??'').'://'.(string)(parse_url($siteBase,PHP_URL_HOST)??''):'';
        $sitePort=parse_url($siteBase,PHP_URL_PORT);if($siteOrigin!==''&&is_int($sitePort))$siteOrigin.=':'.$sitePort;
        if($siteOrigin===''||strcasecmp($origin,$siteOrigin)!==0)return $value;
        $absoluteBase=$siteBase;$path=(string)($parsed['path']??'/');
    }
    $key=pageRoutePathToManagedKey($path,$managedKeys);if($key===null)return $value;
    return $absoluteBase.pageRouteKeyToRoute($key).$query.$fragment;
}
