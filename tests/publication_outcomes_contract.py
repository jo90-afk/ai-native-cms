from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def text(path: str) -> str:
    return (ROOT / path).read_text(encoding='utf-8')

def require(haystack: str, needle: str, message: str) -> None:
    if needle not in haystack:
        raise SystemExit(message)

lock = text('api/publication-lock.php')
for needle in [
    "AINCMS_PUBLICATION_STATE_ROOT",
    "class PublicationOutcome",
    "class PublicationLock",
    "public-update-pending",
    "cms-publication",
    "runtimePublicationError",
    "requireCmsPublicationLock",
]:
    require(lock, needle, f'publication boundary missing {needle}')

rebuild = text('api/content-rebuild.php')
require(rebuild, "require_once __DIR__.'/publication-lock.php';", 'rebuild does not load publication boundary')
require(rebuild, 'PublicationOutcome::published();', 'successful final projection does not clear saved outcome')

projecting = {
    'api/cms-branding.php': "PublicationOutcome::saved($root,['kind'=>'branding'])",
    'api/cms-navigation.php': "PublicationOutcome::saved($root,['kind'=>'navigation'])",
    'api/cms-seo.php': "PublicationOutcome::saved($root,['kind'=>'seo'",
    'api/cms-writing.php': "PublicationOutcome::saved($root,['kind'=>'writing'",
    'api/cms-composer.php': "PublicationOutcome::saved($root,['kind'=>'page-composition'",
    'api/cms-redirects.php': "PublicationOutcome::saved($root,['kind'=>'redirect-save'",
}
for path, saved in projecting.items():
    body = text(path)
    require(body, 'requireCmsPublicationLock($root)', f'{path} can mutate without publication lock')
    require(body, saved, f'{path} does not mark canonical persistence before publication')
    require(body, 'runtimePublicationError(', f'{path} does not distinguish saved from published failure')

for path in ['api/cms-blocks.php', 'api/cms-media.php']:
    body = text(path)
    require(body, 'requireCmsPublicationLock($root)', f'{path} can write during pending publication recovery')

redirects = text('api/cms-redirects.php')
require(redirects, 'contentFinalizePublicProjections($root)', 'redirect mutations bypass shared public finalization')

print('PASS: generic CMS writers coordinate canonical saves with publication completion and pending recovery.')
