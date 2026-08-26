<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/redirects.php';
require_once dirname(__DIR__).'/database/migrations/7-to-8.php';

function rt(bool $condition,string $message): void {if(!$condition){fwrite(STDERR,"FAIL: $message\n");exit(1);}}
function rejects(callable $fn): bool {try{$fn();return false;}catch(RuntimeException $e){return true;}}

rt(redirectNormalizeSource('/old-page/')==='/old-page/','valid redirect source changed');
rt(redirectNormalizeTarget('/new-page/?campaign=x#part','/old-page/')==='/new-page/?campaign=x#part','valid same-site target changed');
rt(rejects(fn()=>redirectNormalizeSource('https://example.com/old')),'absolute redirect source accepted');
rt(rejects(fn()=>redirectNormalizeSource('/api/private')),'reserved application source accepted');
rt(rejects(fn()=>redirectNormalizeSource('/a/%2f/b')),'encoded separator accepted');
rt(rejects(fn()=>redirectNormalizeSource('/a/../b')),'dot segment accepted');
rt(rejects(fn()=>redirectNormalizeTarget('https://evil.example/x','/old/')),'external redirect target accepted');
rt(rejects(fn()=>redirectNormalizeTarget('/same/','/same/')),'self redirect accepted');
rt(redirectAllowedStatus(308)===308,'valid redirect status rejected');
rt(rejects(fn()=>redirectAllowedStatus(305)),'invalid redirect status accepted');

$valid=[['source'=>'/a/','target'=>'/b/','active'=>true],['source'=>'/b/','target'=>'/c/','active'=>true]];redirectValidateGraph($valid);
rt(rejects(fn()=>redirectValidateGraph([['source'=>'/a/','target'=>'/b/','active'=>true],['source'=>'/b/','target'=>'/a/','active'=>true]])),'redirect cycle accepted');
$a=redirectRow(['source'=>'/a/','target'=>'/b/','status'=>301,'preserveQuery'=>true,'active'=>true,'managedBy'=>'manual','note'=>'x']);$b=$a;$b['target']='/c/';$b['revisionHash']=redirectRevisionHash($b);rt($a['revisionHash']!==$b['revisionHash'],'redirect revision hash did not change');

$tmp=sys_get_temp_dir().'/aincms-redirect-'.bin2hex(random_bytes(5));
rt(mkdir($tmp.'/occupied',0775,true),'could not create redirect collision fixture');
file_put_contents($tmp.'/occupied/index.html','fixture');
mkdir($tmp.'/empty',0775,true);
file_put_contents($tmp.'/file.html','fixture');
rt(redirectSourceCollidesWithPublicFile($tmp,'/occupied'),'directory route without trailing slash was not treated as occupied');
rt(redirectSourceCollidesWithPublicFile($tmp,'/occupied/'),'directory route with trailing slash was not treated as occupied');
rt(redirectSourceCollidesWithPublicFile($tmp,'/empty/'),'empty directory route was not treated as occupied');
rt(redirectSourceCollidesWithPublicFile($tmp,'/file.html'),'public file route was not treated as occupied');
rt(!redirectSourceCollidesWithPublicFile($tmp,'/missing/'),'missing route was incorrectly treated as occupied');
unlink($tmp.'/occupied/index.html');unlink($tmp.'/file.html');rmdir($tmp.'/occupied');rmdir($tmp.'/empty');rmdir($tmp);

$sql=migration78TableSql();rt(str_contains($sql,'CREATE TABLE IF NOT EXISTS redirect_records'),'migration omits redirect table');rt(str_contains($sql,'managed_by'),'migration omits redirect provenance class');
$router=(string)file_get_contents(dirname(__DIR__).'/__redirect.php');rt(!str_contains($router,'db(')&&!str_contains($router,'PDO'),'anonymous redirect runtime depends on database');rt(str_contains($router,'preserveQuery'),'redirect runtime lost query preservation');

fwrite(STDOUT,"PASS: redirect and schema-v8 primitives\n");
