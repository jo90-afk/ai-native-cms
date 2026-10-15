<?php
declare(strict_types=1);

$root=sys_get_temp_dir().'/aincms-publication-root-'.bin2hex(random_bytes(4));
$state=sys_get_temp_dir().'/aincms-publication-state-'.bin2hex(random_bytes(4));
mkdir($root,0775,true);
putenv('AINCMS_PUBLICATION_STATE_ROOT='.$state);
require_once dirname(__DIR__).'/api/publication-lock.php';

function poAssert(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
function poRemove(string $path): void {if(!file_exists($path)&&!is_link($path))return;if(is_dir($path)&&!is_link($path)){foreach(scandir($path)?:[] as $name){if($name==='.'||$name==='..')continue;poRemove($path.'/'.$name);}@rmdir($path);return;}@unlink($path);}

try{
    $resolved=publicationEnsureStateRoot($root);
    poAssert($resolved===realpath($state),'configured private state root was not used');
    poAssert(!pathInside($resolved,$root),'publication state must stay outside public root');

    $lock=PublicationLock::acquire($root);
    $same=PublicationLock::acquire($root);
    poAssert($same===$lock,'nested publication lock did not reuse the held lock');
    PublicationLock::release($same);
    PublicationLock::release($lock);

    PublicationOutcome::saved($root,['kind'=>'test','path'=>'index.html','ignored'=>['private']]);
    poAssert(PublicationOutcome::hasSavedMutation(),'saved canonical mutation was not tracked');
    $recorded=PublicationOutcome::recordFailure(new RuntimeException('projection failed'),'abc123');
    poAssert($recorded,'saved publication failure did not install recovery state');
    poAssert(!PublicationOutcome::hasSavedMutation(),'request-local saved state was not consumed after failure');
    poAssert(publicationPendingKind($resolved)==='cms-publication','pending recovery kind drifted');
    $record=json_decode((string)file_get_contents($resolved.'/public-update-pending/record.json'),true,512,JSON_THROW_ON_ERROR);
    poAssert(($record['context']['kind']??'')==='test','bounded public context was not retained');
    poAssert(!array_key_exists('ignored',$record['context']??[]),'non-scalar context leaked into recovery record');
    poAssert(!isset($record['error']),'exception message should not be persisted in generic recovery state');

    $blocked=false;
    try{PublicationLock::acquire($root);}catch(PublicationConflict $e){$blocked=true;poAssert(str_contains($e->publicMessage(),'awaiting publication recovery'),'pending CMS publication did not expose the safe saved-pending message');}
    poAssert($blocked,'pending publication did not block a follow-on writer');

    poRemove($resolved.'/public-update-pending');
    PublicationOutcome::saved($root,['kind'=>'success']);
    poAssert(PublicationOutcome::hasSavedMutation(),'second saved state missing');
    PublicationOutcome::published();
    poAssert(!PublicationOutcome::hasSavedMutation(),'successful publication did not clear saved state');

    echo "PASS: publication state distinguishes saved canonical mutations, failed projection recovery, writer blocking, and successful completion.\n";
} finally {
    poRemove($state);poRemove($root);putenv('AINCMS_PUBLICATION_STATE_ROOT');
}
