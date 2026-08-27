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
    composer=text('api/composer.php');primitives=text('api/composer-primitives.php');presets=text('api/block-presets.php');values=text('api/composition-values.php');store=text('api/composition-store.php');composer_api=text('api/cms-composer.php');blocks_api=text('api/cms-blocks.php');migration=text('database/migrations/8-to-9.php');media=text('api/media.php');media_api=text('api/cms-media.php');sync=text('api/content-sync.php');rebuild=text('api/content-rebuild.php');composer_php=text('cms/composer.php');pages_php=text('cms/pages.php');blocks_php=text('cms/blocks.php');composer_js=text('cms/composer.js');bootstrap_js=text('cms/composer-bootstrap.js');blocks_js=text('cms/block-composer.js');embed_js=text('cms/block-composer-embed.js');picker=text('cms/media-picker.js');live_css=text('cms/composer-live.css')
    require(composer,['composerTemplateVariables',"'type'=>'richtext'","'type'=>'media'","'type'=>'link'",'cmsSanitizeRichHtml','composerSafeHref','composerRekeyEditableIds'],'converted preset typed-value core')
    require(primitives,['composerPrimitiveCatalog',"'eyebrow'","'heading'","'paragraph'","'image'","'button'","'quote'","'metric'","'divider'","'elements'=>18","'h1'=>1",'composerPrimitiveNormalize','composerPrimitiveRender','data-cms-id'],'governed primitive core')
    require(presets,['block_presets','blockPresetRecord','blockPresetUsageCount','blockPresetRender','blockPresetSavePrimitive','blockPresetDelete','Designed in Block Composer'],'saved preset authority')
    require(values,['compositionHydratePresetValues','compositionValueRenderedCmsId','composerNormalizeValues','mediaAllowedPath'],'typed live-state hydration')
    require(store,['page_compositions','compositionHashValue','expectedHash','expectedSourceHash','compositionSourceStateHash','compositionHydratedItems','compositionSyncCanonicalBlocks($path,$structural,true)','contentAuthorityOverlayBlocks','presetKey','A composed public page must contain exactly one H1 heading',"'composition'"],'canonical live composition store')
    require(composer_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-composer'",'dbRequireSchemaVersion(9)','blockPresets()','compositionSave','mediaRefreshLibrary','bootstrapPresets','previewItem','compositionRenderBlock','expectedSourceHash'],'live composer API guard')
    require(blocks_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-blocks'",'dbRequireSchemaVersion(9)','savePrimitive','blockPresetDelete'],'block API guard')
    require(migration,['migration89Apply','schema v8','--apply','CREATE TABLE IF NOT EXISTS block_presets','RENAME TABLE page_block_templates TO block_presets_legacy_archive',"$block['presetKey']", "unset($block['templateKey'])",'GET_LOCK'],'explicit 8-to-9 migration')
    if 'bootstrapInstall(' in migration: fail('schema 8-to-9 migration must not route through bootstrap repair')
    require(media,['mediaAllowedPath','pathInside','is_uploaded_file','finfo(FILEINFO_MIME_TYPE)','getimagesize','move_uploaded_file','image/jpeg','image/png','image/webp','image/gif'],'media safety')
    if "image/svg+xml'=>'svg'" in media: fail('SVG must not be accepted through the upload path')
    require(media_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-media'",'multipart/form-data','mediaUpload'],'media API guard')
    require(sync,['if(compositionExists($path))continue;'],'composition-aware repository reconciliation')
    if (ROOT/'api/cms-pages.php').exists(): fail('retired standalone page mutation API returned')
    require(pages_php,["header('Location: /cms/composer.php',true,302)",'requireCmsAuth(false)'],'Pages compatibility redirect')
    positions=[rebuild.find('contentAuthorityProjectPages($root)'),rebuild.find('compositionProjectAll($root)'),rebuild.find('projectPublishedPosts($root)')]
    if min(positions)<0 or positions!=sorted(positions): fail('rebuild must project base pages -> compositions -> publishing')
    require(composer_php,['sandbox="allow-same-origin"','composer-live.css','composer-frame','selected-panel','block-dialog','bootstrap-presets','composer-bootstrap.js'],'live Page Composer shell')
    if 'allow-scripts' in composer_php: fail('public-page preview iframe must not execute site scripts')
    if 'href="/cms/pages.php"' in composer_php: fail('Page Composer navigation still exposes the retired Pages workflow')
    require(blocks_php,['data-block-open-key','data-block-new-on-load','cms-embed','block-composer-embed.js'],'embeddable Block Composer')
    require(composer_js,['presetKey','instanceId','values','expectedHash','expectedSourceHash','sourceDerived','contentEditable','previewItem','AINCMSMediaPicker','aincms:block-preset-saved','serializeItems'],'typed live Page Composer client')
    for forbidden in ['structuralHtml','htmlContent','outerHTML']:
        if forbidden in composer_js: fail(f'Page Composer browser mutation surface contains structural field {forbidden}')
    typed="({presetKey:item.presetKey,instanceId:item.instanceId,values:item.values||{}})"
    if typed not in composer_js: fail('Page Composer save serializer is no longer limited to typed preset instance state')
    require(bootstrap_js,['bootstrapPresets','X-CMS-CSRF','/api/cms-composer.php'],'repository preset bootstrap affordance')
    require(blocks_js,['savePrimitive','definition','AINCMSMediaPicker','contenteditable'],'Block Composer governed WYSIWYG client')
    require(embed_js,['aincms:block-preset-saved','window.parent.postMessage','data-key'],'embedded Block Composer bridge')
    require(picker,['window.AINCMSMediaPicker','media-picker-grid','onSelect'],'shared thumbnail media picker')
    require(live_css,['composer-live-frame-wrap','composer-selected-panel','composer-block-dialog','@media(max-width:760px)'],'responsive live composer layout')
    for source,name in [(composer_js,'composer.js'),(bootstrap_js,'composer-bootstrap.js'),(embed_js,'block-composer-embed.js')]:
        if 'http://' in source or 'https://' in source: fail(f'{name} contains a third-party/network runtime dependency')
    print('PASS: unified live Page Composer, schema-v9 block presets, and media contract')
if __name__=='__main__': main()
