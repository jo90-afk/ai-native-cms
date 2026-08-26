# AI Native CMS

AI Native CMS is a static-first PHP/MySQL publishing system designed for human and LLM-assisted iteration without turning generated files or conversation history into a second source of truth.

MySQL owns accepted authored state. Git owns application code, structural templates, migrations, tests, documentation, deployment adapters, and adopter-owned public configuration. Authenticated humans and agents work through the same guarded contracts, while public HTML/JSON/XML and redirect routing data are deterministic projections. Anonymous readers do not require a database connection.

## Public release candidate

The current release is **`0.1.0-rc.3`**, schema version **8**, tagged **`v0.1.0-rc.3`**.

This is a public prerelease intended for evaluation, site builds, and governed iteration. Keep tested backups and review migrations/deployment changes before production use.

AI Native CMS is **source-available**, not OSI-approved open source. It is licensed under **Apache License 2.0 subject to Commons Clause License Condition v1.0**. Use, modification, derivative works, attribution-preserving redistribution, and commercial use are permitted subject to the restriction on selling AI Native CMS itself or a product/service whose value derives entirely or substantially from AI Native CMS functionality.

See `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` for the binding terms and attribution notice.

## Why “AI native”

An AI agent should be able to help redesign a site, revise content, add a feature, diagnose a bug, or prepare a release without bypassing the CMS governance model.

That means:

- repository-owned structure and code change through branches and reviewable diffs;
- accepted authored content/state remains canonical in MySQL;
- generated public output is evidence, never hidden reverse authority;
- schema changes require explicit migrations;
- human and agent writers share authentication, validation, revision, expected-hash, provenance, and projection rules;
- durable decisions live in code/tests/docs/project state rather than only in a chat thread.

Start with `AGENTS.md` and `docs/LLM-COLLABORATION.md` when using a coding or content agent.

## Start with a real site

The release ships a neutral starter site:

- `index.html` — Home;
- `about.html` — About;
- `writing.html` — Writing index;
- `assets/styles.css` — responsive starter design system;
- `assets/site.js` — database-free writing-index rendering;
- `templates/article.html` — long-form article shell.

Create adopter-owned **public, non-secret** configuration with:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
```

The initializer writes only `config/site.php`. It never asks for, reads, or writes database credentials or other secrets.

Then configure private runtime secrets outside the public root, bootstrap the database/first owner, and explicitly initialize repository content:

```bash
php database/bootstrap.php
php database/reconcile.php initial-import
php database/readiness.php
```

Open `/cms/` over HTTPS. An unfinished site lands in the resumable **Onboarding** workspace, which derives progress from actual configuration, database, canonical-content, branding/navigation, publication, and readiness state. There is no separate “wizard complete” flag.

See `docs/INSTALLATION.md` for the complete fresh-install and upgrade path.

## CMS workspaces

The first-party `/cms/` surface includes:

- **Onboarding** — resumable first-run/site-build guidance derived from durable state;
- **Pages** — bounded editing of canonical text leaves;
- **Composer** — trusted-template page composition and new managed pages;
- **Media** — first-party asset catalog and bounded raster uploads;
- **Navigation** — canonical primary navigation;
- **Branding** — site identity plus adopter-declared bounded CSS tokens;
- **Writing** — Markdown long-form publishing with revisions and static projection;
- **Search + Social** — canonical SEO controls plus site-wide quality findings;
- **Redirects** — governed same-site redirect authority and slug history;
- **Readiness** — authenticated read-only deployment/operability evidence.

There is no frontend framework or third-party active runtime dependency in the CMS UI.

## Authority model

### Git / repository authority

Repository files own:

- PHP/JavaScript application behavior;
- structural page shells and trusted templates;
- CSS and responsive/accessibility behavior;
- schema and explicit migrations;
- tests/contracts;
- documentation and `AGENTS.md`;
- deployment adapters;
- adopter-owned `config/site.php` when it contains only public structure.

### Canonical MySQL authority

Accepted authored state includes:

- page content leaves;
- typed page compositions;
- posts and revisions;
- media metadata;
- primary navigation;
- bounded branding values;
- SEO overrides;
- redirect records.

### Generated public projection

HTML, JSON, XML, indexes, and redirect maps are deterministic outputs. Fix their owning repository/canonical source rather than editing generated symptoms and treating them as durable state.

### Host/provider state

Credentials, web-server rewrites, caching, document-root configuration, deployment mechanisms, and host-specific diagnostics remain deployment/operator concerns behind bounded adapters.

## Core capabilities

The reusable core includes:

- hardened PHP/MySQL owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-v8 canonical content, revisions, provenance, composition, media, navigation, branding, SEO, and redirects;
- repository-owned page-source lineage with three-way reconciliation that preserves newer canonical edits;
- trusted structural templates exposing only bounded rich-text, safe-link, and media values;
- canonical typed compositions and CMS-created root-level pages with validated parent hierarchy;
- bounded raster uploads and canonical media metadata;
- long-form Markdown publishing with immutable prior snapshots, stale-write rejection, restore, draft/published projection, and slug-history redirects;
- site-wide SEO quality auditing plus deterministic inherited social/schema enhancement without creating a second SEO authority;
- redirect graph validation, global graph-write serialization, file/directory collision rejection, and database-free static redirect routing;
- deterministic site-wide finalization with bounded adapter hooks;
- CLI-only schema/first-owner bootstrap and explicit schema migrations;
- authenticated GET-only/CLI readiness with provider adapter seams;
- deterministic release packaging with exact source provenance and residue scanning.

## Repository → hosting operations

`docs/REPOSITORY-OPERATIONS.md` covers:

- what belongs in Git vs canonical MySQL vs generated output vs host state;
- branch/pull-request workflow;
- SSH pull-to-host deployments;
- reviewed artifact/SFTP/provider-copy deployments;
- secret placement;
- database backups and migrations;
- host-side hotfix recovery;
- readiness and rollback;
- provider capability checks.

A Git deployment updates repository-owned behavior. It does **not** reset canonical CMS content from Git on every deploy.

## Working with an LLM

`AGENTS.md` is the repository-level contract an agent should read before changing the project. `docs/LLM-COLLABORATION.md` provides practical request patterns for interface/design iteration, content revision, features, bug fixes, schema/migration work, and releases.

The central rule: an LLM can inspect, propose, implement, test, and explain changes, but it does not become a new authority layer.

For repository work, use a branch/PR and verification. For canonical content, use the CMS/store contract. For generated-output problems, fix the owning source. For provider behavior, use an adapter. Do not hand an agent production secrets merely to make repository changes.

## Publishing and public delivery

`posts` is canonical. Published posts materialize static article HTML and a public JSON writing index. Drafts have no public article projection. The starter `writing.html` reads only that static index, so anonymous browsing remains database-free.

SEO is canonical in `seo_overrides`; custom canonicals stay on the configured public origin. Site-wide audit findings are read-only observations. Deterministic projection may fill inherited social/schema defaults without overwriting page-specific canonical SEO.

Primary navigation projects only into `<nav id="site-nav">`. Branding controls only identity text and CSS custom properties explicitly exposed by adopter configuration.

Manual redirects are canonical in `redirect_records`. Read-only system aliases come from repository configuration. Published slug changes enter redirect authority automatically. `__redirect.php` consumes a generated static map and never opens MySQL.

## Deployment adapters

`docs/DEPLOYMENT-ADAPTERS.md` defines the provider-neutral transport contract. Included Apache examples demonstrate:

- serve existing files/directories first;
- route unresolved requests to the database-free redirect runtime;
- deny direct map access;
- conservative public cache lifetimes and compression;
- private/preview `Cache-Control: no-store, private`.

They are examples, not automatic deployment behavior or CMS authority.

## Requirements and migration

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`

Python 3.10+ and Node are used only for repository/release validation.

An existing schema-7 installation must use the explicit migration after a verified backup/restore test:

```bash
php database/migrations/7-to-8.php --apply
```

`database/bootstrap.php --repair` is not a migration path.

## Release assurance

The rc.3 line completed a clean release rehearsal from the packaged candidate itself using a fresh MySQL 8 database. The rehearsal covered deterministic double-builds, non-secret site initialization, schema/owner bootstrap, repository reconciliation, authenticated onboarding, zero-blocker readiness, governed repository/agent changes, canonical content mutation and projection, static redirect routing, and paired filesystem/database recovery.

Build a deterministic artifact from an exact source revision with:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The public prerelease contains the ZIP, manifest, and SHA256 checksum for `v0.1.0-rc.3`. See `docs/RELEASE.md` for the exact publication contract.

## Documentation

- `docs/ARCHITECTURE.md` — system and authority model
- `docs/INSTALLATION.md` — fresh install, onboarding, migrations, backup, rollback
- `docs/REPOSITORY-OPERATIONS.md` — GitHub and hosting operations
- `docs/LLM-COLLABORATION.md` — governed agent-assisted iteration
- `docs/DEPLOYMENT-ADAPTERS.md` — host transport/interception contract
- `docs/UPSTREAMING.md` — moving reusable features between adopter/source repos
- `docs/EXTRACTION-MATRIX.md` — reusable-core/product frontier
- `docs/RELEASE.md` — public release-candidate contract
- `SECURITY.md` — security boundaries
