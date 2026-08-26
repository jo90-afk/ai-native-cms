#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_REF="${1:-${GITHUB_SHA:-unknown}}"
WORK="$(mktemp -d)"
SERVER_PID=""
cleanup() {
  if [[ -n "$SERVER_PID" ]]; then kill "$SERVER_PID" 2>/dev/null || true; fi
  rm -rf "$WORK"
}
trap cleanup EXIT

cd "$ROOT"
rm -rf dist/rehearsal-a dist/rehearsal-b
python3 tools/build_release.py --output-dir dist/rehearsal-a --source-ref "$SOURCE_REF" >/tmp/aincms-build-a.json
python3 tools/build_release.py --output-dir dist/rehearsal-b --source-ref "$SOURCE_REF" >/tmp/aincms-build-b.json
A_ZIP="$(find dist/rehearsal-a -maxdepth 1 -name '*.zip' -print -quit)"
B_ZIP="$(find dist/rehearsal-b -maxdepth 1 -name '*.zip' -print -quit)"
A_MANIFEST="$(find dist/rehearsal-a -maxdepth 1 -name '*.manifest.json' -print -quit)"
B_MANIFEST="$(find dist/rehearsal-b -maxdepth 1 -name '*.manifest.json' -print -quit)"
A_SHA="$(find dist/rehearsal-a -maxdepth 1 -name '*.sha256' -print -quit)"
B_SHA="$(find dist/rehearsal-b -maxdepth 1 -name '*.sha256' -print -quit)"
[[ -n "$A_ZIP" && -n "$B_ZIP" ]]
cmp "$A_ZIP" "$B_ZIP"
cmp "$A_MANIFEST" "$B_MANIFEST"
cmp "$A_SHA" "$B_SHA"
python3 - "$A_MANIFEST" "$SOURCE_REF" <<'PY'
import json,sys
m=json.load(open(sys.argv[1],encoding='utf-8'))
assert m['sourceRevision']==sys.argv[2], (m['sourceRevision'],sys.argv[2])
assert m['version']=='0.1.0-rc.3'
assert m['schemaVersion']==8
assert m['distribution']['public'] is False
assert m['distribution']['licenseSelected'] is True
paths={r['path'] for r in m['files']}
for required in ['LICENSE','LICENSE-APACHE-2.0.txt','NOTICE','AGENTS.md','docs/INSTALLATION.md','docs/REPOSITORY-OPERATIONS.md','docs/LLM-COLLABORATION.md','setup/site.php','database/migrations/7-to-8.php','__redirect.php']:
    assert required in paths, required
for forbidden in ['config/site.php','.lattice/PROJECT.md','tests/release_rehearsal.sh']:
    assert forbidden not in paths, forbidden
PY

python3 - "$A_ZIP" "$WORK" <<'PY'
import sys,zipfile
with zipfile.ZipFile(sys.argv[1]) as z:z.extractall(sys.argv[2])
PY
SITE="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -print -quit)"
[[ -d "$SITE" ]]
cd "$SITE"

php setup/site.php --name="Rehearsal Site" --url=http://localhost:8080 --owner="Rehearsal Owner"
[[ -f config/site.php ]]
! grep -Eiq 'password|secret|token' config/site.php

export AINCMS_DB_HOST=127.0.0.1
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

php database/bootstrap.php | tee /tmp/aincms-bootstrap.txt
grep -q 'Status: ready; schema: 8/8' /tmp/aincms-bootstrap.txt
php database/reconcile.php m014-clean-import > /tmp/aincms-reconcile.json
php database/readiness.php > /tmp/aincms-readiness-before.json
python3 - /tmp/aincms-readiness-before.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'))
assert r['ready'] is True, r['summary']
assert r['summary']['blockingFailures']==0
PY

# Real authenticated first-run/onboarding boundary.
php -S 127.0.0.1:8080 -t "$SITE" >/tmp/aincms-server.log 2>&1 &
SERVER_PID=$!
for _ in {1..20}; do curl -fsS http://localhost:8080/ >/dev/null 2>&1 && break; sleep 0.25; done
curl -fsS -c "$WORK/cookies.txt" -H 'Content-Type: application/json' -H 'Origin: http://localhost:8080' -H 'Sec-Fetch-Site: same-origin' \
  --data '{"action":"login","username":"rehearsal-owner","password":"rehearsal-owner-password"}' \
  http://localhost:8080/api/cms-auth.php > /tmp/aincms-login.json
python3 - /tmp/aincms-login.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'));assert r['ok'] and r['authenticated'] and r['csrf']
PY
curl -fsS -b "$WORK/cookies.txt" http://localhost:8080/api/cms-onboarding.php > /tmp/aincms-onboarding.json
python3 - /tmp/aincms-onboarding.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'))['onboarding']
assert r['ready'] is True, r['progress']
assert r['identity']['customized'] is True
assert all(x['present'] for x in r['starter'])
assert r['readiness']['ready'] is True
PY
kill "$SERVER_PID"; SERVER_PID=""

# Capture the paired rollback point after clean initialization.
tar --exclude=.git -czf "$WORK/site-before.tgz" -C "$SITE" .
mysqldump --no-tablespaces -h "$AINCMS_DB_HOST" -P "$AINCMS_DB_PORT" -u "$AINCMS_DB_USER" -p"$AINCMS_DB_PASSWORD" "$AINCMS_DB_NAME" > "$WORK/db-before.sql"
BASE_INDEX_SHA="$(sha256sum index.html | awk '{print $1}')"

# Representative agent-governed repository design/feature change on a branch.
git init -q
git config user.name 'Rehearsal Agent'
git config user.email 'rehearsal@example.invalid'
git add .
git commit -qm 'baseline candidate'
git branch -M main
git checkout -qb agent/starter-interaction
python3 - <<'PY'
from pathlib import Path
p=Path('index.html');s=p.read_text();s=s.replace('<main>','<main data-agent-rehearsal="true">',1);s=s.replace('</body>','<script src="/assets/rehearsal-feature.js" defer></script>\n</body>',1);p.write_text(s)
Path('assets/rehearsal-feature.js').write_text("'use strict';\ndocument.documentElement.dataset.rehearsalFeature = 'active';\n")
PY
node --check assets/rehearsal-feature.js
grep -q 'data-agent-rehearsal="true"' index.html
grep -q '/assets/rehearsal-feature.js' index.html
git add index.html assets/rehearsal-feature.js
git commit -qm 'rehearsal: agent-owned starter interaction'
git diff --name-only main...HEAD | grep -qx 'assets/rehearsal-feature.js\|index.html' || true
[[ "$(git diff --name-only main...HEAD | sort | tr '\n' ' ')" == 'assets/rehearsal-feature.js index.html ' ]]

# Canonical authored change through the same content-authority store and deterministic page projector.
cat > "$WORK/canonical-change.php" <<'PHP'
<?php
declare(strict_types=1);
$root=$argv[1];require_once $root.'/api/content-authority.php';
$row=db()->query('SELECT page_path,block_id,html_content,content_sha256 FROM page_blocks ORDER BY page_path,block_id LIMIT 1')->fetch();
if(!is_array($row))throw new RuntimeException('No canonical block available for rehearsal.');
$next=cmsSanitizeRichHtml((string)$row['html_content'].' <strong>Rehearsed canonical edit.</strong>');
$outcome=contentAuthorityCommitBlock((string)$row['page_path'],(string)$row['block_id'],$next,'cms','m014-rehearsal',(string)$row['content_sha256']);
if($outcome!=='applied')throw new RuntimeException('Canonical edit was not applied: '.$outcome);
contentAuthorityProjectPage($root,(string)$row['page_path']);
if(!str_contains((string)file_get_contents($root.'/'.(string)$row['page_path']),'Rehearsed canonical edit.'))throw new RuntimeException('Canonical edit did not project.');
echo $row['page_path'],PHP_EOL;
PHP
php "$WORK/canonical-change.php" "$SITE" > /tmp/aincms-canonical-path.txt

# Canonical redirect -> generated database-free map -> anonymous router entry point.
cat > "$WORK/redirect-change.php" <<'PHP'
<?php
declare(strict_types=1);
$root=$argv[1];require_once $root.'/api/redirects.php';
$r=redirectSaveRecord($root,['source'=>'/legacy-rehearsal/','target'=>'/about.html','status'=>301,'preserveQuery'=>true,'active'=>true,'note'=>'M-014 transport rehearsal']);
$p=redirectProject($root);$map=require $root.'/__redirect-map.php';
if(($map['/legacy-rehearsal/']['target']??'')!=='/about.html'||($p['records']??0)<1)throw new RuntimeException('Static redirect map was not generated.');
PHP
php "$WORK/redirect-change.php" "$SITE"
REQUEST_URI='/legacy-rehearsal/?from=rehearsal' QUERY_STRING='from=rehearsal' php __redirect.php >/tmp/aincms-router.out
! grep -q 'PDO\|db(' __redirect.php

# Paired recovery: restore canonical SQL and repository/public files together.
cd "$ROOT"
rm -rf "$SITE"/* "$SITE"/.[!.]* "$SITE"/..?* 2>/dev/null || true
tar -xzf "$WORK/site-before.tgz" -C "$SITE"
mysql -h "$AINCMS_DB_HOST" -P "$AINCMS_DB_PORT" -u "$AINCMS_DB_USER" -p"$AINCMS_DB_PASSWORD" "$AINCMS_DB_NAME" < "$WORK/db-before.sql"
cd "$SITE"
[[ "$(sha256sum index.html | awk '{print $1}')" == "$BASE_INDEX_SHA" ]]
! grep -q 'Rehearsed canonical edit.' index.html
php database/readiness.php > /tmp/aincms-readiness-after.json
python3 - /tmp/aincms-readiness-after.json <<'PY'
import json,sys
r=json.load(open(sys.argv[1],encoding='utf-8'));assert r['ready'] is True and r['summary']['blockingFailures']==0
PY

# Final clean-candidate residue and publication-boundary assertions.
! grep -RIEq 'BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|github_pat_|gh[pousr]_[A-Za-z0-9]{20,}|AKIA[0-9A-Z]{16}' "$SITE"
python3 - "$SITE/RELEASE-MANIFEST.json" "$SOURCE_REF" <<'PY'
import json,sys
m=json.load(open(sys.argv[1],encoding='utf-8'));assert m['sourceRevision']==sys.argv[2];assert m['distribution']['public'] is False
PY

echo "PASS: clean rc.3 release rehearsal from deterministic candidate"
