<?php
declare(strict_types=1);
$mapFile=__DIR__.'/__redirect-map.php';$map=is_file($mapFile)?require $mapFile:[];if(!is_array($map))$map=[];
$uri=(string)($_SERVER['REQUEST_URI']??'/');$path=(string)(parse_url($uri,PHP_URL_PATH)??'/');
if(in_array($path,['/__redirect.php','/__redirect-map.php'],true)){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');echo 'Not found.';exit;}
$record=$map[$path]??null;if(!is_array($record)){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');header('X-Robots-Tag: noindex, nofollow');echo 'Not found.';exit;}
$target=(string)($record['target']??'');$status=(int)($record['status']??301);if($target===''||$target[0]!=='/'||preg_match('/[\r\n]/',$target)||!in_array($status,[301,302,307,308],true)){http_response_code(500);header('Content-Type: text/plain; charset=utf-8');echo 'Redirect map is invalid.';exit;}
if(!empty($record['preserveQuery'])){$query=(string)(parse_url($uri,PHP_URL_QUERY)??'');if($query!==''){$fragment='';$hash=strpos($target,'#');if($hash!==false){$fragment=substr($target,$hash);$target=substr($target,0,$hash);}$target.=str_contains($target,'?')?'&'.$query:'?'.$query;$target.=$fragment;}}
header('X-Robots-Tag: noindex, follow');header('Cache-Control: '.(in_array($status,[301,308],true)?'public, max-age=3600':'no-store'));header('Location: '.$target,true,$status);exit;
