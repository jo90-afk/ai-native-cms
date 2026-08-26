<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap-core.php';

/** Explicit schema 7 -> 8 migration. This is intentionally not reachable from bootstrap --repair. */
function migration78TableSql(): string {
    return <<<'SQL'
CREATE TABLE IF NOT EXISTS redirect_records (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  source_path VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  target_path VARCHAR(1024) NOT NULL,
  status_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  preserve_query TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  managed_by VARCHAR(32) NOT NULL DEFAULT 'manual',
  note VARCHAR(1000) NOT NULL DEFAULT '',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_redirect_records_source (source_path),
  KEY idx_redirect_records_active (is_active, source_path(191)),
  KEY idx_redirect_records_updated (updated_at),
  CONSTRAINT fk_redirect_records_created_user FOREIGN KEY (created_by) REFERENCES cms_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_redirect_records_updated_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
}
function migration78State(PDO $pdo): array {
    $tables=bootstrapExistingTables($pdo);$version=bootstrapSchemaVersionInDatabase($pdo,$tables);return ['schemaVersion'=>$version,'redirectTable'=>in_array('redirect_records',$tables,true)];
}
function migration78Apply(PDO $pdo): array {
    $before=migration78State($pdo);$version=(int)$before['schemaVersion'];
    if($version>=8){if(!$before['redirectTable'])throw new RuntimeException('Schema is stamped v8+ but redirect_records is missing; inspect the database before repair.');return ['changed'=>false,'before'=>$before,'after'=>$before];}
    if($version!==7)throw new RuntimeException('This migration accepts only schema v7. Use the migration path matching the installed schema.');
    $lockName='aincms-migrate-7-8-'.substr(hash('sha256',(string)dbConfig()['name']),0,20);$stmt=$pdo->prepare('SELECT GET_LOCK(?,10)');$stmt->execute([$lockName]);if((int)$stmt->fetchColumn()!==1)throw new RuntimeException('Could not acquire the schema migration lock.');
    try{$again=migration78State($pdo);if((int)$again['schemaVersion']>=8)return ['changed'=>false,'before'=>$before,'after'=>$again];if((int)$again['schemaVersion']!==7)throw new RuntimeException('Schema changed while waiting for the migration lock.');$pdo->exec(migration78TableSql());$pdo->exec("UPDATE app_meta SET schema_version=8,updated_at=UTC_TIMESTAMP() WHERE id=1 AND schema_version=7");$after=migration78State($pdo);if((int)$after['schemaVersion']!==8||!$after['redirectTable'])throw new RuntimeException('Schema 7 to 8 migration did not reach the expected state.');return ['changed'=>true,'before'=>$before,'after'=>$after];}
    finally{try{$release=$pdo->prepare('SELECT RELEASE_LOCK(?)');$release->execute([$lockName]);}catch(Throwable $ignored){}}
}
function migration78Cli(array $argv): int {
    try{if(!dbConfigured())throw new RuntimeException('Database credentials are not configured.');$pdo=db();$state=migration78State($pdo);$apply=in_array('--apply',$argv,true);if(!$apply){fwrite(STDOUT,json_encode(['ok'=>true,'applyRequired'=>true,'migration'=>'7-to-8','state'=>$state],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}$result=migration78Apply($pdo);fwrite(STDOUT,json_encode(['ok'=>true,'migration'=>'7-to-8']+$result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}catch(Throwable $e){fwrite(STDERR,'Migration failed: '.$e->getMessage().PHP_EOL);return 1;}
}

if(realpath((string)($_SERVER['SCRIPT_FILENAME']??''))===__FILE__){if(PHP_SAPI!=='cli'){http_response_code(404);exit;}exit(migration78Cli($argv??[]));}
