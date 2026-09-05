# AI Native CMS

AI Native CMS is a static-first PHP/MySQL publishing system designed for human and LLM-assisted iteration without turning generated files or conversation history into a second source of truth.

MySQL owns accepted authored and mutable application state. Git owns application code, structural behavior, migrations, tests, documentation, deployment adapters, and adopter-owned public configuration. Authenticated humans and agents work through the same guarded contracts; anonymous readers consume deterministic public projections without requiring a database connection.

## Public release candidate

The current candidate is **`0.1.0-rc.4`**, schema **10**, with required tag **`v0.1.0-rc.4`**. The published `0.1.0-rc.3` release remains a frozen schema-8 predecessor with an explicit 8→9→10 upgrade path.

This remains a public prerelease for evaluation, site builds, and governed production trials. Keep tested backups and review migrations, deployment, and provider changes before consequential use.

AI Native CMS is **source-available**, not OSI-approved open source. It is licensed under **Apache License 2.0 subject to Commons Clause License Condition v1.0**. See `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` for the binding terms.

## Why “AI native”

An agent should be able to redesign a site, revise content, add a feature, diagnose a bug, or prepare a release without bypassing CMS governance.

That means repository-owned structure/code moves through branches and reviewable diffs; accepted mutable state stays canonical in MySQL; generated public output is replaceable evidence; schema changes use explicit migrations; humans and agents share validation/revision/provenance rules; and durable decisions live in code/tests/docs/project state rather than only in a conversation.

Start with `AGENTS.md` and `docs/LLM-COLLABORATION.md` when using an agent.

## Start with a real site

The package ships a neutral Home/About/Writing starter. Create adopter-owned **public, non-secret** configuration:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
```

Then configure private runtime secrets outside the public root and initialize current schema 10:

```bash
php database/bootstrap.php
php database/reconcile.php initial-import
php database/readiness.php
```

Open `/cms/` over HTTPS. Unfinished setup lands in the resumable **Onboarding** workspace, whose progress is derived from real configuration, canonical database state, projections, and readiness rather than a separate completion flag.

See `docs/INSTALLATION.md` for fresh install and upgrades.

## CMS workspaces

The first-party `/cms/` surface includes:

- **Onboarding** — state-derived first-run/site-build guidance;
- **Composer** — governed visual page composition, content editing, page creation, hierarchy, and clean-route presentation;
- **Blocks** — design and manage reusable semantic presets and snapshot edited page blocks as new presets;
- **Media** — first-party asset catalog and bounded uploads;
- **Navigation** — canonical primary navigation;
- **Branding** — site identity plus explicitly exposed design tokens;
- **Writing** — long-form publishing with revisions and static projection;
- **Search + Social** — canonical SEO controls plus quality findings;
- **Redirects** — same-site redirect authority and slug history;
- **Audience** — list configuration, consent-state inspection, pending resend, operator unsubscribe, confirmed CSV export, and transactional-mail status/testing;
- **Readiness** — authenticated read-only deployment/operability evidence.

There is no frontend framework or third-party active runtime dependency in the CMS UI.

## Authority model

### Git / repository authority

Repository files own PHP/JavaScript behavior, page shells and governed preset rendering, CSS/accessibility behavior, schema and migrations, tests/contracts, documentation/agent instructions, deployment adapters, and non-secret adopter configuration.

### Canonical MySQL authority

Schema-10 accepted state includes page content leaves, reusable block definitions, typed page compositions, posts/revisions, media metadata, navigation, branding values, SEO overrides, redirects, Audience list definitions, subscription/consent state, and revision/audit history.

Mail credentials are not canonical database content. They stay in private runtime configuration.

### Generated public projection

HTML, JSON/XML/text discovery files, clean route directories, writing indexes, and redirect maps are deterministic outputs. Fix their owning repository/canonical source rather than editing generated symptoms.

### Host/provider state

Credentials, web-server rewrites, caching, document roots, deployment mechanisms, and host-specific diagnostics remain operator/deployment concerns behind bounded adapters.

## Core capabilities

The reusable core includes:

- hardened owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-10 canonical content, composition, media, navigation, branding, SEO, redirects, and Audience authority;
- repository-source lineage with three-way reconciliation that preserves newer canonical edits;
- **Page Composer** with server-generated structural HTML, typed preset values, first-adoption stale-source protection, and canonical text convergence;
- **Block Composer** for governed semantic primitives plus save-edited-instance-as-new-preset behavior;
- clean managed `/slug/` projection while retaining stable internal page keys;
- long-form publishing with revisions, stale-write rejection, restore, static projection, and slug-history redirects;
- Audience double opt-in with hashed bearer tokens, explicit POST confirmation, pending expiry/cooldown, unsubscribe suppression state, and explicit resubscribe cycle;
- replaceable transactional mail transports: authenticated SMTP with TLS/STARTTLS verification, deliberate host-local PHP `mail`, and development/CI log transport;
- cPanel-oriented email-provider onboarding without making cPanel list authority;
- deterministic SEO/social/schema enhancement and redirect graph validation;
- site-neutral public discovery: `site-index.json`, XML/text sitemaps, public Markdown alternates, compact `llms.txt`, optional expanded `llms-full.txt`, and idempotent discovery metadata derived only from public state;
- CLI-only fresh-schema bootstrap plus explicit 7→8→9→10 migrations;
- authenticated/CLI readiness and provider adapter seams;
- deterministic release packaging with exact source provenance and residue scanning.

## Repository → hosting operations

`docs/REPOSITORY-OPERATIONS.md` covers Git vs MySQL vs generated output vs host state, branch/PR workflow, SSH pull-to-host and artifact deployment patterns, secret placement, backups/migrations, hotfix recovery, readiness, and rollback.

A Git deployment updates repository-owned behavior. It does **not** reset canonical CMS state from Git on every deploy.

## Working with an LLM

`AGENTS.md` is the repository-level contract an agent should read first. `docs/LLM-COLLABORATION.md` gives request patterns for interface work, content revision, features, bugs, schema changes, and releases.

An LLM may inspect, propose, implement, test, and explain changes; it does not become a new authority layer. Repository changes use branches/PRs and verification. Canonical mutations use the CMS/store contract. Projection problems are repaired at their owning source. Provider behavior stays behind adapters. Production secrets are not needed for ordinary repository work.

## Public delivery and discovery

Published posts materialize static article HTML and a public writing index. Drafts do not project publicly. Canonical SEO stays in `seo_overrides`; deterministic projection may fill inherited social/schema defaults without overwriting page-specific authority.

Manual redirects are canonical in `redirect_records`; configured system aliases are read-only. `__redirect.php` consumes a generated static map and never opens MySQL.

After clean routes materialize, core discovery scans indexable same-origin public HTML, emits `site-index.json` plus XML/text sitemaps, and generates adjacent `.md` alternates with canonical HTML attribution. The compact absolute-link `llms.txt` prefers these alternates; HTML carries one Markdown alternate relation and one LLM discovery relation. Private CMS state, subscriber records, credentials, drafts, private APIs, and host-only state are excluded. Generated Markdown is removed when its source disappears or becomes ineligible, while authored Markdown is preserved.

`llms-full.txt` remains optional. When supplied, it must contain a blank-line-delimited `---` corpus separator: the compact prefix is refreshed while the public corpus below it remains verbatim. A present file that cannot be safely synchronized fails the rebuild. See [the discovery contract](docs/ARCHITECTURE.md#8-public-discovery) for ownership, exclusions, and recovery.

## Requirements and migration

Requirements: PHP 8.1+, PDO MySQL, and MySQL 5.7+ / 8.x with `utf8mb4`. Python 3.10+ and Node are validation-only.

Fresh rc.4 installs bootstrap directly to schema 10. Existing rc.3/schema-8 sites use:

```bash
php database/migrations/8-to-9.php --apply
php database/migrations/9-to-10.php --apply
```

Schema 7 first applies `database/migrations/7-to-8.php --apply`. `database/bootstrap.php --repair` is not an upgrade path. See `docs/INSTALLATION.md` before migration.

## Release assurance

Rc.4 requires exact-head cumulative validation plus a deterministic packaged-candidate rehearsal. The rehearsal proves a clean schema-10 install, authenticated onboarding/readiness, public discovery, canonical mutation/projection, paired recovery, the actual published rc.3 schema-8 → v9 upgrade, and v9 → v10 Audience/mail behavior.

Build an exact-revision candidate with:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

See `docs/RELEASE.md` for the publication contract. Technical readiness does not authorize release publication.

## Documentation

- `docs/ARCHITECTURE.md` — authority and projection model
- `docs/INSTALLATION.md` — fresh install, upgrades, backup, rollback
- `docs/REPOSITORY-OPERATIONS.md` — GitHub and hosting operations
- `docs/LLM-COLLABORATION.md` — governed agent-assisted iteration
- `docs/CPANEL-EMAIL.md` — transactional mail setup using cPanel-provided connection details
- `docs/DEPLOYMENT-ADAPTERS.md` — host transport/interception contract
- `docs/UPSTREAMING.md` — moving reusable features from proving grounds into public core
- `docs/EXTRACTION-MATRIX.md` — reusable-core/product frontier
- `docs/RELEASE.md` — public release-candidate contract
- `SECURITY.md` — security boundaries
