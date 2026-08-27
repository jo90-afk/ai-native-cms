#!/usr/bin/env python3
from __future__ import annotations
from pathlib import Path
import re
ROOT=Path(__file__).resolve().parents[1]
def fail(message:str)->None: print(f"FAIL: {message}");raise SystemExit(1)
def text(path:str)->str:
    file=ROOT/path
    if not file.is_file(): fail(f"missing M-003 file: {path}")
    return file.read_text(encoding='utf-8')
def require(source:str,needles:list[str],label:str)->None:
    missing=[needle for needle in needles if needle not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")
def main()->None:
    rebuild=text('api/content-rebuild.php');reconcile=text('database/reconcile.php');auth=text('api/cms-auth.php');login=text('cms/index.php');pages=text('cms/pages.php');composer=text('cms/composer.php');composer_js=text('cms/composer.js');js=text('cms/cms.js');css=text('cms/cms.css');config=text('config/site.example.php')
    require(rebuild,['before_documents','after_documents','before_pages','after_pages','after_seo','finalize','pathInside($full,$root)','is_callable','contentAuthorityProjectConfiguredDocuments','contentAuthorityProjectPages'],'rebuild hook registry')
    if 'eval(' in rebuild or 'shell_exec' in rebuild or 'exec(' in rebuild: fail('rebuild hook registry introduced dynamic shell/eval execution')
    require(reconcile,['contentRebuild($root)'],'reconciliation orchestration')
    require(config,["'hooks' => [",'adapters/discovery.php','projectDiscovery','after_seo'],'projection configuration')
    require(auth,["enforceCmsHttps(true)","requireSameOrigin(true)","enforceRateLimit('cms-login'",'requireCmsCsrf()','cmsAuthenticate','cmsLogout'],'authentication endpoint')
    require(pages,['requireCmsAuth(false)',"header('Location: /cms/composer.php',true,302)"],'retired Pages compatibility route')
    require(composer,['meta name="cms-csrf"','/cms/composer.js','/cms/cms.css','sandbox="allow-same-origin"','id="save-composition"'],'native live Page Composer shell')
    require(login,['cmsCurrentUser','/cms/cms.js','/cms/cms.css',"$target='/cms/composer.php'"],'login shell')
    for name,source in [('login',login),('composer',composer),('pages',pages)]:
        if re.search(r'<script(?!\s+src=)[^>]*>',source,flags=re.I): fail(f'{name} surface contains inline executable script')
    require(js,['/api/cms-auth.php','/api/cms-onboarding.php','/cms/composer.php','response.status === 401'],'login browser client')
    if '/api/cms-pages.php' in js or 'initPages' in js: fail('retired Pages browser client remains reachable')
    if '.innerHTML' in js or 'insertAdjacentHTML' in js or 'document.write' in js: fail('login browser client introduced an HTML injection rendering path')
    require(composer_js,['expectedHash','expectedSourceHash','presetKey','instanceId','values','contentEditable'],'live composer browser client')
    if 'http://' in js or 'https://' in js or 'http://' in composer_js or 'https://' in composer_js: fail('CMS browser client introduced a third-party/network origin')
    if (ROOT/'api/cms-pages.php').exists(): fail('retired Pages mutation API is still shipped')
    if not css.strip(): fail('CMS stylesheet is empty')
    print('PASS: rebuild and unified native CMS page-authoring contract')
if __name__=='__main__': main()
