# Clean managed-page routes

AI Native CMS can publish CMS-managed repository/database page keys at clean public URLs without changing their canonical internal identity.

## Authority boundary

A key such as `about.html` remains the page identifier used by repository source, canonical SQL records, hierarchy, composition, reconciliation, and guarded CMS APIs. When `projection.clean_managed_routes` is enabled, public finalization additionally writes `about/index.html`, whose reader-facing route is `/about/`.

That directory file is a deterministic projection. It is never imported back as canonical content and anonymous requests do not query MySQL to resolve it.

Only the authoritative managed-page set is projected. Unmanaged HTML files keep their existing location and behavior.

## Configuration

New installs created from `config/site.example.php` enable:

```php
'projection' => [
    'clean_managed_routes' => true,
    // ...
],
```

The code default is `false`. Existing adopter configurations that do not contain this key therefore preserve their established flat `*.html` routes until the operator explicitly opts in.

## Projection behavior

The final route pass:

- maps `index.html` to `/`;
- maps `about.html` to `/about/`;
- supports bounded nested keys such as `docs/start.html` → `/docs/start/`;
- rejects two managed keys that would collide on the same public route;
- rewrites links/actions that target managed pages;
- rebases HTML `href`, `src`, `action`, `poster`, and `srcset` values when a page is relocated beneath a directory;
- rewrites same-site managed canonical/discovery references where those generated files exist;
- preserves external URLs and non-managed destinations;
- preserves query strings and fragments.

The route pass runs after the rest of public finalization so it consumes the final projected page rather than competing with SEO, navigation, branding, redirects, or adopter hooks.

## JavaScript-created runtime URLs

The projector parses rendered HTML, not JavaScript program behavior. A script that constructs a document-relative request at runtime—for example `fetch('api/action.php')` or `img.src = 'assets/image.webp'`—will resolve beneath the clean page route when the browser is at `/about/`.

Pages that may be clean-routed should therefore use root-relative same-site runtime destinations such as `/api/action.php` and `/assets/image.webp`, or another deliberate absolute/base-aware strategy. Do not add page-specific compensatory copies under every clean-route directory.

## Web-server adapter

Clean page files can be served by any static-capable host. Canonical redirects from explicit legacy `*.html` requests are transport behavior and belong in the deployment adapter.

The Apache example redirects `about.html` to `/about/` only if `about/index.html` exists. This keeps unmanaged HTML routes intact and prevents the web-server layer from defining a second managed-page registry.

Other hosts/CDNs should implement equivalent conditional canonicalization if legacy-route redirects are desired.

## Page Composer and navigation

The CMS continues to address pages by their canonical internal key. Navigation emits clean destinations when the feature is enabled and resolves those destinations back to canonical keys for active-page state. Page Composer therefore does not acquire a second page identity or mutation contract merely because the reader-facing route is cleaner.

## Rollback

Disable `projection.clean_managed_routes`, rebuild public projections, and remove or ignore previously generated clean-route directories according to the adopter's deployment process. No database migration or canonical-state reversal is required.
