#!/usr/bin/env python3
from __future__ import annotations
import hashlib, importlib.util, json, tempfile, zipfile
from pathlib import Path
ROOT = Path(__file__).resolve().parents[1]

def fail(message: str) -> None:
    print(f"FAIL: {message}"); raise SystemExit(1)

def load_builder():
    path=ROOT/'tools/build_release.py';spec=importlib.util.spec_from_file_location('aincms_build_release',path)
    if not path.is_file() or spec is None or spec.loader is None: fail('release candidate builder could not be loaded')
    module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module);return module

def main() -> None:
    version=(ROOT/'VERSION').read_text(encoding='utf-8').strip()
    if version!='0.1.0-rc.2': fail('unexpected internal release candidate version')
    metadata=json.loads((ROOT/'release/release.json').read_text(encoding='utf-8'))
    if metadata.get('version')!=version or metadata.get('schemaVersion')!=8: fail('release metadata does not describe schema-v8 rc2')
    distribution=metadata.get('distribution',{})
    if distribution.get('public') is not False or distribution.get('licenseSelected') is not False: fail('release candidate metadata crossed the public/license boundary')
    workflow=(ROOT/'.github/workflows/ci.yml').read_text(encoding='utf-8')
    if 'github.event.pull_request.head.sha || github.sha' not in workflow: fail('CI release artifact does not record reviewed source revision')
    builder=load_builder();candidates=[path.relative_to(ROOT).as_posix() for path in builder.candidate_files()]
    required={
        'README.md','SECURITY.md','VERSION','release/release.json',
        'api/runtime.php','api/redirects.php','api/cms-redirects.php',
        'cms/pages.php','cms/redirects.php','cms/redirects.js','config/site.example.php',
        'database/schema.sql','database/bootstrap.php','database/migrations/7-to-8.php','database/private-config.example.ini',
        '__redirect.php','__redirect-map.php',
        'adapters/apache/public.htaccess.example','adapters/apache/private.htaccess.example',
        'docs/ARCHITECTURE.md','docs/INSTALLATION.md','docs/RELEASE.md','docs/DEPLOYMENT-ADAPTERS.md',
    }
    missing=sorted(required.difference(candidates))
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
        if ma!=mb or ma.get('sourceRevision')!='release-contract-ref' or ma.get('schemaVersion')!=8: fail('release manifest provenance/schema is wrong')
        with zipfile.ZipFile(a['zip'],'r') as archive:
            names=archive.namelist();root=metadata['packageRoot'].rstrip('/')+'/'
            if root+'RELEASE-MANIFEST.json' not in names: fail('candidate ZIP does not contain its release manifest')
            if any(name.startswith(root+prefix) for prefix in forbidden_prefixes for name in names): fail('candidate ZIP contains an excluded operational path')
            if root+'config/site.php' in names: fail('candidate ZIP contains adopter-local config/site.php')
            if any(name.rsplit('/',1)[-1].lower().startswith('license') for name in names): fail('candidate contains a license even though licenseSelected is false')
            for path in [
                'api/redirects.php','database/migrations/7-to-8.php','__redirect.php','__redirect-map.php',
                'adapters/apache/public.htaccess.example','adapters/apache/private.htaccess.example','docs/DEPLOYMENT-ADAPTERS.md',
            ]:
                if root+path not in names: fail('schema-v8 candidate omitted '+path)
    installation=(ROOT/'docs/INSTALLATION.md').read_text(encoding='utf-8');release_doc=(ROOT/'docs/RELEASE.md').read_text(encoding='utf-8')
    for needle in ['database/bootstrap.php','database/migrations/7-to-8.php --apply','database/reconcile.php initial-import','database/readiness.php','backup','rollback','migration']:
        if needle.lower() not in installation.lower(): fail('installation/upgrade documentation is missing: '+needle)
    for needle in ['internal release candidate','not a public release','license','tag','tools/build_release.py','sha256','deployment adapter']:
        if needle.lower() not in release_doc.lower(): fail('release-boundary documentation is missing: '+needle)
    print('PASS: reproducible internal schema-v8 release candidate contract')

if __name__=='__main__': main()
