#!/usr/bin/env python3
from pathlib import Path
import json,re
ROOT=Path(__file__).resolve().parents[1]
def need(path,*tokens):
    text=(ROOT/path).read_text(encoding='utf-8')
    for token in tokens: assert token in text,f'{path} missing {token}'
    return text
schema=need('database/schema.sql','schema v10','VALUES (1, 10)','CREATE TABLE IF NOT EXISTS redirect_records','CREATE TABLE IF NOT EXISTS block_presets','CREATE TABLE IF NOT EXISTS audience_lists','CREATE TABLE IF NOT EXISTS audience_subscriptions')
assert not re.search(r'CREATE TABLE IF NOT EXISTS\s+page_block_templates\b',schema),'fresh schema must not restore retired page_block_templates authority'
assert not re.search(r'CREATE TABLE IF NOT EXISTS\s+subscribers\b',schema),'fresh schema must not restore retired subscribers authority'
migration=need('database/migrations/7-to-8.php','migration78Apply','schema v7','--apply','UPDATE app_meta SET schema_version=8','GET_LOCK');assert 'bootstrapInstall(' not in migration
migration89=need('database/migrations/8-to-9.php','migration89Apply','schema v8','--apply','CREATE TABLE IF NOT EXISTS block_presets','UPDATE app_meta SET schema_version=9','GET_LOCK');assert 'bootstrapInstall(' not in migration89
migration910=need('database/migrations/9-to-10.php','migration910Apply','schema v9','--apply','CREATE TABLE IF NOT EXISTS audience_lists','CREATE TABLE IF NOT EXISTS audience_subscriptions','UPDATE app_meta SET schema_version=10','GET_LOCK');assert 'bootstrapInstall(' not in migration910
redirects=need('api/redirects.php','function redirectRequireSchema(): void { dbRequireSchemaVersion(8); }','redirectNormalizeSource','redirectNormalizeTarget','redirectValidateGraph','redirectRevisionHash','redirectSaveRecord','redirectDeleteRecord','redirectPostPreflight','redirectUpsertPostSlug','redirectProject','system_aliases','redirectAcquireGraphLock','GET_LOCK','RELEASE_LOCK','Conflicting configured system redirect authorities','is_dir($requestTarget)','public file or directory')
for token in ['reserved application path','ambiguous path separator','dot segments','cycle detected','changed since it was opened','redirect graph lock']: assert token.lower() in redirects.lower(),f'redirect safety missing {token}'
assert redirects.index('redirectAcquireGraphLock($pdo)')<redirects.index('redirectValidateGraph(redirectHypothetical($candidate))')
router=need('__redirect.php','__redirect-map.php','preserveQuery','Location: ');assert 'db(' not in router and 'PDO' not in router and 'database.php' not in router
need('api/cms-redirects.php','requireCmsAuth(true)','redirectRequireSchema()','requireCmsCsrf()','enforceRateLimit','expectedHash','redirectProject')
need('api/cms-writing.php','dbRequireSchemaVersion(8)','redirectPostPreflight','redirectUpsertPostSlug','removePostProjection','contentFinalizePublicProjections')
assert not (ROOT/'api/cms-pages.php').exists(),'standalone Pages mutation API must remain retired'
need('cms/pages.php','requireCmsAuth(false)',"header('Location: /cms/composer.php',true,302)");need('cms/index.php',"$target='/cms/composer.php'")
need('database/reconcile.php','dbRequireSchemaVersion(8)','contentSyncRepository','contentRebuild')
rebuild=need('api/content-rebuild.php','after_seo','contentFinalizePublicProjections','seoProjectAll','navigationProject','brandingProject','redirectProject','discoveryProject','llmsProject')
assert 'function contentFinalizePublicProjections(string $root,?array $hooks=null,?array $seedContext=null): array {dbRequireSchemaVersion(8);' in rebuild
assert 'function contentRebuild(string $root): array {dbRequireSchemaVersion(8);' in rebuild
presets=need('api/block-presets.php','blockPresetLegacyRecord','page_block_templates','blockPresetSchemaVersion()<9','dbRequireSchemaVersion(9)')
need('api/composition-store.php',"$item['presetKey']??$item['templateKey']??''",'blockPresetRender','expectedSourceHash','compositionSourceStateHash')
order=[rebuild.index(x) for x in ["$context['core']['seo']=seoProjectAll","contentRebuildRunHooks($hooks,'after_seo'","$context['core']['navigation']=navigationProject","$context['core']['branding']=brandingProject","$context['core']['redirects']=redirectProject","pageProjectionProjectManagedCleanRoutes","discoveryProject($root)","llmsProject($root)"]];assert order==sorted(order),'site-wide finalization order regressed'
for path in ['api/cms-media.php','api/cms-navigation.php','api/cms-branding.php','api/cms-writing.php','api/cms-seo.php']: need(path,'dbRequireSchemaVersion(8)')
need('api/cms-composer.php','dbRequireSchemaVersion(9)','previewItem','expectedSourceHash');need('api/cms-blocks.php','dbRequireSchemaVersion(9)');need('api/audience.php','dbRequireSchemaVersion(10)')
need('config/site.example.php',"'redirects' =>",'system_aliases','after_seo',"'clean_managed_routes' => true")
metadata=json.loads((ROOT/'release/release.json').read_text(encoding='utf-8'));version=(ROOT/'VERSION').read_text().strip();assert version=='0.1.0-rc.4';assert metadata['version']==version and metadata['schemaVersion']==10 and metadata['distribution']['tag']==f'v{version}'
for path in ['cms/redirects.php','cms/blocks.php','cms/composer-live.css','cms/block-composer-embed.js','cms/audience.php','api/discovery-projection.php','api/llms-projection.php']: assert (ROOT/path).is_file(),f'missing {path}'
print('PASS: rc.4 schema-v10 fresh install plus schema-v8 upgrade compatibility')
