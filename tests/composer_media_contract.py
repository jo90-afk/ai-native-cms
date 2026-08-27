#!/usr/bin/env python3
from __future__ import annotations
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def fail(message:str)->None: print(f"FAIL: {message}");raise SystemExit(1)
def text(path:str)->str:
    file=ROOT/path
    if not file.is_file(): fail(f"missing composition file: {path}")
    return file.read_text(encoding='utf-8')
def require(source:str,needles:list[str],label:str)->None:
    missing=[x for x in needles if x not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")
def main()->None:
    composer=text('api/composer.php');primitives=text('api/composer-primitives.php');presets=text('api/block-presets.php');store=text('api/composition-store.php');composer_api=text('api/cms-composer.php');blocks_api=text('api/cms-blocks.php');migration=text('database/migrations/8-to-9.php');media=text('api/media.php');media_api=text('api/cms-media.php');page_api=text('api/cms-pages.php');sync=text('api/content-sync.php');rebuild=text('api/content-rebuild.php');composer_js=text('cms/composer.js');blocks_js=text('cms/block-composer.js');picker=text('cms/media-picker.js')
    require(composer,['composerTemplateVariables',"'type'=>'richtext'","'type'=>'media'","'type'=>'link'",'cmsSanitizeRichHtml','composerSafeHref','composerRekeyEditableIds'],'converted preset typed-value core')
    require(primitives,['composerPrimitiveCatalog',"'eyebrow'","'heading'","'paragraph'","'image'","'button'","'quote'","'metric'","'divider'","'elements'=>18","'h1'=>1",'composerPrimitiveNormalize','composerPrimitiveRender','data-cms-id'],'governed primitive core')
    require(presets,['block_presets','blockPresetRecord','blockPresetUsageCount','blockPresetRender','blockPresetSavePrimitive','blockPresetDelete','preset changes affect future placements' if False else 'Designed in Block Composer'],'saved preset authority')
    require(store,['page_compositions','compositionHashValue','expectedHash','contentAuthorityBackupPage','compositionSyncCanonicalBlocks','contentAuthorityOverlayBlocks','presetKey','A composed public page must contain exactly one H1 heading',"'composition'"],'canonical preset-instance composition store')
    require(composer_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-composer'",'dbRequireSchemaVersion(9)','blockPresets()','compositionSave','mediaRefreshLibrary','bootstrapPresets'],'composer API guard')
    require(blocks_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-blocks'",'dbRequireSchemaVersion(9)','savePrimitive','blockPresetDelete'],'block API guard')
    require(migration,['migration89Apply','schema v8','--apply','CREATE TABLE IF NOT EXISTS block_presets','RENAME TABLE page_block_templates TO block_presets_legacy_archive',"$block['presetKey']", "unset($block['templateKey'])",'GET_LOCK'],'explicit 8-to-9 migration')
    if 'bootstrapInstall(' in migration: fail('schema 8-to-9 migration must not route through bootstrap repair')
    require(media,['mediaAllowedPath','pathInside','is_uploaded_file','finfo(FILEINFO_MIME_TYPE)','getimagesize','move_uploaded_file','image/jpeg','image/png','image/webp','image/gif'],'media safety')
    if "image/svg+xml'=>'svg'" in media: fail('SVG must not be accepted through the upload path')
    require(media_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-media'",'multipart/form-data','mediaUpload'],'media API guard')
    require(sync,['if(compositionExists($path))continue;'],'composition-aware repository reconciliation');require(page_api,['projectCmsPage($root,$path)',"'composed'=>compositionExists($path)"],'composition-aware page editing')
    positions=[rebuild.find('contentAuthorityProjectPages($root)'),rebuild.find('compositionProjectAll($root)'),rebuild.find('projectPublishedPosts($root)')]
    if min(positions)<0 or positions!=sorted(positions): fail('rebuild must project base pages -> compositions -> publishing')
    for path,source in [('cms/composer.js',composer_js)]:
        if 'innerHTML' in source: fail(f'{path} reintroduced innerHTML into the mutation surface')
        if 'http://' in source or 'https://' in source: fail(f'{path} contains a third-party/network runtime dependency')
    if 'structuralHtml' in composer_js or 'htmlContent' in composer_js: fail('Page Composer browser payload appears to submit structural HTML')
    require(composer_js,['presetKey','instanceId','values','expectedHash','AINCMSMediaPicker'],'Page Composer browser payload')
    require(blocks_js,['savePrimitive','definition','AINCMSMediaPicker','contenteditable'],'Block Composer governed WYSIWYG client')
    require(picker,['window.AINCMSMediaPicker','media-picker-grid','onSelect'],'shared thumbnail media picker')
    print('PASS: schema-v9 block presets, composer, and media contract')
if __name__=='__main__': main()
