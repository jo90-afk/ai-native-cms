#!/usr/bin/env python3
from __future__ import annotations
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)

def text(path: str) -> str:
    file = ROOT / path
    if not file.is_file(): fail(f"missing M-007 file: {path}")
    return file.read_text(encoding="utf-8")

def require(source: str, needles: list[str], label: str) -> None:
    missing=[needle for needle in needles if needle not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")

def main() -> None:
    bootstrap=text('database/bootstrap-core.php')
    cli=text('database/bootstrap.php')
    readiness=text('api/readiness.php')
    readiness_api=text('api/cms-readiness.php')
    readiness_ui=text('cms/readiness.php')
    readiness_js=text('cms/readiness.js')
    config=text('config/site.example.php')

    require(bootstrap,[
        'bootstrapSchemaVersion','bootstrapRequiredTables','bootstrapSqlStatements','bootstrapClassifyState',
        "status']==='foreign'","status']==='partial'","--repair",'GET_LOCK','RELEASE_LOCK',
        'bootstrapInstallOwner','bootstrapOwnerCount','database/reconcile.php',
    ],'portable bootstrap')
    if 'content-seed' in bootstrap or 'INSERT INTO posts' in bootstrap:
        fail('portable bootstrap must not seed adopter content')
    if 'ON DUPLICATE KEY UPDATE password_hash' in bootstrap:
        fail('bootstrap appears able to overwrite an existing owner password')
    require(cli,["PHP_SAPI!=='cli'",'bootstrapInstall','--repair'],'CLI-only bootstrap entry point')

    require(readiness,[
        'readinessCheck','readinessCoreChecks','readinessDatabaseChecks','readinessFilesystemChecks',
        'readinessWritableTarget','contentAuthorityStatus','SHOW GRANTS FOR CURRENT_USER','Ssl_cipher',
        "siteConfigValue('readiness','adapters'",'pathInside','readinessAdapterChecks','blockingFailures',
    ],'read-only readiness model')
    for forbidden in ['mail(', 'exec(', 'shell_exec(', 'system(', 'passthru(']:
        if forbidden in readiness: fail(f'readiness model contains active side effect primitive: {forbidden}')
    require(readiness_api,['requireCmsAuth(true)',"$method!=='GET'",'readinessReport'],'authenticated readiness API')
    if 'requireCmsCsrf' in readiness_api or "REQUEST_METHOD']??'GET')==='POST'" in readiness_api:
        fail('readiness API should remain GET-only and non-mutating')
    require(config,["'readiness' => [","'adapters' => [","'script' => 'adapters/readiness-shared-host.php'","'callable' => 'sharedHostReadiness'"],'readiness adapter seam')
    require(readiness_ui,['Read-only diagnostics','/cms/readiness.php'],'native readiness workspace')
    if 'innerHTML' in readiness_js: fail('readiness UI reintroduced innerHTML')
    if 'http://' in readiness_js or 'https://' in readiness_js: fail('readiness UI contains a third-party active runtime dependency')

    print('PASS: bootstrap and readiness contract')

if __name__ == '__main__': main()
