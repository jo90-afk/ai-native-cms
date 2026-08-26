<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/database.php';

/** Portable first-run schema + owner bootstrap. Never seeds adopter content. */

function bootstrapSchemaSql(string $root): string {
    $path=rtrim($root,'/\\').'/database/schema.sql';
    if(!is_file($path)||!is_readable($path))throw new RuntimeException('Database schema source is unavailable.');
    $sql=file_get_contents($path);if($sql===false||trim($sql)==='')throw new RuntimeException('Database schema source could not be read.');return $sql;
}
function bootstrapSchemaVersion(string $root): int {
    $sql=bootstrapSchemaSql($root);
    if(!preg_match('/INSERT\s+INTO\s+app_meta\s*\([^)]*schema_version[^)]*\)\s*VALUES\s*\(\s*1\s*,\s*(\d+)\s*\)/i',$sql,$m))throw new RuntimeException('Schema version could not be determined from database/schema.sql.');
    return max(1,(int)$m[1]);
}
function bootstrapRequiredTables(string $root): array {
    $sql=bootstrapSchemaSql($root);$tables=[];
    if(preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?([A-Za-z0-9_]+)`?/i',$sql,$m))foreach($m[1] as $name)$tables[]=(string)$name;
    $tables=array_values(array_unique($tables));sort($tables,SORT_STRING);if(!$tables)throw new RuntimeException('No required tables were found in database/schema.sql.');return $tables;
}
function bootstrapSqlStatements(string $sql): array {
    $out=[];$buf='';$quote=null;$lineComment=false;$blockComment=false;$len=strlen($sql);
    for($i=0;$i<$len;$i++){
        $ch=$sql[$i];$next=$i+1<$len?$sql[$i+1]:'';
        if($lineComment){if($ch==="\n"){$lineComment=false;$buf.="\n";}continue;}
        if($blockComment){if($ch==='*'&&$next==='/'){$blockComment=false;$i++;}continue;}
        if($quote!==null){$buf.=$ch;if($ch===$quote){if(($quote==="'"||$quote==='"')&&$next===$quote){$buf.=$next;$i++;continue;}$slashes=0;for($j=$i-1;$j>=0&&$sql[$j]==='\\';$j--)$slashes++;if($slashes%2===0)$quote=null;}continue;}
        if($ch==="'"||$ch==='"'||$ch==='`'){$quote=$ch;$buf.=$ch;continue;}
        if($ch==='#'){$lineComment=true;continue;}
        if($ch==='-'&&$next==='-'&&($i+2>=$len||ctype_space($sql[$i+2]))){$lineComment=true;$i++;continue;}
        if($ch==='/'&&$next==='*'){$blockComment=true;$i++;continue;}
        if($ch===';'){$stmt=trim($buf);if($stmt!=='')$out[]=$stmt;$buf='';continue;}$buf.=$ch;
    }
    $stmt=trim($buf);if($stmt!=='')$out[]=$stmt;return $out;
}
function bootstrapClassifyState(array $existing,array $required,int $version,int $expected,int $owners): array {
    $existing=array_values(array_unique(array_map('strval',$existing)));$required=array_values(array_unique(array_map('strval',$required)));sort($existing,SORT_STRING);sort($required,SORT_STRING);$known=array_values(array_intersect($required,$existing));$missing=array_values(array_diff($required,$existing));$unknown=array_values(array_diff($existing,$required));
    $status='partial';if(!$existing)$status='empty';elseif(!$missing&&$version>=$expected&&$owners>0)$status='ready';elseif(!$known&&$unknown)$status='foreign';
    return ['status'=>$status,'schemaVersion'=>$version,'expectedSchemaVersion'=>$expected,'ownerCount'=>$owners,'requiredCount'=>count($required),'existingRequiredCount'=>count($known),'missing'=>$missing,'unknown'=>$unknown];
}
function bootstrapExistingTables(PDO $pdo): array {
    $rows=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$out=array_values(array_map('strval',$rows?:[]));sort($out,SORT_STRING);return $out;
}
function bootstrapOwnerCount(PDO $pdo,array $existing): int {
    if(!in_array('cms_users',$existing,true))return 0;try{return (int)$pdo->query('SELECT COUNT(*) FROM cms_users')->fetchColumn();}catch(Throwable $e){return 0;}
}
function bootstrapSchemaVersionInDatabase(PDO $pdo,array $existing): int {
    if(!in_array('app_meta',$existing,true))return 0;try{return (int)$pdo->query('SELECT schema_version FROM app_meta WHERE id=1')->fetchColumn();}catch(Throwable $e){return 0;}
}
function bootstrapState(PDO $pdo,string $root): array {
    $existing=bootstrapExistingTables($pdo);return bootstrapClassifyState($existing,bootstrapRequiredTables($root),bootstrapSchemaVersionInDatabase($pdo,$existing),bootstrapSchemaVersion($root),bootstrapOwnerCount($pdo,$existing));
}
function bootstrapRunSchema(PDO $pdo,string $root): int {$count=0;foreach(bootstrapSqlStatements(bootstrapSchemaSql($root)) as $stmt){$pdo->exec($stmt);$count++;}return $count;}
function bootstrapConfiguredOwner(): array {
    if(siteSecret('AINCMS_CMS_ENABLED','0')!=='1')throw new RuntimeException('CMS bootstrap is not enabled.');$username=trim(siteSecret('AINCMS_CMS_USER'));$hash=trim(siteSecret('AINCMS_CMS_PASSWORD_HASH'));
    if(!preg_match('/^[A-Za-z0-9._-]{3,64}$/',$username))throw new RuntimeException('CMS bootstrap username is invalid.');if($hash===''||password_get_info($hash)['algoName']==='unknown')throw new RuntimeException('CMS bootstrap password hash is missing or invalid.');
    return ['username'=>$username,'passwordHash'=>$hash,'displayName'=>substr(trim((string)siteConfigValue('site','owner_display_name','Site Owner')),0,191)?:'Site Owner'];
}
function bootstrapInstallOwner(PDO $pdo): bool {
    $existing=bootstrapExistingTables($pdo);if(!in_array('cms_users',$existing,true))throw new RuntimeException('CMS user table is unavailable after schema installation.');if(bootstrapOwnerCount($pdo,$existing)>0)return false;$owner=bootstrapConfiguredOwner();
    $stmt=$pdo->prepare("INSERT INTO cms_users (username,password_hash,display_name,email,role,session_version,created_at,updated_at) VALUES (?,?,?,'','Owner',1,UTC_TIMESTAMP(),UTC_TIMESTAMP())");$stmt->execute([$owner['username'],$owner['passwordHash'],$owner['displayName']]);$id=(int)$pdo->lastInsertId();
    if($id>0){$audit=$pdo->prepare("INSERT INTO cms_activity (user_id,event_type,message,context_json,created_at) VALUES (?,'bootstrap','Initialized CMS database owner','{}',UTC_TIMESTAMP())");$audit->execute([$id]);}return true;
}
function bootstrapInstall(string $root,bool $allowRepair=false): array {
    if(!dbConfigured())throw new RuntimeException('Database credentials are not configured.');if(!extension_loaded('pdo_mysql'))throw new RuntimeException('PHP PDO MySQL support (pdo_mysql) is required.');$pdo=db();$state=bootstrapState($pdo,$root);
    if($state['status']==='ready')return ['changed'=>false,'schemaStatements'=>0,'ownerCreated'=>false,'state'=>$state,'message'=>'Database schema and owner are already initialized.'];
    if($state['status']==='foreign')throw new RuntimeException('The configured database is non-empty and does not look like AI Native CMS. Use a dedicated database.');
    if($state['status']==='partial'&&!$allowRepair)throw new RuntimeException('A partial AI Native CMS installation exists. Inspect it, then rerun with --repair only if completing it is intended.');
    $lockName='aincms-bootstrap-'.substr(hash('sha256',(string)dbConfig()['name']),0,24);$lock=$pdo->prepare('SELECT GET_LOCK(?,5)');$lock->execute([$lockName]);if((int)$lock->fetchColumn()!==1)throw new RuntimeException('Could not acquire the database bootstrap lock.');
    try{
        $state=bootstrapState($pdo,$root);if($state['status']==='ready')return ['changed'=>false,'schemaStatements'=>0,'ownerCreated'=>false,'state'=>$state,'message'=>'Database schema and owner are already initialized.'];if($state['status']==='foreign')throw new RuntimeException('The configured database is non-empty and does not look like AI Native CMS. Use a dedicated database.');if($state['status']==='partial'&&!$allowRepair)throw new RuntimeException('A partial AI Native CMS installation exists. Repair was not authorized.');
        $statements=bootstrapRunSchema($pdo,$root);$ownerCreated=bootstrapInstallOwner($pdo);$final=bootstrapState($pdo,$root);if($final['status']!=='ready')throw new RuntimeException('Database bootstrap did not reach a ready schema-and-owner state.');
        return ['changed'=>true,'schemaStatements'=>$statements,'ownerCreated'=>$ownerCreated,'state'=>$final,'message'=>'Database schema and owner initialized. Run database/reconcile.php to initialize canonical repository content.'];
    }finally{try{$release=$pdo->prepare('SELECT RELEASE_LOCK(?)');$release->execute([$lockName]);}catch(Throwable $ignored){}}
}
