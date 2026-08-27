# AI Native CMS 0.1.0-rc.4

`0.1.0-rc.4` is the second public release candidate of AI Native CMS. It promotes the current development line to a schema-v10 fresh-install baseline while preserving explicit upgrades from the published schema-v8 `0.1.0-rc.3` release.

## What changed since rc.3

- **Live Page Composer** — managed pages can adopt reviewed repository structure into canonical typed compositions, edit page content and layout through one governed visual surface, and preserve canonical SQL authority through rebuilds.
- **Reusable Block Composer** — operators can design governed semantic blocks, save existing edited page blocks as independent reusable presets, and place those presets without browser-authored structural HTML or CSS becoming authority.
- **Clean managed routes** — stable internal managed page keys can project to reader-facing `/slug/` routes with deterministic rewriting of managed links, metadata, relocated HTML references, sitemaps, and other discovery output.
- **Audience lists and double opt-in** — schema v10 adds first-class Audience list definitions and subscription state, pending/confirmed/unsubscribed lifecycle, hashed confirmation tokens, authenticated list management, bounded CSV export, operator unsubscribe, and confirmation resend controls.
- **Transactional mail boundary** — SMTP with certificate verification and authenticated TLS/STARTTLS, deliberate host-local PHP `mail`, and development/CI log transport. Provider credentials remain private runtime configuration rather than canonical content.
- **cPanel email onboarding** — documentation and state-derived onboarding explain how to use the exact Secure SSL/TLS settings shown by cPanel, configure private `AINCMS_MAIL_*` values, send a test message, and inspect SPF/DKIM/DMARC through Email Deliverability.
- **Deterministic public discovery** — public HTML now projects a site-neutral `site-index.json`, XML/text sitemaps, compact `llms.txt`, optional synchronized `llms-full.txt`, and idempotent `rel="describedby"` discovery links. Discovery is derived from published public state and excludes CMS state, subscriber data, credentials, drafts, and non-public APIs.

## Schema and upgrades

Fresh `0.1.0-rc.4` installations initialize directly at **schema 10**.

Existing installations migrate explicitly rather than through bootstrap repair:

- schema 7 → `database/migrations/7-to-8.php --apply`
- schema 8 / `0.1.0-rc.3` → `database/migrations/8-to-9.php --apply`
- schema 9 → `database/migrations/9-to-10.php --apply`

A schema-v8 rc.3 installation therefore uses **8 → 9 → 10**. Back up both database and filesystem/code state and prove restore before applying migrations.

The 8→9 migration converts the earlier page-block-template authority into `block_presets` and rewrites composition references. The 9→10 migration establishes generic Audience authority and, when the earlier subscription primitive is present, preserves its rows into disabled Audience lists before archiving the legacy table.

## Release assurance

The rc.4 publication candidate is required to pass the cumulative repository contract and a packaged-candidate rehearsal on the exact reviewed head. The rehearsal covers deterministic artifact reproduction, a clean schema-v10 installation, authenticated onboarding/readiness, canonical mutation and projection, discovery output, paired recovery, the published rc.3 schema-v8 → v9 composition upgrade, and the v9 → v10 Audience/mail upgrade and double-opt-in lifecycle.

## Requirements

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`

Python 3.10+ and Node are used only for repository/release validation.

Start with `README.md` and `docs/INSTALLATION.md`. For governed agent-assisted iteration, read `AGENTS.md` and `docs/LLM-COLLABORATION.md`.

## License

AI Native CMS remains **source-available**, not OSI-approved open source. It is licensed under the Apache License 2.0 subject to the Commons Clause License Condition v1.0. See `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` for the binding terms.

## Prerelease status

This remains a prerelease. It is intended for evaluation, new site builds, and governed production trials with tested backups and explicit review of migrations, deployment adapters, and provider configuration before consequential changes.
