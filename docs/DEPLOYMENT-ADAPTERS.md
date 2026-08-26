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

- CSS and JavaScript: `public, max-age=86400, stale-while-revalidate=604800`;
- images: `public, max-age=2592000, stale-while-revalidate=604800`;
- JSON/XML/TXT discovery files: `public, max-age=3600, stale-while-revalidate=86400`;
- HTML: `public, max-age=0, must-revalidate`.

These values are intentionally conservative because the public core does not require content-hashed asset filenames. An adopter with fingerprinted assets or a managed CDN may use a different cache strategy, but should preserve the distinction between durable static assets, frequently regenerated discovery files, and HTML that must revalidate.

The core does not emit cache headers for ordinary public static files because static-file transport belongs to the host. Dynamic CMS/API responses continue to own their own private security/cache headers.

## Compression

The Apache reference applies DEFLATE to HTML, plain text, CSS, JavaScript, JSON, XML, and SVG when `mod_deflate` is available. Another host may use Brotli, gzip, or provider-native compression instead.

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

Review and merge these snippets into an existing `.htaccess`; do not overwrite established security, canonical-host, PHP-handler, or provider-specific directives blindly.

## Other adapters

An nginx, Caddy, CDN/edge, container-ingress, or managed-host adapter should implement the same redirect and cache/compression contracts using native mechanisms. Such adapters should live under `adapters/<target>/` and include regression tests for their public/private cache separation and redirect-map handling.

No adapter may change repository visibility, choose a license, create a public release, deploy automatically, or write production credentials merely by existing in the source package.
