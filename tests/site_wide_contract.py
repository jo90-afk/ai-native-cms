#!/usr/bin/env python3
from __future__ import annotations
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def fail(message:str)->None: print(f"FAIL: {message}");raise SystemExit(1)
def text(path:str)->str:
    file=ROOT/path
    if not file.is_file(): fail(f"missing M-006 file: {path}")
    return file.read_text(encoding='utf-8')
def require(source:str,needles:list[str],label:str)->None:
    missing=[needle for needle in needles if needle not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")
def ordered(source:str,needles:list[str],label:str)->None:
    positions=[source.find(item) for item in needles]
    if any(pos<0 for pos in positions) or positions!=sorted(positions): fail(f"{label} order is no longer preserved")
def main()->None:
    core=text('api/content-core.php');authority=text('api/content-authority.php');composer=text('api/composer.php');store=text('api/composition-store.php');composer_api=text('api/cms-composer.php');navigation=text('api/navigation.php');navigation_api=text('api/cms-navigation.php');branding=text('api/branding.php');branding_api=text('api/cms-branding.php');rebuild=text('api/content-rebuild.php');config=text('config/site.example.php')
    require(core,['function cmsConfiguredPages','function cmsManagedPages','page_compositions','cmsSafePublicTarget'],'dynamic managed page boundary')
    require(authority,['contentAuthorityRepositoryPages','cmsConfiguredPages','contentAuthorityManagedPages'],'repository/dynamic authority split')
    if 'foreach(contentAuthorityManagedPages($root) as $path=>$label)' in authority.split('function contentAuthorityDocumentSpecs',1)[1].split('function contentAuthorityLogChange',1)[0]: fail('dynamic managed pages re-entered repository page-source lineage')
    require(composer,['foreach(cmsConfiguredPages($root) as $path=>$label)'],'repository-derived converted preset harvesting')
    for stale in ['function composerSaveComposition','function composerComposition(','function composerProjectPage']:
        if stale in composer: fail('duplicate composition persistence remains in template module')
    require(store,['compositionValidateParent','Page hierarchy cannot contain a parent cycle','compositionSafeNewPagePath','compositionCreatePage','compositionRetargetShell','parent_path','shell_path','cmsSafePublicTarget','compositionSourceStateHash'],'canonical new-page hierarchy')
    require(composer_api,["$action==='create'",'compositionCreatePage','includeInNavigation','navigationAddPage','expectedSourceHash'],'new-page/live composer API')
    require(navigation,['site_navigation','navigationValidate','navigationSafeHref','presentationHash','expectedHash','navigationCandidates','compositionParentMap','id=[\"\\\']site-nav','noopener noreferrer'],'canonical navigation')
    require(navigation_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-navigation'"],'navigation API guard')
    require(branding,['site_branding','brandingDefinitions',"['color','number','length']",'^--[A-Za-z0-9_-]','expectedHash','AINCMS BRAND OVERRIDES START','identity_classes'],'bounded branding')
    require(branding_api,['requireCmsAuth(true)','requireSameOrigin(true)','requireCmsCsrf()',"enforceRateLimit('cms-branding'"],'branding API guard')
    require(config,["'navigation' => [","'branding' => [","'identity_classes' => [","'tokens' => ["],'adopter site-wide configuration')
    if 'function contentFinalizePublicProjections' not in rebuild or 'function contentRebuild(' not in rebuild: fail('site-wide finalization boundary is missing')
    finalizer=rebuild.split('function contentFinalizePublicProjections',1)[1].split('function contentRebuild(',1)[0];rebuild_body=rebuild.split('function contentRebuild(',1)[1]
    ordered(rebuild_body,['contentAuthorityProjectPages($root)','compositionProjectAll($root)','projectPublishedPosts($root)','contentFinalizePublicProjections($root'],'base page -> composition -> publishing -> finalization rebuild')
    ordered(finalizer,["contentRebuildRunHooks($hooks,'after_pages'",'seoProjectAllPublicPages($root)',"contentRebuildRunHooks($hooks,'after_seo'",'navigationProject($root)','brandingProject($root)','redirectProject($root)',"contentRebuildRunHooks($hooks,'finalize'"],'site-wide finalizer')
    for path in ['cms/composer.php','cms/blocks.php','cms/media.php','cms/navigation.php','cms/branding.php','cms/writing.php','cms/seo.php']:
        source=text(path);require(source,['/cms/navigation.php','/cms/branding.php','/cms/composer.php','/cms/blocks.php'],f'{path} site-wide control navigation')
        if '/cms/pages.php' in source: fail(f'{path} reintroduced retired Pages navigation')
    pages=text('cms/pages.php');require(pages,["header('Location: /cms/composer.php',true,302)"],'Pages compatibility redirect')
    composer_js=text('cms/composer.js')
    for forbidden in ['structuralHtml','htmlContent','outerHTML']:
        if forbidden in composer_js: fail(f'live Page Composer submits structural field {forbidden}')
    for path in ['cms/navigation.js','cms/branding.js']:
        source=text(path)
        if 'innerHTML' in source: fail(f'{path} reintroduced innerHTML')
        if 'http://' in source or 'https://' in source: fail(f'{path} contains a third-party active runtime dependency')
    if 'http://' in composer_js or 'https://' in composer_js: fail('live Page Composer contains a third-party active runtime dependency')
    print('PASS: hierarchy, navigation, branding, and unified page-authoring contract')
if __name__=='__main__': main()
