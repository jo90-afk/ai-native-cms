from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

routes = (ROOT / 'api/page-routes.php').read_text()
projection = (ROOT / 'api/page-projection.php').read_text()
rebuild = (ROOT / 'api/content-rebuild.php').read_text()
navigation = (ROOT / 'api/navigation.php').read_text()
composer = (ROOT / 'cms/composer.php').read_text()
composer_routes = (ROOT / 'cms/clean-routes-ui.js').read_text()
config = (ROOT / 'config/site.example.php').read_text()
apache = (ROOT / 'adapters/apache/public.htaccess.example').read_text()

required_routes = [
    'pageRoutesEnabled',
    'pageRouteNormalizeKey',
    'pageRouteKeyToRoute',
    'pageRouteProjectionRelativePath',
    'pageRouteManagedKeySet',
    'pageRoutePathToManagedKey',
    'pageRouteRewriteManagedUrl',
    'Managed page keys collide on public route',
]
for token in required_routes:
    assert token in routes, f'missing route invariant: {token}'

required_projection = [
    'pageProjectionProjectManagedCleanRoutes',
    'pageProjectionRewriteManagedReferences',
    'pageProjectionRewriteMetadata',
    'pageProjectionCanonicalizeSitemapXml',
    'presentationPublicHtmlFiles',
    "['sitemap.xml','sitemap.txt','llms.txt','llms-full.txt','feed.xml']",
]
for token in required_projection:
    assert token in projection, f'missing projection invariant: {token}'

assert "pageRoutesEnabled()?pageProjectionProjectManagedCleanRoutes" in rebuild
assert "array_keys(contentAuthorityManagedPages($root))" in rebuild
assert "require_once __DIR__.'/page-routes.php'" in navigation
assert "pageRouteManagedKeySet(array_keys(cmsManagedPages($root)))" in navigation
assert "'clean_managed_routes' => true" in config
assert 'JavaScript-created' in config and 'root-relative' in config
assert 'RewriteCond %{DOCUMENT_ROOT}/$1/index.html -f' in apache
assert 'RewriteRule ^(.+)\\.html$ /$1/ [R=301,L,NE]' in apache

assert '>Route<input id="new-path" placeholder="services"' in composer
assert 'Publishes at /services/.' in composer
assert '/cms/clean-routes-ui.js' in composer
assert 'routeForKey' in composer_routes
assert 'queueMicrotask' in composer_routes
assert "routeInput.value=`${slug}.html`" in composer_routes
assert '#composer-pages button[data-path] span' in composer_routes

for text in (routes, projection, composer_routes):
    lowered = text.lower()
    assert 'judeoneill' not in lowered
    assert 'lattice' not in lowered

print('clean managed route contract ok')
