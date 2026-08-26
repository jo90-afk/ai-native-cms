# AI Native CMS

AI Native CMS is a static-first publishing system designed for human and LLM-assisted iteration without turning generated files or conversation history into a second source of truth.

MySQL owns accepted authored state. Git owns application code, structural templates, migrations, tests, documentation, and adopter-owned public configuration. Authenticated humans and agents work through the same guarded contracts, and public HTML/JSON/XML plus redirect routing data are deterministic projections. Anonymous readers do not require a database connection.

## Why “AI native”

An AI agent should be able to help redesign a site, revise content, add a feature, diagnose a bug, or prepare a release without bypassing the CMS governance model.

That means:

- repository-owned structure and code change through branches and reviewable diffs;
- accepted authored content/state remains canonical in MySQL;
- generated public output is inspected as evidence, never promoted into hidden reverse authority;
- schema changes require explicit migrations;
- human and agent writers share the same authentication, validation, revision, expected-hash, provenance, and projection rules;
- important decisions live in code/tests/docs/project state rather than only in one chat thread.

Start with `AGENTS.md` and `docs/LLM-COLLABORATION.md` when using a coding or content agent.

## Current candidate

The current internal candidate line is **`0.1.0-rc.3`**, schema version **8**.

rc.3 is licensed but remains private and unpublished. The selected terms are **Apache License 2.0 subject to the Commons Clause License Condition v1.0**. This is a **source-available** license, not an OSI-approved open-source license. It allows use, modification, derivative works, and commercial use while withholding the right to sell AI Native CMS itself, or a product/service whose value derives entirely or substantially from the CMS functionality. Attribution/license notices must be retained according to the terms.

The binding files are `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE`.

Repository visibility, a Git tag/GitHub Release, public package publication, and production deployment remain separate release decisions.

## Start with a real site, not an empty framework

rc.3 ships a neutral starter site:

- `index.html` — Home;
- `about.html` — About;
- `writing.html` — Writing index;
- `assets/styles.css` — responsive starter design system;
- `assets/site.js` — database-free writing-index rendering;
- `templates/article.html` — long-form article shell.

The starter is intentionally simple enough to replace, but complete enough to browse immediately and rich enough to seed the Composer template library.

Create adopter-owned **public, non-secret** configuration with:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
```

The initializer writes only `config/site.php`. It never asks for, reads, or writes database credentials or other secrets.

Then configure private runtime secrets outside the public root, bootstrap the database/first owner, and explicitly initialize repository content:

```bash
php database/bootstrap.php
php database/reconcile.php initial-import
```

Open `/cms/` over HTTPS. An unfinished site lands in the resumable **Onboarding** workspace, which derives progress from actual configuration, database, canonical-content, branding/navigation, publication, and readiness state. There is no separate “wizard complete” flag.

Onboarding guides the adopter through:

1. site identity and public repository configuration;
2. starter-site integrity;
3. secure database/owner bootstrap;
4. canonical content initialization;
5. starter page editing and additional page composition;
6. branding and navigation;
7. optional first writing publication;
8. final readiness evidence.

Each step hands off to the existing guarded workspace or CLI boundary that owns the state. Browser onboarding never writes credentials, runs migrations, deploys, or promotes repository source into canonical SQL.

See `docs/INSTALLATION.md` for the full first-run and upgrade path.

## CMS surface

The first-party `/cms/` workspaces cover:

- **Onboarding** — resumable first-run/site-build guidance derived from durable state;
- **Pages** — bounded editing of canonical text leaves;
- **Composer** — trusted-template page composition and new managed pages;
- **Media** — first-party asset catalog and bounded raster uploads;
- **Navigation** — canonical primary navigation;
- **Branding** — site identity plus adopter-declared bounded CSS tokens;
- **Writing** — Markdown long-form publishing with revisions and static projection;
- **SEO** — canonical metadata/discovery controls;
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

HTML, JSON, XML, indexes, and redirect maps are deterministic outputs. Fix their owning repository/canonical source rather than editing the generated symptom and calling it durable.

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
- canonical SEO, navigation, branding, and redirect authority;
- redirect graph validation, global graph-write serialization, file/directory collision rejection, and database-free static redirect routing;
- deterministic site-wide finalization with bounded `after_pages`, `after_seo`, and final adapter hooks;
- CLI-only schema/first-owner bootstrap and explicit schema migrations;
- authenticated GET-only/CLI readiness with provider adapter seams;
- deterministic internal release-candidate packaging with exact source provenance and residue scanning.

## Repository → hosting operations

`docs/REPOSITORY-OPERATIONS.md` is the operator guide for managing a site as a GitHub repository and deploying it without collapsing source boundaries.

It covers:

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

`AGENTS.md` is the repository-level contract an agent should read before changing the project. `docs/LLM-COLLABORATION.md` provides practical request patterns for:

- interface/design iteration;
- content revision;
- feature development;
- bug fixes;
- schema/migration work;
- release preparation.

The central rule is simple: an LLM can inspect, propose, implement, test, and explain changes, but it does not become a new authority layer.

For repository work, use a branch/PR and verification. For canonical content, use the CMS/store contract. For generated-output problems, fix the owning source. For provider behavior, use an adapter. Do not hand an agent production secrets merely to make repository changes.

## Publishing and public delivery

`posts` is canonical. Published posts materialize static article HTML and a public JSON writing index. Drafts have no public article projection. The starter `writing.html` reads only that static index, so anonymous browsing stays database-free.

SEO is canonical in `seo_overrides`; custom canonicals stay on the configured public origin. Primary navigation projects only into `<nav id="site-nav">`. Branding controls only identity text and CSS custom properties explicitly exposed by adopter configuration.

Manual redirects are canonical in `redirect_records`. Read-only system aliases come from repository configuration. Published slug changes enter redirect authority automatically. `__redirect.php` consumes a generated static map and never opens MySQL.

## Deployment adapters

`docs/DEPLOYMENT-ADAPTERS.md` defines the provider-neutral transport contract. The included Apache examples demonstrate:

- serve existing files/directories first;
- route unresolved requests to the database-free redirect runtime;
- deny direct map access;
- conservative public cache lifetimes and compression;
- private/preview `Cache-Control: no-store, private`.

They are examples, not automatic deployment behavior or CMS authority.

## Installation, migration, and readiness

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ only for repository/release validation

Node is used only for repository-side JavaScript syntax validation.

An existing schema-7 installation must use the explicit migration after a verified backup/restore test:

```bash
php database/migrations/7-to-8.php --apply
```

`database/bootstrap.php --repair` is not a migration path.

Use the read-only readiness report after setup/deployment:

```bash
php database/readiness.php
```

See `docs/INSTALLATION.md` for fresh install, backup, migration, rollback, and onboarding detail.

## Release-candidate build

Build a deterministic internal candidate from an exact source revision:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits a ZIP, manifest, and SHA256 under `dist/`. rc.3 packages the license/NOTICE files, starter public site, onboarding, safe public site initializer, operator documentation, `AGENTS.md`, schema/migrations, redirect runtime, and deployment examples. It excludes `.git`, `.github`, `.lattice`, tests, release tooling, local `config/site.php`, populated secret INI files, uploads/runtime state, and known adopter/private residue.

CI produces the same candidate only as a short-lived private workflow artifact until publication is explicitly authorized.

See `docs/RELEASE.md` for the candidate contract and final pre-release blockers.

## Pre-release frontier

Before a public publication decision, rc.3 must prove four things:

1. a new adopter can reach a coherent site through the documented onboarding path;
2. GitHub-to-host operation and rollback are understandable and reproducible;
3. iterative LLM collaboration can change design/content/features without bypassing governance;
4. the complete empty-site path is rehearsed from a clean candidate, including representative agent-assisted change and recovery/rollback evidence.

The production proving-ground parity check still applies: a material reusable core feature added upstream automatically reopens parity work before publication.

## Further documentation

- `docs/ARCHITECTURE.md` — system and authority model
- `docs/INSTALLATION.md` — fresh install, onboarding, migrations, backup, rollback
- `docs/REPOSITORY-OPERATIONS.md` — GitHub and hosting operations
- `docs/LLM-COLLABORATION.md` — governed agent-assisted iteration
- `docs/DEPLOYMENT-ADAPTERS.md` — host transport/interception contract
- `docs/UPSTREAMING.md` — moving reusable features between adopter/source repos
- `docs/EXTRACTION-MATRIX.md` — extraction/product frontier
- `docs/RELEASE.md` — internal candidate and publication gate
- `SECURITY.md` — security boundaries
