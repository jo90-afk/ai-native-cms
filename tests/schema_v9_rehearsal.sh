#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
ZIP="$(find "$ROOT/dist/rehearsal-a" -maxdepth 1 -name '*.zip' -print -quit)"
[[ -n "$ZIP" ]]
python3 - "$ZIP" "$WORK" <<'PY'
import sys,zipfile
with zipfile.ZipFile(sys.argv[1]) as z:z.extractall(sys.argv[2])
PY
SITE="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -print -quit)"
cd "$SITE"
php setup/site.php --name="Schema V9 Rehearsal" --url=http://localhost:8080 --owner="Rehearsal Owner" >/tmp/aincms-v9-site-setup.txt
export AINCMS_DB_HOST="${AINCMS_DB_HOST:-127.0.0.1}"
export AINCMS_DB_PORT="${AINCMS_DB_PORT:-3306}"
export AINCMS_DB_NAME="${AINCMS_DB_NAME:-aincms}"
export AINCMS_DB_USER="${AINCMS_DB_USER:-aincms}"
export AINCMS_DB_PASSWORD="${AINCMS_DB_PASSWORD:-rehearsal-db-pass}"
export AINCMS_CMS_ENABLED=1
export AINCMS_CMS_USER=rehearsal-owner
export AINCMS_CMS_PASSWORD_HASH="$(php -r 'echo password_hash("rehearsal-owner-password", PASSWORD_DEFAULT);')"
export AINCMS_RATE_LIMIT_SECRET=0123456789abcdef0123456789abcdef
export AINCMS_ENV=development
export AINCMS_CMS_REQUIRE_HTTPS=0
export AINCMS_PUBLIC_ORIGIN=http://localhost:8080

php -r 'require "api/runtime.php"; if((dbHealth()["schemaVersion"]??0)!==8) throw new RuntimeException("v9 rehearsal must start from restored schema 8");'
cat > "$WORK/seed-v8.php" <<'PHP'
<?php
declare(strict_types=1);
$root=$argv[1];require $root.'/api/composer.php';require $root.'/api/content-authority.php';
$pdo=db();
$pdo->prepare('DELETE FROM page_compositions WHERE page_path=?')->execute(['migration-probe.html']);
$pdo->prepare('DELETE FROM page_block_templates WHERE template_key=?')->execute(['migration-probe']);
$html='<section class="probe"><h1 data-cms-id="probe.title">Migration probe</h1><p data-cms-id="probe.copy">Before</p></section>';
$vars=[
 ['key'=>'copy-probe.title','type'=>'richtext','label'=>'H1','target'=>['kind'=>'cms','id'=>'probe.title'],'default'=>'Migration probe'],
 ['key'=>'copy-probe.copy','type'=>'richtext','label'=>'Copy','target'=>['kind'=>'cms','id'=>'probe.copy'],'default'=>'Before'],
];
$stmt=$pdo->prepare("INSERT INTO page_block_templates (template_key,label,category,source_page,source_ordinal,source_hash,html_content,variables_json,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())");
$stmt->execute(['migration-probe','Migration probe','Section','index.html',0,hash('sha256',$html),$html,json_encode($vars,JSON_UNESCAPED_SLASHES)]);
$blocks=[['templateKey'=>'migration-probe','instanceId'=>'probe-01','values'=>['copy-probe.title'=>'Migration probe','copy-probe.copy'=>'Before']]];
$stmt=$pdo->prepare("INSERT INTO page_compositions (page_path,label,title,shell_path,parent_path,blocks_json,updated_by,created_at,updated_at) VALUES (?,?,?,?,NULL,?,NULL,UTC_TIMESTAMP(),UTC_TIMESTAMP())");
$stmt->execute(['migration-probe.html','Migration probe','Migration probe','index.html',json_encode($blocks,JSON_UNESCAPED_SLASHES)]);

// Seed the exact repository block identities Page Composer will adopt after v9.
$source=composerSourceHtml($root,'index.html');$insert=$pdo->prepare("INSERT INTO page_block_templates (template_key,label,category,source_page,source_ordinal,source_hash,html_content,variables_json,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE label=VALUES(label),category=VALUES(category),source_page=VALUES(source_page),source_ordinal=VALUES(source_ordinal),source_hash=VALUES(source_hash),html_content=VALUES(html_content),variables_json=VALUES(variables_json),updated_at=UTC_TIMESTAMP()");
foreach(composerExtractTopBlocks($source) as $i=>$block){$variables=composerTemplateVariables($block);$insert->execute([composerTemplateKey('index.html',$i),composerTemplateLabel($block,$i),composerTemplateCategory($block),'index.html',$i,hash('sha256',$block),$block,json_encode($variables,JSON_UNESCAPED_SLASHES)]);}
$pageBlocks=contentAuthorityPageBlocks('index.html');if(!isset($pageBlocks['home-title']))throw new RuntimeException('starter home-title canonical block is unavailable');
$outcome=contentAuthorityCommitBlock('index.html','home-title','Canonical before adoption','cms','m016-rehearsal',(string)$pageBlocks['home-title']['hash']);if($outcome!=='applied')throw new RuntimeException('pre-adoption canonical edit was not applied');contentAuthorityProjectPage($root,'index.html');
PHP
php "$WORK/seed-v8.php" "$SITE"
php database/migrations/8-to-9.php --apply | tee /tmp/aincms-v9-migration.json
php database/migrations/8-to-9.php --apply | tee /tmp/aincms-v9-migration-second.json
cat > "$WORK/verify-v9.php" <<'PHP'
<?php
declare(strict_types=1);
$root=$argv[1];require $root.'/api/content-rebuild.php';
if((dbHealth()['schemaVersion']??0)!==9)throw new RuntimeException('schema did not advance to 9');
$tables=db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);if(!in_array('block_presets',$tables,true)||!in_array('block_presets_legacy_archive',$tables,true)||in_array('page_block_templates',$tables,true))throw new RuntimeException('preset authority/archive tables are wrong');
$record=blockPresetRecord('migration-probe');if(!$record||$record['kind']!=='legacy')throw new RuntimeException('legacy template did not become a converted preset');
$row=db()->query("SELECT blocks_json FROM page_compositions WHERE page_path='migration-probe.html'")->fetchColumn();$blocks=json_decode((string)$row,true);if(($blocks[0]['presetKey']??'')!=='migration-probe'||isset($blocks[0]['templateKey']))throw new RuntimeException('composition reference was not rewritten to presetKey');
$definition=['label'=>'Primitive rehearsal','layout'=>'split','surface'=>'soft','width'=>'medium','spacing'=>'standard','elements'=>[
 ['id'=>'heading-01','type'=>'heading','column'=>1,'html'=>'<script>bad()</script>Portable heading','level'=>'h1','scale'=>'section'],
 ['id'=>'copy-01','type'=>'paragraph','column'=>1,'html'=>'Portable copy','style'=>'lead'],
 ['id'=>'metric-01','type'=>'metric','column'=>2,'value'=>'3×','label'=>'Governed metric'],
 ['id'=>'button-01','type'=>'button','column'=>2,'label'=>'Read more','href'=>'/about.html','tone'=>'secondary'],
]];
$preset=blockPresetSavePrimitive($root,null,'Primitive rehearsal','Designed',$definition);$key=(string)$preset['key'];
$rendered=blockPresetRender($root,$key,(array)$preset['defaults'],'primitive-rehearsal-01');if(str_contains($rendered['html'],'<script>')||!str_contains($rendered['html'],'data-cms-id=')||!str_contains($rendered['html'],'cms-layout-split'))throw new RuntimeException('primitive renderer violated governance contract');
$page=compositionStructuralHtml($root,'index.html',[['presetKey'=>$key,'instanceId'=>'primitive-page-01','values'=>(array)$preset['defaults']]],'index.html','Schema V9 Rehearsal');if(preg_match_all('/<h1\b/i',$page)!==1)throw new RuntimeException('primitive composition did not preserve exactly one H1');

// M-016: hydrate a pre-existing canonical leaf before first composition adoption.
$payload=compositionPayload($root,'index.html');if(!$payload['sourceDerived']||$payload['hash']!=='none'||empty($payload['sourceHash']))throw new RuntimeException('repository page was not exposed as guarded source-derived composition');
$titleBlock=-1;$titleKey='';foreach($payload['blocks'] as $i=>$block){$p=blockPresetRecord((string)$block['presetKey']);foreach((array)($p['variables']??[]) as $v){if(($v['target']['kind']??'')==='cms'&&($v['target']['id']??'')==='home-title'){$titleBlock=$i;$titleKey=(string)$v['key'];break 2;}}}
if($titleBlock<0||$titleKey===''||($payload['blocks'][$titleBlock]['values'][$titleKey]??'')!=='Canonical before adoption')throw new RuntimeException('source-derived typed values lost canonical page copy');
$staleRejected=false;try{compositionSave($root,'index.html',$payload['blocks'],'none',['label'=>$payload['label'],'title'=>$payload['title'],'shellPath'=>$payload['shellPath'],'parentPath'=>$payload['parentPath'],'expectedSourceHash'=>'stale']);}catch(RuntimeException $e){$staleRejected=str_contains($e->getMessage(),'Page source changed since the composer was opened');}
if(!$staleRejected)throw new RuntimeException('first-adoption stale source guard did not reject a bad hash');
$adopted=compositionSave($root,'index.html',$payload['blocks'],'none',['label'=>$payload['label'],'title'=>$payload['title'],'shellPath'=>$payload['shellPath'],'parentPath'=>$payload['parentPath'],'expectedSourceHash'=>$payload['sourceHash']]);
$live=compositionPayload($root,'index.html');$live['blocks'][$titleBlock]['values'][$titleKey]='Live composer canonical edit';$saved=compositionSave($root,'index.html',$live['blocks'],$adopted['hash'],['label'=>$live['label'],'title'=>$live['title'],'shellPath'=>$live['shellPath'],'parentPath'=>$live['parentPath']]);
$canonical=contentAuthorityPageBlocks('index.html');if(($canonical['home-title']['html']??'')!=='Live composer canonical edit')throw new RuntimeException('live typed copy did not become canonical page_blocks state');
$public=(string)file_get_contents($root.'/index.html');if(!str_contains($public,'Live composer canonical edit'))throw new RuntimeException('live typed copy did not reach public projection');
$stored=compositionRecord('index.html');if(!$stored||($stored['blocks'][$titleBlock]['values'][$titleKey]??'')!=='Live composer canonical edit')throw new RuntimeException('live typed copy did not persist in composition snapshot');
contentRebuild($root);$canonical=contentAuthorityPageBlocks('index.html');$public=(string)file_get_contents($root.'/index.html');if(($canonical['home-title']['html']??'')!=='Live composer canonical edit'||!str_contains($public,'Live composer canonical edit'))throw new RuntimeException('later rebuild overwrote live canonical copy');
echo json_encode(['ok'=>true,'preset'=>$key,'schema'=>9,'sourceGuard'=>true,'liveCopy'=>'preserved'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;
PHP
php "$WORK/verify-v9.php" "$SITE" | tee /tmp/aincms-v9-composition.json
python3 - /tmp/aincms-v9-migration.json /tmp/aincms-v9-migration-second.json <<'PY'
import json,sys
first=json.load(open(sys.argv[1],encoding='utf-8'));second=json.load(open(sys.argv[2],encoding='utf-8'))
assert first['ok'] and first['changed'] is True
assert first['after']['schemaVersion']==9 and first['after']['blockPresets'] is True and first['after']['legacyTemplates'] is False
assert first['compositionsRewritten']>=1
assert second['ok'] and second['changed'] is False
PY
echo 'PASS: schema-v9 migration, live composer convergence, and governed composition rehearsal'
