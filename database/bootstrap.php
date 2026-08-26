<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/bootstrap-core.php';

$root=dirname(__DIR__);$repair=in_array('--repair',$argv,true);
try{
    $result=bootstrapInstall($root,$repair);$state=$result['state'];
    fwrite(STDOUT,$result['message'].PHP_EOL);
    fwrite(STDOUT,'Status: '.$state['status'].'; schema: '.$state['schemaVersion'].'/'.$state['expectedSchemaVersion'].'; owners: '.$state['ownerCount'].PHP_EOL);
    exit(0);
}catch(Throwable $e){fwrite(STDERR,'Bootstrap failed: '.$e->getMessage().PHP_EOL);exit(1);}
