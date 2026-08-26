<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__).'/api/seo-quality.php';

$root=dirname(__DIR__);$strict=in_array('--strict',$argv??[],true);
try{
    dbRequireSchemaVersion(8);$audit=seoQualitySite($root);$summary=(array)($audit['summary']??[]);
    fwrite(STDOUT,json_encode($audit,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n");
    $errors=(int)($summary['errors']??0);$warnings=(int)($summary['warnings']??0);exit($errors>0||($strict&&$warnings>0)?1:0);
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(2);}
