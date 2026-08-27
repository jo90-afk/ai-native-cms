#!/usr/bin/env python3
from __future__ import annotations
import json
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def fail(message:str)->None: print(f"FAIL: {message}");raise SystemExit(1)
def text(path:str)->str:
    file=ROOT/path
    if not file.is_file(): fail(f"missing onboarding/governance file: {path}")
    return file.read_text(encoding="utf-8")
def require(source:str,needles:list[str],label:str)->None:
    missing=[needle for needle in needles if needle not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")
def main()->None:
    metadata=json.loads(text("release/release.json"));distribution=metadata.get("distribution",{});license_meta=distribution.get("license",{});version=text('VERSION').strip()
    if version!='0.1.0-rc.4' or metadata.get('version')!=version or metadata.get('schemaVersion')!=10: fail('rc.4 metadata/version/schema boundary is wrong')
    if metadata.get('channel')!='public-release-candidate': fail('rc.4 must use the authorized public release-candidate channel')
    if distribution.get('public') is not True or distribution.get('licenseSelected') is not True or distribution.get('tagRequired') is not True or distribution.get('tag')!=f'v{version}': fail('rc.4 public distribution/license/tag state is wrong')
    expected_license={"base":"Apache-2.0","condition":"Commons Clause License Condition v1.0","sourceAvailable":True,"osiApproved":False,"attributionRequired":True}
    for key,value in expected_license.items():
        if license_meta.get(key)!=value: fail(f"license metadata disagrees on {key}")
    require(text('LICENSE'),['Commons Clause','Apache License, Version 2.0','right to Sell the Software','commercial'],'source-available license');require(text('NOTICE'),['AI Native CMS','attribution'],'attribution notice')
    for path in ['index.html','about.html','writing.html','assets/styles.css','assets/site.js','templates/article.html']: text(path)
    for path in ['index.html','about.html','writing.html']: require(text(path),['id="site-nav"','brand-mark','brand-name','data-cms-id='],f'starter page {path}')
    require(text('config/site.example.php'),["'article_template' => 'templates/article.html'","'stylesheet' => 'assets/styles.css'","'accent' => [","'contentWidth' => [","'css'=>'--content-width'"],'starter configuration')
    setup=text('setup/site.php');require(setup,["PHP_SAPI==='cli'",'siteSetupUrl','siteSetupConfig','siteSetupWrite','config/site.php already exists','never reads or writes credentials'],'safe public site initializer')
    for forbidden in ['$_POST','$_GET','REQUEST_METHOD','db()','siteSecret(']:
        if forbidden in setup: fail(f'site initializer crossed the browser/secret/database boundary: {forbidden}')
    onboarding=text('api/onboarding.php');onboarding_api=text('api/cms-onboarding.php');onboarding_ui=text('cms/onboarding.php');onboarding_js=text('cms/onboarding.js')
    require(onboarding,['onboardingStarterFiles','onboardingSiteIdentity','readinessReport($root)','brandingState()','navigationState($root)','content.authority',"$identity['customized']&&$starterReady",'Repository-owned code, rendering behavior, and configuration change through Git branches/review.','Accepted page composition, reusable block definitions, and authored content stay canonical in MySQL.','Audience list definitions, subscription state, and consent timestamps are canonical MySQL state when schema v10 Audience is used.',"'/cms/composer.php'",'Open Composer','schemaVersion','audienceAvailable','mailConfigured'],'state-derived onboarding model')
    for forbidden in ['INSERT INTO','UPDATE ','DELETE FROM','file_put_contents','cmsAtomicWrite','$_POST']:
        if forbidden in onboarding: fail(f'onboarding state model became mutating: {forbidden}')
    require(onboarding_api,['requireCmsAuth(true)',"$method!=='GET'",'onboardingState($root)'],'read-only authenticated onboarding API')
    require(onboarding_ui,['/cms/onboarding.js','Start with a working site. Make it specific.','Source configuration lives in Git. Accepted page composition and content live in the CMS.','Keep iterating without losing the source of truth.'],'onboarding workspace')
    require(onboarding_js,['/api/cms-onboarding.php','replaceChildren','progress'],'onboarding browser client')
    if any(token in onboarding_js for token in ['.innerHTML','insertAdjacentHTML','document.write']): fail('onboarding browser client introduced an HTML injection rendering path')
    cms_index=text('cms/index.php');cms_js=text('cms/cms.js');require(cms_index,['/cms/onboarding.php','onboardingState($root)','/cms/composer.php'],'state-aware authenticated CMS entry');require(cms_js,["let target = '/cms/onboarding.php'","await request('/api/cms-onboarding.php')",'state.onboarding?.ready',"target = '/cms/composer.php'",'location.href = target'],'state-aware post-login handoff')
    if '/api/cms-pages.php' in cms_js: fail('retired Pages client is still present after onboarding handoff')
    workspace_paths=['cms/composer.php','cms/blocks.php','cms/media.php','cms/navigation.php','cms/branding.php','cms/writing.php','cms/seo.php','cms/redirects.php','cms/readiness.php']
    for path in workspace_paths:
        source=text(path);require(source,['href="/cms/onboarding.php"','href="/cms/composer.php"','href="/cms/blocks.php"'],f'shared unified navigation in {path}')
        if 'href="/cms/pages.php"' in source: fail(f'retired Pages navigation returned in {path}')
    audience=text('cms/audience.php');require(audience,['href="/cms/onboarding.php"','href="/cms/composer.php"','Audience'],'Audience workspace navigation')
    require(text('cms/pages.php'),["header('Location: /cms/composer.php',true,302)"],'Pages compatibility redirect')
    require(text('docs/REPOSITORY-OPERATIONS.md'),['Commit to Git','Keep in canonical MySQL state','Generated public output','Branch and pull-request workflow','SSH pull-to-host','reviewed artifact/copy','Database backups and migrations','Rollback','Working with an LLM on the repository'],'repository/hosting operations guide')
    require(text('docs/LLM-COLLABORATION.md'),['The four kinds of state','Repository-owned state','Canonical CMS state','Generated public projection','Host/provider state','Interface / design iteration','Content iteration','Feature work','Bug fix','Schema / migration work','Release work','conversation history'],'LLM collaboration guide')
    require(text('AGENTS.md'),['Source-of-truth order','Human and agent writers use the same canonical contracts','Change packets','Conversation memory is context, not authority'],'repository agent contract')
    print('PASS: public licensed rc4 onboarding and unified page/Audience governance contract')
if __name__=='__main__': main()
