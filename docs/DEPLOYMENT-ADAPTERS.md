# Deployment adapters

AI Native CMS core deliberately stops before host configuration. Canonical authored state, schema, redirects, projection, and release packaging must work without assuming Apache, nginx, a CDN, a container platform, FTP, SSH, or a hosting provider.

Deployment adapters translate those portable outputs into host behavior. They are examples and contracts, not additional CMS authority.

## Redirect interception contract

Schema v8 projects canonical redirect authority into `__redirect-map.php` and routes requests through the database-free `__redirect.php` runtime. A deployment adapter that enables redirects must preserve these invariants:

1. Existing public files and directories are served normally before redirect fallback is considered.
2. An unresolved request is internally routed to `__redirect.php` or an equivalent adapter that consumes the same generated map semantics.
3. The original request path and query string remain observable to the redirect runtime.
4. `__redirect-map.php` is not directly exposed as a public asset.
5. The redirect path never connects to MySQL or promotes filesystem state into canonical SQL.
6. Unknown paths remain ordinary 404 responses; the adapter does not invent redirects.
7. 301/302/307/308 and query-preservation behavior remain controlled by the projected redirect record, not duplicated in web-server rules.

A host may implement an equivalent native redirect-table mechanism, but it must be generated from the same canonical projection and remain database-free at anonymous request time.

## Public cache policy

The Apache reference adapter mirrors the conservative policy proven in the production source site:

- public CSS and JavaScript: `public, max-age=86400, stale-while-revalidate=604800`;
- public images: `public, max-age=2592000, stale-while-revalidate=604800`;
- public JSON/XML/TXT discovery files: `public, max-age=3600, stale-while-revalidate=86400`;
- public HTML: `public, max-age=0, must-revalidate`.

These values are intentionally conservative because the public core does not require content-hashed asset filenames. An adopter with fingerprinted assets or a managed CDN may use a different cache strategy, but should preserve the distinction between durable static assets, frequently regenerated discovery files, and HTML that must revalidate.

### CMS operator assets are private state

Do **not** apply the public one-day CSS/JavaScript cache policy to `/cms/`. Operator HTML, JavaScript, and CSS can change together during a deployment; caching an old CMS script against new PHP markup/API behavior creates a stale control surface even when the public site is correct.

Apply this policy to the entire `/cms/` path, including static `.css` and `.js` files:

```text
Cache-Control: no-store, private
X-Robots-Tag: noindex, nofollow, noarchive, nosnippet
```

Dynamic CMS/API responses already emit private security/cache headers themselves. Static CMS assets require the host or deployment adapter to provide the equivalent transport rule.

The Apache reference includes `adapters/apache/cms.htaccess.example`, intended to be copied to `/cms/.htaccess` only when per-directory Apache configuration is supported. Other servers/CDNs should express the same path-scoped rule natively.

## Compression

The Apache public/private references apply DEFLATE to HTML, plain text, CSS, JavaScript, JSON, XML, and SVG when `mod_deflate` is available. Another host may use Brotli, gzip, or provider-native compression instead.

Compression is transport-only. It must not rewrite canonical/projected files on disk or change release-candidate hashes.

## Private and preview deployments

Private/preview deployments must not inherit public caching lifetimes. The Apache private reference uses:

```text
Cache-Control: no-store, private
```

while retaining safe response compression and redirect interception. Authentication/access-control rules remain adopter/deployment concerns and are intentionally not invented by this reference adapter.

## Apache reference files

- `adapters/apache/public.htaccess.example` — unresolved-path redirect fallback, generated-map denial, conservative public caching, and DEFLATE.
- `adapters/apache/private.htaccess.example` — the same redirect fallback and compression with global private `no-store` instead of public cache lifetimes.
- `adapters/apache/cms.htaccess.example` — path-local no-store/noindex policy for CMS HTML, JavaScript, and CSS so operator assets cannot remain stale across deployments.

Review and merge these snippets into existing host configuration; do not overwrite established security, canonical-host, PHP-handler, or provider-specific directives blindly.

## Other adapters

An nginx, Caddy, CDN/edge, container-ingress, or managed-host adapter should implement the same redirect and cache/compression contracts using native mechanisms. Such adapters should live under `adapters/<target>/` and include regression tests for their public/private cache separation, CMS no-store policy, and redirect-map handling.

No adapter may write production credentials or mutate canonical CMS state merely by existing in the source package.
