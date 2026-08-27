#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORK="$(mktemp -d)"; trap 'rm -rf "$WORK"' EXIT
ZIP="$(find "$ROOT/dist/rehearsal-a" -maxdepth 1 -name '*.zip' -print -quit)"; [[ -n "$ZIP" ]]
python3 - "$ZIP" "$WORK" <<'PY'
import sys,zipfile
with zipfile.ZipFile(sys.argv[1]) as z:z.extractall(sys.argv[2])
PY
SITE="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -print -quit)"; cd "$SITE"
php setup/site.php --name="RC3 Upgrade Rehearsal" --url=http://localhost:8080 --owner="Rehearsal Owner" >/tmp/aincms-v9-site-setup.txt
export AINCMS_DB_HOST="${AINCMS_DB_HOST:-127.0.0.1}" AINCMS_DB_PORT="${AINCMS_DB_PORT:-3306}" AINCMS_DB_NAME="${AINCMS_DB_NAME:-aincms}" AINCMS_DB_USER="${AINCMS_DB_USER:-aincms}" AINCMS_DB_PASSWORD="${AINCMS_DB_PASSWORD:-rehearsal-db-pass}"
export AINCMS_CMS_ENABLED=1 AINCMS_CMS_USER=rehearsal-owner AINCMS_CMS_PASSWORD_HASH="$(php -r 'echo password_hash("rehearsal-owner-password", PASSWORD_DEFAULT);')" AINCMS_RATE_LIMIT_SECRET=0123456789abcdef0123456789abcdef AINCMS_ENV=development AINCMS_CMS_REQUIRE_HTTPS=0 AINCMS_PUBLIC_ORIGIN=http://localhost:8080

# Reset only the dedicated rehearsal database, using the same scoped application
# account the service provisions. No server/root privilege is required.
git -C "$ROOT" show v0.1.0-rc.3:database/schema.sql > "$WORK/schema-v8.sql"
grep -q 'VALUES (1, 8)' "$WORK/schema-v8.sql"
MYSQL=(mysql -h "$AINCMS_DB_HOST" -P "$AINCMS_DB_PORT" -u "$AINCMS_DB_USER" -p"$AINCMS_DB_PASSWORD" "$AINCMS_DB_NAME")
while IFS= read -r table; do
  [[ -n "$table" ]] || continue
  "${MYSQL[@]}" -e "SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS \\`$table\\`; SET FOREIGN_KEY_CHECKS=1;"
done < <("${MYSQL[@]}" -Nse 'SHOW TABLES')
"${MYSQL[@]}" < "$WORK/schema-v8.sql"
php -r 'require "api/runtime.php"; if((dbHealth()["schemaVersion"]??0)!==8) throw new RuntimeException("published rc.3 fixture is not schema 8");'

cat > "$WORK/seed-v8.php" <<'PHP'
<?php
declare(strict_types=1);$root=$argv[1];require $root.'/api/database.php';$pdo=db();$html='<section><h1 data-cms-id="probe.title">Migration probe</h1><p data-cms-id="probe.copy">Before</p></section>';$vars=[['key'=>'copy-probe.title','type'=>'richtext','label'=>'H1','target'=>['kind'=>'cms','id'=>'probe.title'],'default'=>'Migration probe'],['key'=>'copy-probe.copy','type'=>'richtext','label'=>'Copy','target'=>['kind'=>'cms','id'=>'probe.copy'],'default'=>'Before']];$stmt=$pdo->prepare("INSERT INTO page_block_templates (template_key,label,category,source_page,source_ordinal,source_hash,html_content,variables_json,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())");$stmt->execute(['migration-probe','Migration probe','Section','index.html',0,hash('sha256',$html),$html,json_encode($vars,JSON_UNESCAPED_SLASHES)]);$blocks=[['templateKey'=>'migration-probe','instanceId'=>'probe-01','values'=>['copy-probe.title'=>'Migration probe','copy-probe.copy'=>'Before']]];$stmt=$pdo->prepare("INSERT INTO page_compositions (page_path,label,title,shell_path,parent_path,blocks_json,updated_by,created_at,updated_at) VALUES (?,?,?,?,NULL,?,NULL,UTC_TIMESTAMP(),UTC_TIMESTAMP())");$stmt->execute(['migration-probe.html','Migration probe','Migration probe','index.html',json_encode($blocks,JSON_UNESCAPED_SLASHES)]);
PHP
php "$WORK/seed-v8.php" "$SITE"
php database/migrations/8-to-9.php --apply | tee /tmp/aincms-v9-migration.json
php database/migrations/8-to-9.php --apply | tee /tmp/aincms-v9-migration-second.json
cat > "$WORK/verify-v9.php" <<'PHP'
<?php
declare(strict_types=1);$root=$argv[1];require $root.'/api/content-rebuild.php';if((dbHealth()['schemaVersion']??0)!==9)throw new RuntimeException('schema did not advance to 9');$tables=db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);foreach(['block_presets','block_presets_legacy_archive'] as $t)if(!in_array($t,$tables,true))throw new RuntimeException('missing '.$t);if(in_array('page_block_templates',$tables,true))throw new RuntimeException('retired template table remains active');$record=blockPresetRecord('migration-probe');if(!$record||$record['kind']!=='legacy')throw new RuntimeException('legacy template did not become a converted preset');$row=db()->query("SELECT blocks_json FROM page_compositions WHERE page_path='migration-probe.html'")->fetchColumn();$blocks=json_decode((string)$row,true);if(($blocks[0]['presetKey']??'')!=='migration-probe'||isset($blocks[0]['templateKey']))throw new RuntimeException('composition reference was not rewritten');$definition=['label'=>'Primitive rehearsal','layout'=>'split','surface'=>'soft','width'=>'medium','spacing'=>'standard','elements'=>[['id'=>'heading-01','type'=>'heading','column'=>1,'html'=>'Portable heading','level'=>'h1','scale'=>'section'],['id'=>'copy-01','type'=>'paragraph','column'=>2,'html'=>'Portable copy','style'=>'lead']]];$preset=blockPresetSavePrimitive($root,null,'Primitive rehearsal','Designed',$definition);$rendered=blockPresetRender($root,(string)$preset['key'],(array)$preset['defaults'],'primitive-rehearsal-01');if(!str_contains($rendered['html'],'Portable heading')||!str_contains($rendered['html'],'cms-layout-split'))throw new RuntimeException('schema-v9 primitive did not render');$projected=compositionStructuralHtml($root,'migration-probe.html',$blocks,'index.html','Migration probe');if(!str_contains($projected,'Migration probe'))throw new RuntimeException('migrated composition did not render');echo json_encode(['ok'=>true,'schema'=>9,'convertedPreset'=>true,'compositionRewritten'=>true,'primitivePreset'=>$preset['key']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;
PHP
php "$WORK/verify-v9.php" "$SITE" | tee /tmp/aincms-v9-composition.json
python3 - /tmp/aincms-v9-migration.json /tmp/aincms-v9-migration-second.json <<'PY'
import json,sys
first=json.load(open(sys.argv[1],encoding='utf-8'));second=json.load(open(sys.argv[2],encoding='utf-8'));assert first['ok'] and first['changed'] is True;assert first['after']['schemaVersion']==9 and first['after']['blockPresets'] is True and first['after']['legacyTemplates'] is False;assert first['compositionsRewritten']>=1;assert second['ok'] and second['changed'] is False
PY
echo 'PASS: published rc.3 schema-v8 to schema-v9 composition rehearsal'
