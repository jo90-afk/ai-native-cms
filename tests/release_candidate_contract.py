#!/usr/bin/env python3
from __future__ import annotations
import hashlib, importlib.util, json, tempfile, zipfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]

def fail(message:str)->None: print(f"FAIL: {message}");raise SystemExit(1)
def load_builder():
    path=ROOT/'tools/build_release.py';spec=importlib.util.spec_from_file_location('aincms_build_release',path)
    if not path.is_file() or spec is None or spec.loader is None: fail('release candidate builder could not be loaded')
    module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module);return module

def main()->None:
    version=(ROOT/'VERSION').read_text(encoding='utf-8').strip();metadata=json.loads((ROOT/'release/release.json').read_text(encoding='utf-8'))
    if version!='0.1.0-rc.4' or metadata.get('version')!=version or metadata.get('schemaVersion')!=10: fail('release identity is not schema-v10 rc4')
    if metadata.get('channel')!='public-release-candidate': fail('rc4 is not marked as a public release candidate')
    distribution=metadata.get('distribution',{})
    if distribution.get('public') is not True or distribution.get('licenseSelected') is not True or distribution.get('tagRequired') is not True or distribution.get('tag')!=f'v{version}': fail('public release candidate distribution/tag state is wrong')
    license_meta=distribution.get('license',{});expected={'base':'Apache-2.0','condition':'Commons Clause License Condition v1.0','sourceAvailable':True,'osiApproved':False,'attributionRequired':True}
    if any(license_meta.get(k)!=v for k,v in expected.items()): fail('release candidate license metadata is wrong')
    workflow=(ROOT/'.github/workflows/ci.yml').read_text(encoding='utf-8');publisher=(ROOT/'.github/workflows/publish-release.yml').read_text(encoding='utf-8')
    if 'github.event.pull_request.head.sha || github.sha' not in workflow: fail('CI release artifact does not record reviewed source revision')
    for needle in ['contents: write','gh release create','--prerelease','release/release.json','RELEASE-NOTES-','steps.release.outputs.version']:
        if needle not in publisher: fail('version-driven public release publisher is missing: '+needle)
    if '0.1.0-rc.3.manifest.json' in publisher or '--notes-file release/RELEASE-NOTES-0.1.0-rc.3.md' in publisher: fail('publisher still hard-codes rc3 publication assets')
    builder=load_builder();candidates=[p.relative_to(ROOT).as_posix() for p in builder.candidate_files()]
    required={
        'README.md','AGENTS.md','SECURITY.md','VERSION','LICENSE','LICENSE-APACHE-2.0.txt','NOTICE','release/release.json',
        'index.html','about.html','writing.html','assets/styles.css','assets/site.js','templates/article.html','setup/site.php',
        'api/runtime.php','api/redirects.php','api/onboarding.php','api/block-presets.php','api/composition-store.php','api/page-routes.php','api/page-projection.php','api/discovery-projection.php','api/llms-projection.php','api/audience.php','api/audience-subscribe.php','api/mail-transport.php',
        'cms/composer.php','cms/blocks.php','cms/audience.php','cms/onboarding.php','config/site.example.php',
        'database/schema.sql','database/bootstrap.php','database/migrations/7-to-8.php','database/migrations/8-to-9.php','database/migrations/9-to-10.php','database/private-config.example.ini',
        '__redirect.php','__redirect-map.php','adapters/apache/public.htaccess.example','adapters/apache/private.htaccess.example',
        'docs/ARCHITECTURE.md','docs/INSTALLATION.md','docs/RELEASE.md','docs/CPANEL-EMAIL.md','docs/DEPLOYMENT-ADAPTERS.md','docs/REPOSITORY-OPERATIONS.md','docs/LLM-COLLABORATION.md',
    }
    missing=sorted(required.difference(candidates));
    if missing: fail('release candidate is missing required files: '+', '.join(missing))
    forbidden_prefixes=('.git/','.github/','.lattice/','tests/','tools/','dist/','runtime/','uploads/')
    for path in candidates:
        if path=='config/site.php' or path.startswith(forbidden_prefixes): fail('excluded operational/adopter path entered candidate: '+path)
        if path.endswith('.ini') and path!='database/private-config.example.ini': fail('non-example INI entered candidate: '+path)
    with tempfile.TemporaryDirectory() as tmp:
        a=builder.build(Path(tmp)/'one','release-contract-ref');b=builder.build(Path(tmp)/'two','release-contract-ref');za=Path(a['zip']).read_bytes();zb=Path(b['zip']).read_bytes()
        if za!=zb: fail('release candidate ZIP is not byte-for-byte reproducible')
        if hashlib.sha256(za).hexdigest()!=a['sha256']: fail('reported release checksum does not match candidate ZIP')
        ma=json.loads(Path(a['manifest']).read_text(encoding='utf-8'));mb=json.loads(Path(b['manifest']).read_text(encoding='utf-8'))
        if ma!=mb or ma.get('sourceRevision')!='release-contract-ref' or ma.get('schemaVersion')!=10: fail('release manifest provenance/schema is wrong')
        if ma.get('version')!=version or ma.get('distribution',{}).get('tag')!=f'v{version}': fail('release manifest lost rc4 identity')
        with zipfile.ZipFile(a['zip'],'r') as archive:
            names=archive.namelist();root=metadata['packageRoot'].rstrip('/')+'/'
            if root+'RELEASE-MANIFEST.json' not in names: fail('candidate ZIP does not contain its release manifest')
            if any(name.startswith(root+prefix) for prefix in forbidden_prefixes for name in names): fail('candidate ZIP contains an excluded operational path')
            if root+'config/site.php' in names: fail('candidate ZIP contains adopter-local config/site.php')
            for path in required:
                if path.startswith('release/') or path=='SECURITY.md' or path.startswith('.'): continue
                if path in candidates and root+path not in names: fail('rc4 candidate omitted '+path)
    installation=(ROOT/'docs/INSTALLATION.md').read_text(encoding='utf-8');release_doc=(ROOT/'docs/RELEASE.md').read_text(encoding='utf-8')
    for needle in ['database/migrations/8-to-9.php --apply','database/migrations/9-to-10.php --apply','schema 10','backup','rollback','/cms/onboarding.php']:
        if needle.lower() not in installation.lower(): fail('installation/upgrade documentation is missing: '+needle)
    for needle in ['0.1.0-rc.4','schema 10','v0.1.0-rc.4','Commons Clause','source-available','proving-ground','sha256']:
        if needle.lower() not in release_doc.lower(): fail('public release documentation is missing: '+needle)
    print('PASS: reproducible public licensed schema-v10 rc4 contract')

if __name__=='__main__': main()
