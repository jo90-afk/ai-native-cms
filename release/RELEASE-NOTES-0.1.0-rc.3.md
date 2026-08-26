# AI Native CMS 0.1.0-rc.3

This is the first public release candidate of AI Native CMS: a static-first PHP/MySQL publishing system designed for human and LLM-assisted iteration without allowing generated output or conversation history to become a second source of truth.

## What is included

- neutral Home, About, and Writing starter site;
- resumable authenticated onboarding derived from real repository/database/readiness state;
- canonical MySQL page content, compositions, posts, media metadata, navigation, branding, SEO, and redirects;
- repository-owned templates, structure, CSS/JavaScript behavior, schema, migrations, tests, documentation, and public adopter configuration;
- site-wide SEO quality auditing and deterministic social/schema enhancement;
- static, database-free anonymous delivery and redirect routing;
- explicit schema 7→8 migration and CLI-only fresh-install bootstrap;
- readiness checks, backup/rollback guidance, and reference Apache deployment adapters;
- `AGENTS.md` plus practical LLM collaboration guidance for design, content, features, bugs, schema changes, and releases;
- deterministic ZIP/manifest/SHA256 release artifacts with exact source provenance and residue scanning.

## Release assurance

The rc.3 line completed a clean release rehearsal from the packaged candidate itself using a fresh MySQL 8 database. The rehearsal covered site initialization, owner/schema bootstrap, repository reconciliation, authenticated onboarding, green readiness, a representative agent-governed repository change, canonical content mutation and projection, redirect generation, and paired filesystem/database recovery.

## Requirements

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`

Python 3.10+ and Node are used only for repository/release validation.

Start with `README.md` and `docs/INSTALLATION.md`. For agent-assisted development, read `AGENTS.md` and `docs/LLM-COLLABORATION.md` first.

## License

AI Native CMS is **source-available**, not OSI-approved open source. It is licensed under the Apache License 2.0 subject to the Commons Clause License Condition v1.0. Use, modification, derivative works, attribution-preserving redistribution, and commercial use are permitted subject to the restriction on selling the CMS itself or a product/service whose value derives entirely or substantially from the CMS functionality.

See `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` for the binding terms and attribution notice.

## Release-candidate status

This is a prerelease. The project is suitable for evaluation, site builds, and governed iteration, but adopters should keep tested backups and review migrations/deployment changes before production use.
