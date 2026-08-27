#!/usr/bin/env python3
from pathlib import Path
import json
ROOT=Path(__file__).resolve().parents[1]
def need(path,*tokens):
    text=(ROOT/path).read_text(encoding='utf-8')
    for token in tokens: assert token in text,f'{path} missing {token}'
    return text
schema=need('database/schema.sql','schema v8','VALUES (1, 8)','CREATE TABLE IF NOT EXISTS redirect_records','managed_by VARCHAR(32)','uq_redirect_records_source')
migration=need('database/migrations/7-to-8.php','migration78Apply','schema v7','--apply','UPDATE app_meta SET schema_version=8','GET_LOCK')
assert 'bootstrapInstall(' not in migration,'migration must not route through bootstrap repair'
migration89=need('database/migrations/8-to-9.php','migration89Apply','schema v8','--apply','CREATE TABLE IF NOT EXISTS block_presets','UPDATE app_meta SET schema_version=9','GET_LOCK')
assert 'bootstrapInstall(' not in migration89,'schema v9 migration must not route through bootstrap repair'
redirects=need('api/redirects.php','function redirectRequireSchema(): void { dbRequireSchemaVersion(8); }','redirectNormalizeSource','redirectNormalizeTarget','redirectValidateGraph','redirectRevisionHash','redirectSaveRecord','redirectDeleteRecord','redirectPostPreflight','redirectUpsertPostSlug','redirectProject','system_aliases','redirectAcquireGraphLock','GET_LOCK','RELEASE_LOCK','Conflicting configured system redirect authorities','is_dir($requestTarget)','public file or directory')
for token in ['reserved application path','ambiguous path separator','dot segments','cycle detected','changed since it was opened','redirect graph lock']: assert token.lower() in redirects.lower(),f'redirect safety missing {token}'
assert redirects.index('redirectAcquireGraphLock($pdo)')<redirects.index('redirectValidateGraph(redirectHypothetical($candidate))'),'manual redirect graph is validated before acquiring the graph lock'
router=need('__redirect.php','__redirect-map.php','preserveQuery','Location: ');assert 'db(' not in router and 'PDO' not in router and 'database.php' not in router,'anonymous redirect runtime must be database-free'
need('api/cms-redirects.php','requireCmsAuth(true)','redirectRequireSchema()','requireCmsCsrf()','enforceRateLimit','expectedHash','redirectProject')
need('api/cms-writing.php','dbRequireSchemaVersion(8)','redirectPostPreflight','redirectUpsertPostSlug','removePostProjection','contentFinalizePublicProjections')
pages=need('api/cms-pages.php','dbRequireSchemaVersion(8)','database/reconcile.php','contentFinalizePublicProjections');assert 'contentAuthorityImport(' not in pages,'browser Pages endpoint must not import repository source'
need('database/reconcile.php','dbRequireSchemaVersion(8)','contentSyncRepository')
rebuild=need('api/content-rebuild.php','after_seo','contentFinalizePublicProjections','seoProjectAll','navigationProject','brandingProject','redirectProject')
assert 'function contentFinalizePublicProjections(string $root,?array $hooks=null,?array $seedContext=null): array {dbRequireSchemaVersion(8);' in rebuild,'schema-v8 sites must be able to reconcile/finalize immediately before the explicit v9 migration'
assert 'function contentRebuild(string $root): array {dbRequireSchemaVersion(9);' in rebuild,'full preset-aware rebuild must require schema v9'
order=[rebuild.index(x) for x in ["$context['core']['seo']=seoProjectAll","contentRebuildRunHooks($hooks,'after_seo'","$context['core']['navigation']=navigationProject","$context['core']['branding']=brandingProject","$context['core']['redirects']=redirectProject"]];assert order==sorted(order),'site-wide finalization order regressed'
for path in ['api/cms-pages.php','api/cms-media.php','api/cms-navigation.php','api/cms-branding.php','api/cms-writing.php','api/cms-seo.php']: need(path,'dbRequireSchemaVersion(8)')
need('api/cms-composer.php','dbRequireSchemaVersion(9)');need('api/cms-blocks.php','dbRequireSchemaVersion(9)')
for path in ['cms/pages.php','cms/composer.php','cms/media.php','cms/navigation.php','cms/branding.php','cms/writing.php','cms/seo.php','cms/readiness.php','cms/redirects.php']: need(path,'href="/cms/redirects.php"')
need('config/site.example.php',"'redirects' =>",'system_aliases','after_seo')
metadata=json.loads((ROOT/'release/release.json').read_text(encoding='utf-8'));assert metadata['schemaVersion']==8,'published rc.3 metadata must remain schema v8 while v9 is under development';assert metadata.get('channel') in {'internal-release-candidate','public-release-candidate'};assert metadata.get('version')==(ROOT/'VERSION').read_text().strip();assert metadata.get('version','').startswith('0.1.0-rc.')
for path in ['cms/redirects.php','cms/redirects.js','__redirect.php','__redirect-map.php','cms/blocks.php','cms/block-composer.js']: assert (ROOT/path).is_file(),f'missing {path}'
print('PASS: rc.3 schema-v8 release plus schema-v9 development upgrade boundary')
