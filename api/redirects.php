<?php
declare(strict_types=1);
require_once __DIR__.'/runtime.php';
require_once __DIR__.'/presentation-core.php';

/** Canonical redirect authority. SQL owns records; anonymous routing consumes only the generated map. */

function redirectRequireSchema(): void { dbRequireSchemaVersion(8); }
function redirectGraphLockName(): string {
    $cfg=dbConfig();
    return 'aincms-redirect-graph-'.substr(hash('sha256',(string)($cfg['name']??'')),0,24);
}
function redirectAcquireGraphLock(PDO $pdo,int $timeout=10): string {
    $name=redirectGraphLockName();
    $stmt=$pdo->prepare('SELECT GET_LOCK(?,?)');
    $stmt->execute([$name,max(1,min(30,$timeout))]);
    if((int)$stmt->fetchColumn()!==1)throw new RuntimeException('Could not acquire the redirect graph lock.');
    return $name;
}
function redirectReleaseGraphLock(PDO $pdo,string $name): void {
    if($name==='')return;
    try{$stmt=$pdo->prepare('SELECT RELEASE_LOCK(?)');$stmt->execute([$name]);}catch(Throwable $ignored){}
}
function redirectAllowedStatus(int $status): int {
    if(!in_array($status,[301,302,307,308],true))throw new RuntimeException('Redirect status must be 301, 302, 307, or 308.');
    return $status;
}
function redirectReservedPath(string $decoded): bool {
    return preg_match('#^/(?:api|cms|database|tests|tools|release|runtime|uploads|\.git|\.github|\.lattice)(?:/|$)#i',$decoded)===1
        || in_array(strtolower($decoded),['/__redirect.php','/__redirect-map.php'],true);
}
function redirectNormalizeSource(string $raw): string {
    $value=trim($raw);
    if($value===''||strlen($value)>512||$value[0]!=='/'||preg_match('/[\x00-\x1f\x7f]/',$value))throw new RuntimeException('Redirect source must be a site path beginning with /.');
    if(str_contains($value,'\\')||str_contains($value,'//')||preg_match('/%(?:2f|5c)/i',$value))throw new RuntimeException('Redirect source contains an ambiguous path separator.');
    $parts=parse_url($value);
    if(!is_array($parts)||isset($parts['scheme'])||isset($parts['host'])||isset($parts['query'])||isset($parts['fragment'])||isset($parts['user'])||isset($parts['pass']))throw new RuntimeException('Redirect source may contain only a path.');
    $path=(string)($parts['path']??'');$decoded=rawurldecode($path);
    if($decoded===''||$decoded[0]!=='/'||str_contains($decoded,'\\')||str_contains($decoded,'//')||str_contains($decoded,"\0")||str_contains($decoded,'?')||str_contains($decoded,'#')||preg_match('/[\x00-\x1f\x7f]/',$decoded))throw new RuntimeException('Redirect source path is invalid.');
    foreach(explode('/',$decoded) as $segment)if($segment==='.'||$segment==='..')throw new RuntimeException('Redirect source cannot contain dot segments.');
    if(redirectReservedPath($decoded))throw new RuntimeException('Redirect source is inside a reserved application path.');
    return $path;
}
function redirectNormalizeTarget(string $raw,string $source=''): string {
    $value=trim($raw);
    if($value===''||strlen($value)>1024||$value[0]!=='/'||preg_match('/[\x00-\x1f\x7f]/',$value)||str_contains($value,'\\')||preg_match('/%(?:2f|5c)/i',$value))throw new RuntimeException('Redirect target must be a same-site path beginning with /.');
    $parts=parse_url($value);
    if(!is_array($parts)||isset($parts['scheme'])||isset($parts['host'])||isset($parts['user'])||isset($parts['pass']))throw new RuntimeException('Redirect target must remain on this site.');
    $path=(string)($parts['path']??'');$decoded=rawurldecode($path);
    if($path===''||$path[0]!=='/'||str_contains($decoded,'\\')||str_contains($decoded,'//')||str_contains($decoded,"\0")||str_contains($decoded,'?')||str_contains($decoded,'#')||preg_match('/[\x00-\x1f\x7f]/',$decoded))throw new RuntimeException('Redirect target path is invalid.');
    foreach(explode('/',$decoded) as $segment)if($segment==='.'||$segment==='..')throw new RuntimeException('Redirect target cannot contain dot segments.');
    if(redirectReservedPath($decoded))throw new RuntimeException('Redirect target is inside a reserved application path.');
    if($source!==''&&hash_equals(redirectNormalizeSource($source),$path))throw new RuntimeException('Redirect source and target resolve to the same path.');
    return $value;
}
function redirectRevisionPayload(array $row): array {
    return ['source'=>(string)($row['source']??''),'target'=>(string)($row['target']??''),'status'=>(int)($row['status']??301),'preserveQuery'=>(bool)($row['preserveQuery']??true),'active'=>(bool)($row['active']??true),'managedBy'=>(string)($row['managedBy']??'manual'),'note'=>(string)($row['note']??'')];
}
function redirectRevisionHash(array $row): string {
    $json=json_encode(redirectRevisionPayload($row),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(!is_string($json))throw new RuntimeException('Redirect revision could not be encoded.');return hash('sha256',$json);
}
function redirectRow(array $row,bool $readOnly=false): array {
    $out=['id'=>(int)($row['id']??0),'source'=>(string)($row['source_path']??$row['source']??''),'target'=>(string)($row['target_path']??$row['target']??''),'status'=>(int)($row['status_code']??$row['status']??301),'preserveQuery'=>(bool)($row['preserve_query']??$row['preserveQuery']??true),'active'=>(bool)($row['is_active']??$row['active']??true),'managedBy'=>(string)($row['managed_by']??$row['managedBy']??'manual'),'note'=>(string)($row['note']??''),'readOnly'=>$readOnly||(bool)($row['readOnly']??false),'createdAt'=>isset($row['created_at'])?dbIso((string)$row['created_at']):null,'updatedAt'=>isset($row['updated_at'])?dbIso((string)$row['updated_at']):null];
    $out['revisionHash']=redirectRevisionHash($out);return $out;
}
function redirectDatabaseRecords(bool $activeOnly=false): array {
    redirectRequireSchema();$sql='SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records'.($activeOnly?' WHERE is_active=1':'').' ORDER BY source_path';return array_map(fn($row)=>redirectRow($row,false),db()->query($sql)->fetchAll());
}
function redirectSystemRecords(): array {
    $configured=siteConfigValue('redirects','system_aliases',[]);if(!is_array($configured))return [];$out=[];
    foreach($configured as $row){
        if(!is_array($row))continue;
        $source=redirectNormalizeSource((string)($row['source']??''));$target=redirectNormalizeTarget((string)($row['target']??''),$source);
        $candidate=redirectRow(['source'=>$source,'target'=>$target,'status'=>redirectAllowedStatus((int)($row['status']??301)),'preserveQuery'=>!array_key_exists('preserveQuery',$row)||(bool)$row['preserveQuery'],'active'=>!array_key_exists('active',$row)||(bool)$row['active'],'managedBy'=>(string)($row['managedBy']??'system'),'note'=>(string)($row['note']??'Configured read-only compatibility alias.'),'readOnly'=>true],true);
        if(isset($out[$source])&&redirectRevisionPayload($out[$source])!==redirectRevisionPayload($candidate))throw new RuntimeException('Conflicting configured system redirect authorities for '.$source);
        $out[$source]=$candidate;
    }
    ksort($out);return array_values($out);
}
function redirectAllRecords(): array {
    $out=[];foreach(redirectDatabaseRecords(false) as $row)$out[$row['source']]=$row;foreach(redirectSystemRecords() as $row){if(isset($out[$row['source']])&&!hash_equals((string)$out[$row['source']]['target'],(string)$row['target']))throw new RuntimeException('Conflicting redirect authorities for '.$row['source']);$out[$row['source']]=$row;}ksort($out);return array_values($out);
}
function redirectValidateGraph(array $records): void {
    $map=[];foreach($records as $row){if(empty($row['active']))continue;$source=redirectNormalizeSource((string)$row['source']);$target=redirectNormalizeTarget((string)$row['target'],$source);$path=(string)(parse_url($target,PHP_URL_PATH)??'');if(isset($map[$source])&&!hash_equals($map[$source],$path))throw new RuntimeException('Conflicting redirect authorities for '.$source);$map[$source]=$path;}
    foreach(array_keys($map) as $start){$seen=[];$current=$start;while(isset($map[$current])){if(isset($seen[$current]))throw new RuntimeException('Redirect cycle detected at '.$current);$seen[$current]=true;$current=$map[$current];}}
}
function redirectSourceToRelativeFile(string $source): string {
    $path=(string)(parse_url(redirectNormalizeSource($source),PHP_URL_PATH)??'/');if($path==='/')return 'index.html';$rel=ltrim($path,'/');return str_ends_with($path,'/')?$rel.'index.html':$rel;
}
function redirectSourceCollidesWithPublicFile(string $root,string $source): bool {
    $path=(string)(parse_url(redirectNormalizeSource($source),PHP_URL_PATH)??'/');
    $root=rtrim($root,'/\\');if($path==='/')return true;
    $decoded=rawurldecode($path);$requestTarget=$root.'/'.rtrim(ltrim($decoded,'/'),'/');
    if(is_file($requestTarget)||is_dir($requestTarget))return true;
    $rel=redirectSourceToRelativeFile($source);return is_file($root.'/'.$rel);
}
function redirectHypothetical(array $candidate): array {
    $rows=redirectDatabaseRecords(false);$replaced=false;foreach($rows as &$row)if((int)$row['id']===(int)($candidate['id']??0)&&$row['id']>0){$row=array_merge($row,$candidate);$replaced=true;break;}unset($row);if(!$replaced)$rows[]=$candidate;return array_merge($rows,redirectSystemRecords());
}
function redirectFindBySource(string $source,bool $forUpdate=false): ?array {
    redirectRequireSchema();$sql='SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records WHERE source_path=? LIMIT 1'.($forUpdate?' FOR UPDATE':'');$stmt=db()->prepare($sql);$stmt->execute([redirectNormalizeSource($source)]);$row=$stmt->fetch();return is_array($row)?redirectRow($row,false):null;
}
function redirectSaveRecord(string $root,array $input,string $expectedHash=''): array {
    redirectRequireSchema();$id=max(0,(int)($input['id']??0));$source=redirectNormalizeSource((string)($input['source']??''));$target=redirectNormalizeTarget((string)($input['target']??''),$source);$status=redirectAllowedStatus((int)($input['status']??301));$preserve=!array_key_exists('preserveQuery',$input)||(bool)$input['preserveQuery'];$active=!array_key_exists('active',$input)||(bool)$input['active'];$note=substr(trim((string)($input['note']??'')),0,1000);
    foreach(redirectSystemRecords() as $system)if($system['source']===$source)throw new RuntimeException('This redirect source is managed by a read-only system alias.');
    if($active&&redirectSourceCollidesWithPublicFile($root,$source))throw new RuntimeException('Redirect source currently resolves to a public file or directory. Remove or move that route before activating this redirect.');
    $candidate=['id'=>$id,'source'=>$source,'target'=>$target,'status'=>$status,'preserveQuery'=>$preserve,'active'=>$active,'managedBy'=>'manual','note'=>$note];$pdo=db();$lock=redirectAcquireGraphLock($pdo);
    try{
        redirectValidateGraph(redirectHypothetical($candidate));$pdo->beginTransaction();
        try{
            $current=null;
            if($id>0){
                $stmt=$pdo->prepare('SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records WHERE id=? FOR UPDATE');$stmt->execute([$id]);$raw=$stmt->fetch();if(!is_array($raw))throw new RuntimeException('Redirect record no longer exists.');$current=redirectRow($raw,false);if($expectedHash===''||!hash_equals($expectedHash,$current['revisionHash']))throw new RuntimeException('Redirect changed since it was opened. Refresh before saving.');if($current['managedBy']!=='manual')throw new RuntimeException('Only manually governed redirects are editable here.');$stmt=$pdo->prepare("UPDATE redirect_records SET source_path=?,target_path=?,status_code=?,preserve_query=?,is_active=?,note=?,updated_by=NULLIF(?,0),updated_at=UTC_TIMESTAMP() WHERE id=?");$stmt->execute([$source,$target,$status,$preserve?1:0,$active?1:0,$note,(int)($_SESSION['cms_user_id']??0),$id]);
            }else{
                $stmt=$pdo->prepare("INSERT INTO redirect_records (source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,'manual',?,NULLIF(?,0),NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP())");$uid=(int)($_SESSION['cms_user_id']??0);$stmt->execute([$source,$target,$status,$preserve?1:0,$active?1:0,$note,$uid,$uid]);$id=(int)$pdo->lastInsertId();
            }
            $pdo->commit();
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }finally{redirectReleaseGraphLock($pdo,$lock);}
    $stmt=db()->prepare('SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records WHERE id=?');$stmt->execute([$id]);$row=$stmt->fetch();if(!is_array($row))throw new RuntimeException('Saved redirect could not be reloaded.');return redirectRow($row,false);
}
function redirectDeleteRecord(int $id,string $expectedHash): void {
    redirectRequireSchema();if($id<1)throw new RuntimeException('Redirect record is invalid.');$pdo=db();$pdo->beginTransaction();try{$stmt=$pdo->prepare('SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records WHERE id=? FOR UPDATE');$stmt->execute([$id]);$raw=$stmt->fetch();if(!is_array($raw))throw new RuntimeException('Redirect record no longer exists.');$current=redirectRow($raw,false);if($expectedHash===''||!hash_equals($expectedHash,$current['revisionHash']))throw new RuntimeException('Redirect changed since it was opened. Refresh before deleting.');if($current['managedBy']!=='manual')throw new RuntimeException('Only manually governed redirects can be deleted.');$del=$pdo->prepare('DELETE FROM redirect_records WHERE id=?');$del->execute([$id]);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
function redirectPostPreflight(string $root,string $source,string $target): void {
    redirectRequireSchema();$source=redirectNormalizeSource($source);$target=redirectNormalizeTarget($target,$source);foreach(redirectSystemRecords() as $system)if($system['source']===$source)throw new RuntimeException('A system-managed redirect already owns the former post URL.');$existing=redirectFindBySource($source);if($existing&&$existing['managedBy']==='manual'&&!hash_equals($existing['target'],$target))throw new RuntimeException('A manually governed redirect already owns the former post URL.');$candidate=$existing??['id'=>0,'source'=>$source,'target'=>$target,'status'=>301,'preserveQuery'=>true,'active'=>true,'managedBy'=>'post','note'=>'Published post slug history.'];$candidate['target']=$target;$candidate['active']=true;redirectValidateGraph(redirectHypothetical($candidate));
}
function redirectUpsertPostSlug(string $source,string $target): array {
    redirectRequireSchema();$source=redirectNormalizeSource($source);$target=redirectNormalizeTarget($target,$source);$pdo=db();$lock=redirectAcquireGraphLock($pdo);
    try{
        foreach(redirectSystemRecords() as $system)if($system['source']===$source)throw new RuntimeException('A system-managed redirect already owns the former post URL.');
        $existing=redirectFindBySource($source);if($existing&&$existing['managedBy']==='manual'&&!hash_equals($existing['target'],$target))throw new RuntimeException('A manually governed redirect already owns the former post URL.');
        $candidate=$existing??['id'=>0,'source'=>$source,'target'=>$target,'status'=>301,'preserveQuery'=>true,'active'=>true,'managedBy'=>'post','note'=>'Published post slug history.'];$candidate['target']=$target;$candidate['active']=true;redirectValidateGraph(redirectHypothetical($candidate));
        if($existing&&$existing['managedBy']==='manual')return $existing;
        $pdo->beginTransaction();
        try{
            $existing=redirectFindBySource($source,true);$uid=(int)($_SESSION['cms_user_id']??0);
            if($existing&&$existing['managedBy']==='manual'){if(!hash_equals($existing['target'],$target))throw new RuntimeException('A manually governed redirect already owns the former post URL.');$pdo->commit();return $existing;}
            if($existing){$stmt=$pdo->prepare("UPDATE redirect_records SET target_path=?,status_code=301,preserve_query=1,is_active=1,managed_by='post',note='Published post slug history.',updated_by=NULLIF(?,0),updated_at=UTC_TIMESTAMP() WHERE id=?");$stmt->execute([$target,$uid,$existing['id']]);$id=(int)$existing['id'];}
            else{$stmt=$pdo->prepare("INSERT INTO redirect_records (source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_by,updated_by,created_at,updated_at) VALUES (?,?,301,1,1,'post','Published post slug history.',NULLIF(?,0),NULLIF(?,0),UTC_TIMESTAMP(),UTC_TIMESTAMP())");$stmt->execute([$source,$target,$uid,$uid]);$id=(int)$pdo->lastInsertId();}
            $collapse=$pdo->prepare("UPDATE redirect_records SET target_path=?,updated_at=UTC_TIMESTAMP() WHERE managed_by='post' AND target_path=? AND source_path<>?");$collapse->execute([$target,$source,$source]);$pdo->commit();
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }finally{redirectReleaseGraphLock($pdo,$lock);}
    $stmt=db()->prepare('SELECT id,source_path,target_path,status_code,preserve_query,is_active,managed_by,note,created_at,updated_at FROM redirect_records WHERE id=?');$stmt->execute([$id]);$row=$stmt->fetch();if(!is_array($row))throw new RuntimeException('Post redirect could not be reloaded.');return redirectRow($row,false);
}
function redirectProject(string $root): array {
    redirectRequireSchema();$records=redirectAllRecords();redirectValidateGraph($records);$map=[];foreach($records as $row){if(empty($row['active']))continue;$source=redirectNormalizeSource((string)$row['source']);$target=redirectNormalizeTarget((string)$row['target'],$source);$map[$source]=['target'=>$target,'status'=>redirectAllowedStatus((int)$row['status']),'preserveQuery'=>(bool)$row['preserveQuery']];}ksort($map);$php="<?php\ndeclare(strict_types=1);\n// Generated from canonical redirect records and configured read-only aliases.\nreturn ".var_export($map,true).";\n";cmsAtomicWrite(rtrim($root,'/\\').'/__redirect-map.php',$php);return ['records'=>count($map),'sha256'=>hash('sha256',$php)];
}