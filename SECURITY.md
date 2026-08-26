# Security

AI Native CMS treats the administration surface as a private application even when the generated site is public.

## Baseline guarantees

- Production CMS access requires HTTPS.
- CMS sessions use strict cookie settings and bounded idle/absolute lifetimes.
- State-changing browser requests require an authenticated owner session, exact same-origin evidence, and a session CSRF token.
- JSON request bodies are content-type checked and size bounded.
- Authentication and mutation surfaces are intended to be rate limited.
- PDO uses native prepares with emulation disabled and multi-statement execution disabled when available.
- Non-local MySQL transport requires verified TLS by default unless an operator explicitly accepts an insecure private-network exception.
- Private configuration files are refused when they resolve inside the public site root.
- Runtime errors return incident identifiers rather than raw exception details in production.
- Anonymous public delivery remains static-first and does not expose a database read path.

## Secrets

Do not commit populated INI files, database credentials, password hashes intended for a real deployment, rate-limit secrets, mail credentials, or hosting tokens. Use environment variables or a private INI file outside the public document root.

## Public-release sanitization

`tests/public_release_contract.py` prevents known reference-site identifiers and the legacy site-specific environment prefix from entering release code. The contract is intentionally additive: new adopter-specific identifiers discovered during extraction should be added to the deny list until the corresponding seam is generic.

## Reporting

This repository is pre-release. Until a dedicated disclosure channel is configured, use a private repository/security communication path rather than opening a public issue containing exploit details or secrets.
