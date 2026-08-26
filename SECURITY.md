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
- Portable readiness diagnostics are observational and do not initialize, migrate, publish, mail, deploy, or expose secret/grant values.
- Database bootstrap initializes schema plus the first owner only; it does not seed adopter content, overwrite existing owner credentials, or silently migrate older schemas.

## Secrets

Do not commit populated INI files, database credentials, deployment password hashes, rate-limit secrets, mail credentials, hosting tokens, private keys, or database backups. Use environment variables or a private INI file outside the public document root.

`database/private-config.example.ini` contains placeholders only and is the sole INI file intentionally eligible for the source release candidate.

## Public-release sanitization

`tests/public_release_contract.py` prevents known reference-site identifiers and the legacy site-specific environment prefix from entering release code. The contract is intentionally additive: new adopter-specific identifiers discovered during extraction should be denied until the corresponding seam is generic.

M-008 adds a second artifact boundary in `tools/build_release.py` and `tests/release_candidate_contract.py`. The internal source candidate excludes `.git`, `.github`, `.lattice`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, and populated INI files. The builder also fails closed on symlinks, known adopter identifiers, obvious private-key/token patterns, and version/schema metadata disagreement.

The generated candidate contains an embedded file manifest with per-file SHA256 hashes plus an outer SHA256 for the complete deterministic ZIP.

## Release status

`0.1.0-rc.1` is an internal release-candidate identity only. It is not a public release. No license has been selected, no public distribution is authorized, and CI workflow artifacts are temporary private review artifacts rather than publication channels.

Repository visibility, license selection, tags/releases, package publication, and production deployment require separate explicit decisions.

## Reporting

This repository is pre-release. Until a dedicated disclosure channel is configured, use a private repository/security communication path rather than opening a public issue containing exploit details, credentials, or secrets.
