# Security

AI Native CMS treats the administration surface as a private application even when generated public output is served anonymously.

## Baseline guarantees

- Production CMS access requires HTTPS.
- CMS sessions use strict cookie settings and bounded idle/absolute lifetimes.
- State-changing browser requests require an authenticated owner session, exact same-origin evidence, and a session CSRF token.
- JSON request bodies are content-type checked and size bounded.
- Authentication and mutation surfaces are rate limited.
- PDO uses native prepares with emulation disabled and multi-statement execution disabled when available.
- Non-local MySQL transport requires verified TLS by default unless an operator explicitly accepts an insecure private-network exception.
- Private configuration files are refused when they resolve inside the public site root.
- Runtime errors return incident identifiers rather than raw exception details in production.
- Anonymous public delivery remains static-first and does not expose a database read path.
- Canonical CMS mutations require the current schema before writing; browser endpoints do not opportunistically migrate schema or reconcile repository source into SQL.
- Canonical redirects are projected to static runtime data; anonymous redirect handling does not query MySQL.
- Redirect sources/targets are bounded to safe same-site paths and validated for reserved paths, ambiguous encoded separators, control characters, dot segments, public-file collisions, conflicting authorities, self-resolution, and cycles before persistence/projection.
- Portable readiness diagnostics are observational and do not initialize, migrate, publish, mail, deploy, or expose secret/grant values.
- Database bootstrap initializes schema plus the first owner only; it does not seed adopter content, overwrite existing owner credentials, or silently migrate older schemas.

## Secrets

Do not commit populated INI files, database credentials, deployment password hashes, rate-limit secrets, mail credentials, hosting tokens, private keys, or database backups. Use environment variables or a private INI file outside the public document root.

`database/private-config.example.ini` contains placeholders only and is the sole INI file intentionally eligible for the source release candidate.

Reference deployment adapters contain no credentials or access-control secrets. They are transport examples only; an adopter must merge them with existing host/security rules deliberately.

## Public-release sanitization

`tests/public_release_contract.py` prevents known reference-site identifiers and the legacy site-specific environment prefix from entering release code. The contract is intentionally additive: new adopter-specific identifiers discovered during extraction should be denied until the corresponding seam is generic.

Release/example text scanning includes `.example` deployment configuration files, so reference `.htaccess.example` files cannot bypass the same known-identifier residue guard merely because of their filename suffix.

`tools/build_release.py` and `tests/release_candidate_contract.py` enforce a second artifact boundary. The internal source candidate excludes `.git`, `.github`, `.lattice`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, and populated INI files. The builder also fails closed on symlinks, known adopter identifiers, obvious private-key/token patterns, and version/schema metadata disagreement.

The generated candidate contains an embedded file manifest with per-file SHA256 hashes plus an outer SHA256 for the complete deterministic ZIP.

## Redirect runtime and deployment adapters

`__redirect-map.php` is generated executable data for the database-free redirect runtime, not a public content asset. A deployment adapter must prevent direct public access to the map while preserving internal access from `__redirect.php`.

Reference Apache adapters serve existing files/directories before redirect fallback and route only unresolved requests to the static redirect runtime. Public caching rules are deliberately conservative for versionless assets; private/preview reference configuration uses `Cache-Control: no-store, private` and must not inherit public max-age values.

Transport compression changes bytes on the wire only. It must not mutate projected files or release-candidate hashes.

## Release status

`0.1.0-rc.2` is an internal schema-v8 release-candidate identity only. It is not a public release. No license has been selected, no public distribution is authorized, and CI workflow artifacts are temporary private review artifacts rather than publication channels.

Repository visibility, license selection, tags/releases, package publication, and production deployment/adoption require separate explicit decisions.

## Reporting

This repository is pre-release. Until a dedicated disclosure channel is configured, use a private repository/security communication path rather than opening a public issue containing exploit details, credentials, or secrets.
