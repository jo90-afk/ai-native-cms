<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap-core.php';

/** Explicit schema 8 -> 9 migration. Never invoked by bootstrap --repair or a browser request. */
function migration89TableSql(): string {return <<<'SQL'
CREATE TABLE IF NOT EXISTS block_presets (
  preset_key VARCHAR(191) NOT NULL PRIMARY KEY,
  label VARCHAR(191) NOT NULL,
  category VARCHAR(96) NOT NULL DEFAULT 'Section',
  preset_kind VARCHAR(32) NOT NULL DEFAULT 'legacy',
  html_content LONGTEXT NOT NULL,
  definition_json LONGTEXT NOT NULL,
  variables_json LONGTEXT NOT NULL,
  source_note VARCHAR(512) NOT NULL DEFAULT '',
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_block_presets_category (category, label),
  KEY idx_block_presets_kind (preset_kind),
  KEY idx_block_presets_created_user (created_by),
  KEY idx_block_presets_updated_user (updated_by),
  CONSTRAINT fk_block_presets_created_user FOREIGN KEY (created_by) REFERENCES cms_users(id) ON DELETE SET NULL,
  CONSTRAINT fk_block_presets_updated_user FOREIGN KEY (updated_by) REFERENCES cms_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;}
function migration89State(PDO $pdo): array {$tables=bootstrapExistingTables($pdo);return ['schemaVersion'=>bootstrapSchemaVersionInDatabase($pdo,$tables),'blockPresets'=>in_array('block_presets',$tables,true),'legacyTemplates'=>in_array('page_block_templates',$tables,true),'legacyArchive'=>in_array('block_presets_legacy_archive',$tables,true)];}
function migration89CopyTemplates(PDO $pdo,string $table): int {
    $sql="INSERT INTO block_presets (preset_key,label,category,preset_kind,html_content,definition_json,variables_json,source_note,created_at,updated_at) SELECT template_key,label,category,'legacy',html_content,'{}',variables_json,CONCAT('Converted from repository page structure: ',source_page,' #',source_ordinal+1),created_at,updated_at FROM {$table} ON DUPLICATE KEY UPDATE label=VALUES(label),category=VALUES(category),html_content=VALUES(html_content),variables_json=VALUES(variables_json),source_note=VALUES(source_note),updated_at=VALUES(updated_at)";
    return (int)$pdo->exec($sql);
}
function migration89RewriteCompositions(PDO $pdo): int {
    $rows=$pdo->query('SELECT page_path,blocks_json FROM page_compositions')->fetchAll();$update=$pdo->prepare('UPDATE page_compositions SET blocks_json=?,updated_at=updated_at WHERE page_path=?');$changed=0;
    foreach($rows as $row){$blocks=json_decode((string)$row['blocks_json'],true);if(!is_array($blocks))throw new RuntimeException('A page composition contains invalid JSON; repair it before migration.');$dirty=false;foreach($blocks as &$block){if(!is_array($block))continue;if(isset($block['templateKey'])&&!isset($block['presetKey'])){$block['presetKey']=(string)$block['templateKey'];unset($block['templateKey']);$dirty=true;}}unset($block);if(!$dirty)continue;$json=json_encode($blocks,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);if(!is_string($json))throw new RuntimeException('Could not encode a migrated page composition.');$update->execute([$json,(string)$row['page_path']]);$changed++;}return $changed;
}
function migration89Apply(PDO $pdo): array {
    $before=migration89State($pdo);$version=(int)$before['schemaVersion'];
    if($version>=9){if(!$before['blockPresets'])throw new RuntimeException('Schema is stamped v9+ but block_presets is missing; inspect the database before repair.');return ['changed'=>false,'before'=>$before,'after'=>$before,'compositionsRewritten'=>0];}
    if($version!==8)throw new RuntimeException('This migration accepts only schema v8. Use the migration path matching the installed schema.');
    $lock='aincms-migrate-8-9-'.substr(hash('sha256',(string)dbConfig()['name']),0,20);$stmt=$pdo->prepare('SELECT GET_LOCK(?,10)');$stmt->execute([$lock]);if((int)$stmt->fetchColumn()!==1)throw new RuntimeException('Could not acquire the schema migration lock.');
    try{$again=migration89State($pdo);if((int)$again['schemaVersion']>=9)return ['changed'=>false,'before'=>$before,'after'=>$again,'compositionsRewritten'=>0];if((int)$again['schemaVersion']!==8)throw new RuntimeException('Schema changed while waiting for the migration lock.');$pdo->exec(migration89TableSql());$source=$again['legacyTemplates']?'page_block_templates':($again['legacyArchive']?'block_presets_legacy_archive':'');if($source!=='')migration89CopyTemplates($pdo,$source);$rewritten=migration89RewriteCompositions($pdo);
        $mid=migration89State($pdo);if($mid['legacyTemplates']&&$mid['legacyArchive'])throw new RuntimeException('Both the active legacy table and recovery archive exist; inspect before continuing.');if($mid['legacyTemplates'])$pdo->exec('RENAME TABLE page_block_templates TO block_presets_legacy_archive');$pdo->exec("UPDATE app_meta SET schema_version=9,updated_at=UTC_TIMESTAMP() WHERE id=1 AND schema_version=8");$after=migration89State($pdo);if((int)$after['schemaVersion']!==9||!$after['blockPresets']||$after['legacyTemplates'])throw new RuntimeException('Schema 8 to 9 migration did not reach the expected state.');return ['changed'=>true,'before'=>$before,'after'=>$after,'compositionsRewritten'=>$rewritten];}
    finally{try{$release=$pdo->prepare('SELECT RELEASE_LOCK(?)');$release->execute([$lock]);}catch(Throwable $ignored){}}
}
function migration89Cli(array $argv): int {try{if(!dbConfigured())throw new RuntimeException('Database credentials are not configured.');$state=migration89State(db());if(!in_array('--apply',$argv,true)){fwrite(STDOUT,json_encode(['ok'=>true,'applyRequired'=>true,'migration'=>'8-to-9','state'=>$state],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}$result=migration89Apply(db());fwrite(STDOUT,json_encode(['ok'=>true,'migration'=>'8-to-9']+$result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);return 0;}catch(Throwable $e){fwrite(STDERR,'Migration failed: '.$e->getMessage().PHP_EOL);return 1;}}
if(realpath((string)($_SERVER['SCRIPT_FILENAME']??''))===__FILE__){if(PHP_SAPI!=='cli'){http_response_code(404);exit;}exit(migration89Cli($argv??[]));}
