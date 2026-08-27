# Architecture

## Core invariant

Accepted authored state has one authority before publication. Public files are projections, not competing editorial masters.

The reference implementation proved this model on a static-first PHP/MySQL site. AI Native CMS keeps the same invariant while removing adopter-specific content, labels, environment names, and deployment assumptions.

The published `0.1.0-rc.3` release remains schema v8. Post-release development adds an explicit schema-v9 migration and does not redefine that release artifact.

## Layers

### 1. Site adapter

`config/site.php` describes adopter-owned public source boundaries: site identity, editable repository pages/documents, generated roots, writing/media paths, navigation defaults, branding tokens, read-only redirect aliases, readiness adapters, and deterministic projection hooks. Core modules must not require a particular site taxonomy, theme, content ledger, host, or web server.

Repository pages are the portable Git/source class. They provide reviewed source lineage and shell behavior. After schema v9, Page Composer may deliberately adopt their top-level blocks into canonical composition; doing so does not turn generated public HTML back into source authority.

### 2. Guarded runtime

`api/database.php` and `api/runtime.php` own secret loading, database transport, HTTPS/origin checks, sessions, authentication, CSRF, request limits, rate limits, and audit records.

Human UI and agent-facing operations share these boundaries. Canonical mutation endpoints fail closed when the installed schema is older than the code contract. An agent does not gain a second write path.

### 3. Canonical state

The frozen schema-v8 release stores accepted authored state in MySQL, including `page_block_templates` and `page_compositions`. The explicit 8→9 migration replaces the active template authority with `block_presets`, rewrites composition references from `templateKey` to `presetKey`, and archives the old template table.

Schema-v9 canonical state includes:

- `page_blocks` and `content_documents` for accepted editable leaves and repository lineage;
- `block_presets` for converted repository structures and governed semantic primitive definitions;
- `page_compositions` for page identity, hierarchy, shells, ordered preset instance IDs, and independent typed value snapshots;
- `posts` and `post_revisions` for long-form publishing;
- `media_library`, `site_navigation`, `site_branding`, and `seo_overrides` for reusable site-wide state;
- `redirect_records` for canonical manual and post-history redirects;
- `page_revisions`, `cms_activity`, `content_change_log`, and `content_update_sets` for revision/provenance history.

A preset is a recipe, not a live component. Editing a preset affects future placements. Existing page instances retain their stored typed values until the page itself is edited.

Repository-owned `redirects.system_aliases` are a separate read-only compatibility source. They may participate in projection, but browser/API mutations cannot rewrite them.

`api/content-core.php` contains site-neutral filesystem, sanitization, revision, and atomic-write primitives. Canonical object modules own validation/persistence; `/api/cms-*` endpoints expose the same guarded model to human and agent callers.

### 4. Page authoring boundary

After schema v9, Page Composer is the sole browser mutation boundary for public pages. `/cms/pages.php` is an authenticated compatibility redirect and the former standalone Pages API/client are not shipped.

The public page is shown inside a same-origin sandboxed iframe with site scripts disabled. That iframe is an interaction surface, not an authority surface. Browser actions resolve to typed preset variables and stable instance identities. Add, duplicate, move, remove, copy, media, link, and primitive-text changes are serialized as `presetKey`, `instanceId`, and bounded typed values. Structural HTML is regenerated and validated on the server.

For an existing repository page that has never had a composition record, `compositionPayload()` derives candidate preset instances from the reviewed repository blocks and hydrates them from the current canonical visible page. That preserves prior accepted `page_blocks` edits. First adoption requires both the absence hash (`none`) and an `expectedSourceHash`; a stale source/canonical view is rejected rather than silently adopted.

Composition saves deliberately converge the two canonical views of editable page text. The typed composition snapshot is rendered on the server and its editable leaves replace that page's canonical `page_blocks` inside the same composition transaction. Routine rebuilds use the opposite mode: they preserve accepted `page_blocks` while reconstructing structural composition. This distinction prevents either a live edit or a later rebuild from silently undoing the other.

Block Composer remains a separate reusable-block design tool but can be embedded inside Page Composer. Its browser payload contains governed semantic definitions, not arbitrary structural HTML/CSS.

### 5. Repository-source boundary and reconciliation

Repository source is a portable seed, code-reviewed proposal, and database-free fixture. It never silently outranks newer accepted SQL.

`api/content-sync.php` implements three-way reconciliation using both canonical hashes and the last effective source hashes. A changed repository candidate advances SQL only when canonical state still matches the previous effective source; divergent accepted SQL is preserved and source lineage advances instead. Immutable compare-and-swap update sets express deliberate supersession.

Repository reconciliation is intentionally **not a browser action**. `database/reconcile.php` is CLI/deployment-adapter-only. Schema-v8 installs remain read-compatible during the explicit upgrade seam so they can reconcile and reach a safe backup point before 8→9 migration. New block/preset/Page Composer mutations require schema v9.

### 6. Schema evolution

`database/bootstrap.php` is a fresh/current-release initializer. `--repair` only completes interrupted installs already stamped with the bootstrap schema; it never upgrades an older schema.

Version transitions use explicit migration files. Schema 7→8 adds canonical redirects. Schema 8→9 creates `block_presets`, converts prior structural templates, rewrites composition references, archives the retired template authority, and advances `app_meta.schema_version` only after guarded work succeeds. Both migrations acquire database locks and are idempotent where required by their contract.

Rollback means restoring the paired pre-migration database backup and code revision, not trying to reverse accepted canonical writes piecemeal.

### 7. Redirect authority and anonymous routing

`api/redirects.php` owns canonical redirect semantics. Source/target paths are same-site and bounded; reserved application paths, control characters, ambiguous encoded separators, dot segments, self-resolution, unsafe status codes, conflicting authorities, public-file collisions, and graph cycles are rejected.

Manual records carry optimistic revision hashes. Published post slug changes are preflighted against the redirect graph and persist their old route as post-managed history; manually governed ownership is not silently taken over. Safe post-managed chains can collapse to the newest target.

`redirectProject()` merges active canonical SQL records with configured read-only system aliases and emits deterministic `__redirect-map.php`. `__redirect.php` consumes only that map. Anonymous redirects therefore remain static-first and never query MySQL.

Routing unresolved web requests into `__redirect.php` is a deployment concern. Apache/Nginx/CDN/host-specific interception belongs in adapters; the core owns the map/runtime semantics, not one server configuration.

### 8. Projection and finalization

Canonical content is projected first; site-wide derived state converges through one explicit finalization boundary so one projector cannot silently erase another projector's output until the next rebuild.

`api/content-rebuild.php` preserves bounded adopter hooks and defines this order:

1. repository documents/pages and canonical compositions;
2. published posts;
3. `after_pages` adopter hooks;
4. canonical SEO projection;
5. `after_seo` adopter hooks for sitemap/discovery/feed work that must consume final SEO state;
6. canonical navigation;
7. canonical branding;
8. deterministic redirect map;
9. `finalize` adopter hooks.

The public core does not impose one sitemap/discovery implementation, but it guarantees an `after_seo` phase so those adapters can honor noindex/canonical decisions instead of racing SEO.

Anonymous public delivery remains database-free. A static host or CDN may serve public files while the private authoring plane uses MySQL.

## Core versus adopter code

The extraction intentionally preserves the reference implementation’s top-level paths:

- `api/` — reusable runtime and canonical operations;
- `cms/` — reusable administration UI;
- `database/` — schema, migrations, bootstrap, reconciliation;
- `tests/` — product/security/regression contracts;
- `config/` — adopter-owned public source/configuration and bounded adapter registry.

This path compatibility lets general work developed first inside an adopter repository move upstream as reviewable diffs while personal/site-specific state remains behind configuration or adapters.

## AI-native operation contract

AI-native does not mean an LLM owns truth. Automation mutates explicit durable objects under the same constraints as a human operator. A mature operation therefore identifies a typed target, supplies expected revisions/source hashes when replacing state, records provenance, validates authority/consequences, writes canonical state atomically where practical, returns the resulting revision/projection work, and rejects stale replay.

Browser automation is an adapter of last resort, not the CMS architecture.

## Deployment boundary

The core must remain deployable on ordinary PHP/MySQL hosting without encoding credentials, a host vendor, Git provider, or server implementation. Deployment adapters may perform repository synchronization, unresolved-route interception, cache/compression policy, or other transport work. Repository visibility, licensing, publication, and production deployment remain explicit operator decisions.
