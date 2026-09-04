#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_REF="${1:-${GITHUB_SHA:-unknown}}"
WORK="$(mktemp -d)"
SERVER_PID=""
cleanup(){ if [[ -n "$SERVER_PID" ]]; then kill "$SERVER_PID" 2>/dev/null || true; fi; rm -rf "$WORK"; }
trap cleanup EXIT

cd "$ROOT"
rm -rf dist/rehearsal-a dist/rehearsal-b
python3 tools/build_release.py --output-dir dist/rehearsal-a --source-ref "$SOURCE_REF" >/tmp/aincms-build-a.json
python3 tools/build_release.py --output-dir dist/rehearsal-b --source-ref "$SOURCE_REF" >/tmp/aincms-build-b.json
A_ZIP="$(find dist/rehearsal-a -maxdepth 1 -name '*.zip' -print -quit)"; B_ZIP="$(find dist/rehearsal-b -maxdepth 1 -name '*.zip' -print -quit)"
A_MANIFEST="$(find dist/rehearsal-a -maxdepth 1 -name '*.manifest.json' -print -quit)"; B_MANIFEST="$(find dist/rehearsal-b -maxdepth 1 -name '*.manifest.json' -print -quit)"
A_SHA="$(find dist/rehearsal-a -maxdepth 1 -name '*.sha256' -print -quit)"; B_SHA="$(find dist/rehearsal-b -maxdepth 1 -name '*.sha256' -print -quit)"
[[ -n "$A_ZIP" && -n "$B_ZIP" ]]; cmp "$A_ZIP" "$B_ZIP"; cmp "$A_MANIFEST" "$B_MANIFEST"; cmp "$A_SHA" "$B_SHA"
python3 - "$A_MANIFEST" "$SOURCE_REF" <<'PY'
import json,sys
m=json.load(open(sys.argv[1],encoding='utf-8'));assert m['sourceRevision']==sys.argv[2];assert m['version']=='0.1.0-rc.4';assert m['schemaVersion']==10;assert m['channel']=='public-release-candidate';assert m['distribution']['tag']=='v0.1.0-rc.4'
paths={r['path'] for r in m['files']}
for required in ['LICENSE','AGENTS.md','docs/INSTALLATION.md','docs/CPANEL-EMAIL.md','api/discovery-projection.php','api/markdown-projection.php','api/llms-projection.php','api/audience.php','api/mail-transport.php','database/migrations/7-to-8.php','database/migrations/8-to-9.php','database/migrations/9-to-10.php','__redirect.php']:
    assert required in paths,required
for forbidden in ['config/site.php','.lattice/PROJECT.md','tests/release_rehearsal.sh']:
    assert forbidden not in paths,forbidden
PY
python3 - "$A_ZIP" "$WORK" <<'PY'
import sys,zipfile
with zipfile.ZipFile(sys.argv[1]) as z:z.extractall(sys.argv[2])
PY
SITE="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -print -quit)"; [[ -d "$SITE" ]]; cd "$SITE"
php setup/site.php --name="Rehearsal Site" --url=http://localhost:8080 --owner="Rehearsal Owner"
[[ -f config/site.php ]]; ! grep -Eiq 'password|secret|token' config/site.php
export AINCMS_DB_HOST=127.0.0.1 AINCMS_DB_PORT="${AINCMS_DB_PORT:-3306}" AINCMS_DB_NAME="${AINCMS_DB_NAME:-aincms}" AINCMS_DB_USER="${AINCMS_DB_USER:-aincms}" AINCMS_DB_PASSWORD="${AINCMS_DB_PASSWORD:-rehearsal-db-pass}"
export AINCMS_CMS_ENABLED=1 AINCMS_CMS_USER=rehearsal-owner AINCMS_CMS_PASSWORD_HASH="$(php -r 'echo password_hash("rehearsal-owner-password", PASSWORD_DEFAULT);')" AINCMS_RATE_LIMIT_SECRET=0123456789abcdef0123456789abcdef
export AINCMS_ENV=development AINCMS_CMS_REQUIRE_HTTPS=0 AINCMS_PUBLIC_ORIGIN=http://localhost:8080
php database/bootstrap.php | tee /tmp/aincms-bootstrap.txt
grep -q 'Status: ready; schema: 10/10' /tmp/aincms-bootstrap.txt
php database/reconcile.php rc4-clean-import > /tmp/aincms-reconcile.json
php database/readiness.php > /tmp/aincms-readiness-before.json
python3 - /tmp/aincms-readiness-before.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'));assert r['ready'] is True,r['summary'];assert r['summary']['blockingFailures']==0
PY
for file in site-index.json sitemap.xml sitemap.txt llms.txt; do [[ -s "$file" ]]; done
python3 - "$SITE" > /tmp/aincms-discovery-summary.json <<'PY'
import json,sys
from pathlib import Path
root=Path(sys.argv[1]);idx=json.loads((root/'site-index.json').read_text());text=(root/'llms.txt').read_text();assert idx['site']['name']=='Rehearsal Site';assert idx['pages'];assert text.startswith('# Rehearsal Site\n');assert '/cms/' not in text;assert all(p['url'].startswith('http://localhost:8080/') for p in idx['pages']);assert any('rel="describedby" href="/llms.txt"' in p.read_text() for p in root.rglob('*.html'))
for page in idx['pages']:
    source=root/page['sourcePath'];alternate=source.with_suffix('.md');assert alternate.is_file(),alternate
    markdown=alternate.read_text();assert markdown.startswith('# ');assert 'Canonical: '+page['url'] in markdown
    assert 'http://localhost:8080/'+alternate.relative_to(root).as_posix() in text
    assert 'rel="alternate" type="text/markdown"' in source.read_text()
assert '[Expanded LLM context]' not in text, 'a missing optional corpus must remain optional'
print(json.dumps({'pages':len(idx['pages']),'markdownAlternates':len(idx['pages']),'revision':idx['revision'],'llmsBytes':len(text)}))
PY

php -S 127.0.0.1:8080 -t "$SITE" >/tmp/aincms-server.log 2>&1 & SERVER_PID=$!
for _ in {1..20}; do curl -fsS http://localhost:8080/ >/dev/null 2>&1 && break; sleep 0.25; done
curl -fsS -c "$WORK/cookies.txt" -H 'Content-Type: application/json' -H 'Origin: http://localhost:8080' -H 'Sec-Fetch-Site: same-origin' --data '{"action":"login","username":"rehearsal-owner","password":"rehearsal-owner-password"}' http://localhost:8080/api/cms-auth.php > /tmp/aincms-login.json
python3 - /tmp/aincms-login.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'));assert r['ok'] and r['authenticated'] and r['csrf']
PY
curl -fsS -b "$WORK/cookies.txt" http://localhost:8080/api/cms-onboarding.php > /tmp/aincms-onboarding.json
python3 - /tmp/aincms-onboarding.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'))['onboarding'];assert r['ready'] is True;assert r['readiness']['ready'] is True;assert r['database']['schemaVersion']==10
PY
kill "$SERVER_PID"; SERVER_PID=""

tar --exclude=.git -czf "$WORK/site-before.tgz" -C "$SITE" .
mysqldump --no-tablespaces -h "$AINCMS_DB_HOST" -P "$AINCMS_DB_PORT" -u "$AINCMS_DB_USER" -p"$AINCMS_DB_PASSWORD" "$AINCMS_DB_NAME" > "$WORK/db-before.sql"
BASE_INDEX_SHA="$(sha256sum index.html | awk '{print $1}')"
cat > "$WORK/canonical-change.php" <<'PHP'
<?php
declare(strict_types=1);$root=$argv[1];require_once $root.'/api/content-authority.php';require_once $root.'/api/content-rebuild.php';$row=db()->query('SELECT page_path,block_id,html_content,content_sha256 FROM page_blocks ORDER BY page_path,block_id LIMIT 1')->fetch();if(!is_array($row))throw new RuntimeException('No canonical block available.');$next=cmsSanitizeRichHtml((string)$row['html_content'].' <strong>Rehearsed canonical edit.</strong>');$outcome=contentAuthorityCommitBlock((string)$row['page_path'],(string)$row['block_id'],$next,'cms','rc4-rehearsal',(string)$row['content_sha256']);if($outcome!=='applied')throw new RuntimeException('Canonical edit was not applied.');contentRebuild($root);if(!str_contains((string)file_get_contents($root.'/'.(string)$row['page_path']),'Rehearsed canonical edit.'))throw new RuntimeException('Canonical edit did not project.');echo $row['page_path'],PHP_EOL;
PHP
php "$WORK/canonical-change.php" "$SITE" > /tmp/aincms-canonical-path.txt
cat > "$WORK/redirect-change.php" <<'PHP'
<?php
declare(strict_types=1);$root=$argv[1];require_once $root.'/api/redirects.php';$r=redirectSaveRecord($root,['source'=>'/legacy-rehearsal/','target'=>'/about.html','status'=>301,'preserveQuery'=>true,'active'=>true,'note'=>'rc4 rehearsal']);$p=redirectProject($root);$map=require $root.'/__redirect-map.php';if(($map['/legacy-rehearsal/']['target']??'')!=='/about.html'||($p['records']??0)<1)throw new RuntimeException('Static redirect map was not generated.');
PHP
php "$WORK/redirect-change.php" "$SITE"
! grep -q 'PDO\|db(' __redirect.php

cd "$ROOT"; rm -rf "$SITE"/* "$SITE"/.[!.]* "$SITE"/..?* 2>/dev/null || true; tar -xzf "$WORK/site-before.tgz" -C "$SITE"; mysql -h "$AINCMS_DB_HOST" -P "$AINCMS_DB_PORT" -u "$AINCMS_DB_USER" -p"$AINCMS_DB_PASSWORD" "$AINCMS_DB_NAME" < "$WORK/db-before.sql"; cd "$SITE"
[[ "$(sha256sum index.html | awk '{print $1}')" == "$BASE_INDEX_SHA" ]]; ! grep -q 'Rehearsed canonical edit.' index.html
php database/readiness.php > /tmp/aincms-readiness-after.json
python3 - /tmp/aincms-readiness-after.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'));assert r['ready'] is True and r['summary']['blockingFailures']==0
PY
! grep -RIEq 'BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|github_pat_|gh[pousr]_[A-Za-z0-9]{20,}|AKIA[0-9A-Z]{16}' "$SITE"
python3 - "$SITE/RELEASE-MANIFEST.json" "$SOURCE_REF" <<'PY'
import json,sys
m=json.load(open(sys.argv[1],encoding='utf-8'));assert m['sourceRevision']==sys.argv[2];assert m['version']=='0.1.0-rc.4';assert m['schemaVersion']==10;assert m['distribution']['tag']=='v0.1.0-rc.4'
PY
echo "PASS: clean public rc.4 schema-v10 release rehearsal from deterministic candidate"
