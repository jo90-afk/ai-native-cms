<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__).'/api/readiness.php';

try{
    $report=readinessReport(dirname(__DIR__));
    fwrite(STDOUT,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit($report['ready']?0:2);
}catch(Throwable $e){fwrite(STDERR,'Readiness failed: '.$e->getMessage().PHP_EOL);exit(1);}
