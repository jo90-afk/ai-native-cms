# Architecture

## Core invariant

Accepted authored and mutable application state has one authority before publication. Public files are projections, not competing masters.

The published `0.1.0-rc.3` artifact remains schema 8. The `0.1.0-rc.4` candidate promotes fresh installations to **schema 10** while preserving explicit 8→9→10 upgrades.

## Layers

### 1. Site adapter

`config/site.php` describes adopter-owned public, non-secret structure: site identity, editable repository pages/documents, generated roots, writing/media paths, navigation defaults, branding tokens, read-only redirect aliases, readiness adapters, clean-route policy, and bounded projection hooks. Core modules must not require a particular site taxonomy, theme, host, or authored corpus.

Repository pages are portable reviewed source proposals. Page Composer can deliberately adopt their top-level structure into canonical composition; generated public HTML never becomes reverse source authority.

### 2. Guarded runtime

`api/database.php` and `api/runtime.php` own private configuration loading, database transport, HTTPS/origin checks, sessions, authentication, CSRF, request/rate limits, and audit records. Human UI and agent-facing operations share these boundaries. Older installed schemas fail closed for mutation surfaces requiring newer authority.

### 3. Canonical schema-10 state

Fresh rc.4 installs contain:

- `page_blocks` and `content_documents` for accepted editable leaves and repository lineage;
- `block_presets` for converted repository structures, governed semantic primitives, and server-generated trusted presets;
- `page_compositions` for page identity, hierarchy, shells, ordered preset instances, and independent typed value snapshots;
- `posts` and `post_revisions` for long-form publishing;
- `media_library`, `site_navigation`, `site_branding`, and `seo_overrides` for reusable site-wide state;
- `redirect_records` for canonical manual and post-history redirects;
- `audience_lists` for stable generic list identity, public/operator labels, confirmation copy, and enabled/disabled state;
- `audience_subscriptions` for pending/confirmed/unsubscribed membership, consent timestamps, resend state, hashed confirmation tokens, and bounded source provenance;
- revision/audit/change tables for history and reconciliation evidence.

A preset is a recipe, not a live shared component. Existing page instances retain their typed values when a source preset changes.

`mail_outbox` is only a development/log transport. SMTP credentials are private runtime configuration; neither transport nor cPanel becomes list or content authority.

### 4. Page and block authoring

After schema 9, Page Composer is the sole browser mutation boundary for public page composition. The public page appears in a same-origin sandboxed iframe with site scripts disabled. Browser actions serialize stable preset/instance identities and bounded typed values; structural HTML is generated and validated server-side.

For repository pages without a stored composition, the composer derives a candidate structure from reviewed source and hydrates editable leaves from current canonical SQL. First adoption requires absence/source hashes so stale source cannot silently become authority.

Composition saves converge typed composition and canonical page text in the same guarded write path. Rebuilds preserve accepted canonical text while reconstructing structural composition.

Block Composer remains the separate design surface for reusable semantic presets. Browser payloads carry governed definitions rather than arbitrary structural HTML/CSS. “Save as new block” snapshots an edited instance into a new independent preset without mutating the source preset or current page instance.

### 5. Repository reconciliation

Repository source is a portable seed/code-reviewed proposal and database-free fixture. It never silently outranks newer accepted SQL.

`api/content-sync.php` performs three-way reconciliation using current canonical hashes and prior effective source hashes. Changed source advances SQL only when canonical state still matches its predecessor; divergent accepted SQL is preserved. Immutable compare-and-swap update sets express deliberate supersession.

Reconciliation is CLI/deployment-adapter-only through `database/reconcile.php`. Browser CMS pages do not fetch/pull Git, migrate, or reconcile source into canonical state.

### 6. Schema evolution

`database/bootstrap.php` initializes the **current fresh schema** only. In rc.4 that is schema 10. `--repair` completes interrupted current-schema installs; it does not upgrade older databases.

Versioned migrations preserve historical upgrade boundaries:

- 7→8 establishes canonical redirects;
- 8→9 creates `block_presets`, converts prior trusted templates, rewrites composition references, and archives `page_block_templates`;
- 9→10 creates Audience authority, preserves valid legacy subscription membership into disabled generic lists when present, archives the retired `subscribers` table, and advances to schema 10.

Core read/rebuild paths that must support a safe pre-migration backup/reconciliation seam remain schema-8 compatible. New composition mutation requires schema 9; Audience mutation requires schema 10.

Rollback restores the paired pre-migration database and code revision rather than trying to reverse accepted writes piecemeal.

### 7. Redirect and clean-route projection

`api/redirects.php` owns canonical redirect semantics. Redirects are bounded to same-site safe paths, reject reserved/control/ambiguous/cyclic states, use optimistic revisions, and project to deterministic `__redirect-map.php`. `__redirect.php` is database-free.

Managed pages retain stable internal `.html` identity while `api/page-routes.php` and `api/page-projection.php` may materialize reader-facing `/slug/` directories. Relocated HTML references, managed links, canonical/social URL metadata, and known discovery outputs are rewritten deterministically. JavaScript-created runtime URLs are outside HTML parsing and should be root-relative when pages may relocate.

Request interception remains a deployment-adapter concern.

### 8. Public discovery

Rc.4 adds site-neutral core discovery rather than requiring every adopter to hand-maintain indexes.

After public HTML, SEO, navigation, branding, redirects, and clean routes converge:

1. `api/discovery-projection.php` scans public HTML only;
2. `noindex` pages and external canonicals are excluded;
3. duplicate legacy/clean files collapse onto their same-site canonical URL;
4. `site-index.json`, `sitemap.xml`, and `sitemap.txt` are emitted from that public surface;
5. `api/llms-projection.php` builds a compact `llms.txt` routing index from `site-index.json`;
6. an optional existing `llms-full.txt` expanded-public-context body can be synchronized beneath the compact index;
7. public HTML receives an idempotent `rel="describedby"` link to `/llms.txt`.

These outputs never read private CMS state, subscriber records, credentials, drafts, or host-only operational markers. They are replaceable public projections, not content authority.

### 9. Projection/finalization order

`api/content-rebuild.php` converges public state in this order:

1. configured documents/pages and canonical compositions;
2. published posts;
3. `after_pages` adopter hooks;
4. canonical SEO;
5. `after_seo` adopter hooks;
6. canonical navigation;
7. canonical branding;
8. deterministic redirect map;
9. `finalize` adopter hooks;
10. managed clean-route materialization;
11. core public discovery index/sitemaps;
12. `llms.txt` and public discovery-link injection.

Hooks remain available for adopter-specific derived surfaces; they may not replace canonical authority. Anonymous delivery remains static-first/database-free.

## Core versus adopter code

- `api/` — reusable runtime, authority, and projection operations;
- `cms/` — reusable administration UI;
- `database/` — current schema, migrations, bootstrap, reconciliation;
- `tests/` — product/security/release contracts;
- `config/` — adopter-owned public source/configuration and bounded adapters.

Path compatibility lets general mechanisms developed in a proving-ground repository move upstream as reviewable behavior while site identity/content/private host state remain behind configuration or adapters.

## AI-native operation contract

AI-native operation does not give an LLM special authority. Automation names a durable target, supplies expected revisions/source hashes when replacing state, records provenance, validates consequences, writes through guarded contracts, runs deterministic projection, and rejects stale replay. Conversation history and generated output do not outrank repository or canonical state.

## Deployment boundary

The core remains deployable on ordinary PHP/MySQL hosting without encoding credentials, a host vendor, Git provider, or private filesystem layout. Deployment adapters may perform repository synchronization, unresolved-route interception, cache/compression policy, or host-specific operational provenance. Missing host provenance should be represented as unknown/unobserved—not fabricated evidence that canonical SQL is stale.

Repository visibility, installed-site migration, provider credentials, production deployment, and public release publication remain explicit operator consequences.
