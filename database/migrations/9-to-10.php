<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap-core.php';

/** Explicit schema 9 -> 10 migration for generic Audience authority. */
function migration910TablesSql(): array {return [<<<'SQL'
CREATE TABLE IF NOT EXISTS audience_lists (
  list_key VARCHAR(64) NOT NULL PRIMARY KEY,
  label VARCHAR(191) NOT NULL,
  public_label VARCHAR(191) NOT NULL,
  description TEXT NOT NULL,
  confirmation_subject VARCHAR(255) NOT NULL,
  confirmation_body TEXT NOT NULL,
  status ENUM('active','disabled') NOT NULL DEFAULT 'disabled',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_audience_lists_status (status,label),
  CONSTRAINT fk_audience_lists_created_user FOREIGN KEY (created_by) REFERENCES cms_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_audience_lists_updated_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,<<<'SQL'
CREATE TABLE IF NOT EXISTS audience_subscriptions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  list_key VARCHAR(64) NOT NULL,
  email VARCHAR(254) NOT NULL,
  status ENUM('pending','confirmed','unsubscribed') NOT NULL DEFAULT 'pending',
  requested_at DATETIME NOT NULL,
  last_confirmation_sent_at DATETIME NULL,
  confirmed_at DATETIME NULL,
  unsubscribed_at DATETIME NULL,
  confirmation_token_hash CHAR(64) NULL,
  source VARCHAR(64) NOT NULL DEFAULT 'public-form',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_audience_subscription (list_key,email),
  KEY idx_audience_subscription_status (list_key,status),
  KEY idx_audience_subscription_token (confirmation_token_hash),
  CONSTRAINT fk_audience_subscription_list FOREIGN KEY (list_key) REFERENCES audience_lists(list_key) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL];}
function migration910State(PDO $pdo): array {
    $tables=bootstrapExistingTables($pdo);
    return ['schemaVersion'=>bootstrapSchemaVersionInDatabase($pdo,$tables),'audienceLists'=>in_array('audience_lists',$tables,true),'audienceSubscriptions'=>in_array('audience_subscriptions',$tables,true),'legacySubscribers'=>in_array('subscribers',$tables,true),'legacyArchive'=>in_array('subscribers_legacy_archive',$tables,true)];
}
function migration910SafeLegacyKey(string $key): bool {return preg_match('/^[a-z0-9][a-z0-9-]{1,63}$/',$key)===1;}
function migration910LegacyLabel(string $key): string {$label=trim(ucwords(str_replace(['-','_'],' ',$key)));return $label!==''?$label:$key;}
function migration910ImportLegacy(PDO $pdo): array {
    $state=migration910State($pdo);if(!$state['legacySubscribers'])return ['lists'=>0,'subscriptions'=>0];if($state['legacyArchive'])throw new RuntimeException('Both subscribers and subscribers_legacy_archive exist; inspect the legacy subscription state before migration.');
    $keys=$pdo->query('SELECT DISTINCT list_key FROM subscribers ORDER BY list_key')->fetchAll(PDO::FETCH_COLUMN);$listInsert=$pdo->prepare("INSERT IGNORE INTO audience_lists (list_key,label,public_label,description,confirmation_subject,confirmation_body,status,created_at,updated_at) VALUES (?,?,?,'Imported from the legacy subscription primitive.','Confirm your subscription','Confirm your subscription by opening {confirmation_url}','disabled',UTC_TIMESTAMP(),UTC_TIMESTAMP())");$lists=0;
    foreach($keys as $raw){$key=(string)$raw;if(!migration910SafeLegacyKey($key))throw new RuntimeException('Legacy subscriber list key is outside the Audience identifier contract: '.$key);$label=migration910LegacyLabel($key);$listInsert->execute([$key,$label,$label]);$lists+=$listInsert->rowCount();}
    $rows=$pdo->query('SELECT list_key,email,status,requested_at,last_confirmation_sent_at,confirmed_at,confirmation_token_hash,created_at,updated_at FROM subscribers ORDER BY id')->fetchAll();$insert=$pdo->prepare("INSERT INTO audience_subscriptions (list_key,email,status,requested_at,last_confirmation_sent_at,confirmed_at,unsubscribed_at,confirmation_token_hash,source,created_at,updated_at) VALUES (?,?,?,?,?,?,NULL,?,'legacy-subscribers',?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),requested_at=VALUES(requested_at),last_confirmation_sent_at=VALUES(last_confirmation_sent_at),confirmed_at=VALUES(confirmed_at),confirmation_token_hash=VALUES(confirmation_token_hash),source='legacy-subscribers',updated_at=VALUES(updated_at)");$subscriptions=0;
    foreach($rows as $row){$insert->execute([(string)$row['list_key'],strtolower(trim((string)$row['email'])),(string)$row['status'],(string)$row['requested_at'],$row['last_confirmation_sent_at'],$row['confirmed_at'],$row['confirmation_token_hash'],(string)$row['created_at'],(string)$row['updated_at']]);$subscriptions+=$insert->rowCount();}
    $pdo->exec('RENAME TABLE subscribers TO subscribers_legacy_archive');return ['lists'=>$lists,'subscriptions'=>$subscriptions];
}
function migration910Apply(PDO $pdo): array {
    $before=migration910State($pdo);$version=(int)$before['schemaVersion'];
    if($version>=10){if(!$before['audienceLists']||!$before['audienceSubscriptions'])throw new RuntimeException('Schema is stamped v10+ but Audience authority tables are missing; inspect before repair.');return ['changed'=>false,'before'=>$before,'after'=>$before,'legacyImported'=>['lists'=>0,'subscriptions'=>0]];}
    if($version!==9)throw new RuntimeException('This migration accepts only schema v9. Apply earlier migrations first.');
    $cfg=dbConfig();$lock='aincms-migrate-9-10-'.substr(hash('sha256',(string)($cfg['name']??'')),0,20);$stmt=$pdo->prepare('SELECT GET_LOCK(?,10)');$stmt->execute([$lock]);if((int)$stmt->fetchColumn()!==1)throw new RuntimeException('Could not acquire the schema migration lock.');
    try{$again=migration910State($pdo);if((int)$again['schemaVersion']>=10)return ['changed'=>false,'before'=>$before,'after'=>$again,'legacyImported'=>['lists'=>0,'subscriptions'=>0]];if((int)$again['schemaVersion']!==9)throw new RuntimeException('Schema changed while waiting for the migration lock.');foreach(migration910TablesSql() as $sql)$pdo->exec($sql);$imported=migration910ImportLegacy($pdo);$pdo->exec("UPDATE app_meta SET schema_version=10,updated_at=UTC_TIMESTAMP() WHERE id=1 AND schema_version=9");$after=migration910State($pdo);if((int)$after['schemaVersion']!==10||!$after['audienceLists']||!$after['audienceSubscriptions']||$after['legacySubscribers'])throw new RuntimeException('Schema 9 to 10 migration did not reach the expected Audience state.');return ['changed'=>true,'before'=>$before,'after'=>$after,'legacyImported'=>$imported];}
    finally{try{$release=$pdo->prepare('SELECT RELEASE_LOCK(?)');$release->execute([$lock]);}catch(Throwable $ignored){}}
}
function migration910Cli(array $argv): int {try{if(!dbConfigured())throw new RuntimeException('Database credentials are not configured.');$state=migration910State(db());if(!in_array('--apply',$argv,true)){fwrite(STDOUT,json_encode(['ok'=>true,'applyRequired'=>true,'migration'=>'9-to-10','state'=>$state],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}$result=migration910Apply(db());fwrite(STDOUT,json_encode(['ok'=>true,'migration'=>'9-to-10']+$result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}catch(Throwable $e){fwrite(STDERR,'Migration failed: '.$e->getMessage().PHP_EOL);return 1;}}
if(realpath((string)($_SERVER['SCRIPT_FILENAME']??''))===__FILE__){if(PHP_SAPI!=='cli'){http_response_code(404);exit;}exit(migration910Cli($argv??[]));}
