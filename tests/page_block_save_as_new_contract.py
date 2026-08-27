#!/usr/bin/env python3
from __future__ import annotations
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]

def fail(message:str)->None:
    print(f"FAIL: {message}")
    raise SystemExit(1)

def text(path:str)->str:
    file=ROOT/path
    if not file.is_file(): fail(f"missing M-017 file: {path}")
    return file.read_text(encoding='utf-8')

def require(source:str,needles:list[str],label:str)->None:
    missing=[needle for needle in needles if needle not in source]
    if missing: fail(f"{label} is missing: {', '.join(missing)}")

def main()->None:
    values=text('api/composition-values.php')
    presets=text('api/block-presets.php')
    api=text('api/cms-composer.php')
    ui=text('cms/page-block-save-as-new.js')
    markup=text('cms/composer.php')

    require(values,[
        'compositionTypedSnapshotValues',
        'composerNormalizeValues',
        'compositionValueRenderedCmsId',
        'composerPrimitiveCmsId',
        'Page block editable fields changed since selection',
        'Page block image fields changed since selection',
        'Page block link fields changed since selection',
    ],'typed instance snapshot normalization')

    require(presets,[
        'blockPresetSaveAsNew',
        'composerPrimitiveApplyValues',
        'blockPresetSavePrimitive($root,null',
        "preset_kind,html_content",
        "'legacy'",
        'Saved as new from preset',
    ],'new preset derivation')
    if 'UPDATE block_presets SET' in presets.split('function blockPresetSaveAsNew',1)[1].split('function blockPresetUpdateMetadata',1)[0]:
        fail('save-as-new mutates an existing preset instead of creating a new one')

    require(api,[
        "requireCmsAuth(true)",
        'requireSameOrigin(true)',
        'requireCmsCsrf()',
        "enforceRateLimit('cms-composer'",
        "$action==='saveAsNewPreset'",
        'compositionPayload($root,$path)',
        'compositionTypedSnapshotValues',
        'blockPresetSaveAsNew',
        "cmsAudit('composer','Saved page block as new preset'",
    ],'guarded Page Composer save-as-new action')
    for forbidden in ["$payload['html']", '$payload["html"]', "['outerHTML']", '["outerHTML"]']:
        if forbidden in api: fail('save-as-new endpoint accepts browser structural HTML')

    require(ui,[
        'Save as new block',
        "querySelectorAll('[data-cms-id]')",
        "querySelectorAll('img')",
        "querySelectorAll('a')",
        'leafValues',
        'mediaValues',
        'linkValues',
        "action:'saveAsNewPreset'",
    ],'Page Composer save-as-new client')
    if 'outerHTML' in ui or 'document.write' in ui or 'insertAdjacentHTML' in ui:
        fail('save-as-new client serializes or injects structural HTML')
    if 'http://' in ui or 'https://' in ui:
        fail('save-as-new client contains a third-party active runtime dependency')

    require(markup,[
        'id="save-block-as-new"',
        'id="save-block-as-new-dialog"',
        'page-block-save-as-new.js',
        'The source preset and this page instance stay unchanged.',
    ],'Page Composer save-as-new affordance')

    print('PASS: typed page-block save-as-new contract')

if __name__=='__main__': main()
