# Architecture

## Core invariant

Accepted authored state has one authority before publication. Public files are projections, not competing editorial masters.

The reference implementation proved this model on a static-first PHP/MySQL site. AI Native CMS keeps the same invariant while removing adopter-specific content, labels, environment names, and deployment assumptions.

## Layers

### 1. Site adapter

`config/site.php` describes the adopter’s public structure: site identity, editable pages, canonical structured documents, generated roots, and projection outputs. A site may add adapter code for custom content types, but core modules must not require a particular site’s page names, project taxonomy, or theme.

`cms.editable_pages` names HTML surfaces whose `data-cms-id` leaves are editable. `cms.documents` optionally registers structured authored files that should participate in the same canonical SQL and repository-reconciliation model.

### 2. Guarded runtime

`api/database.php` and `api/runtime.php` own secret loading, database transport, HTTPS/origin checks, sessions, authentication, CSRF, request limits, rate limits, and audit records.

Human UI and agent-facing operations share these boundaries. An agent does not gain a second write path.

### 3. Canonical content model

MySQL stores accepted authored state:

- `page_blocks` for editable page leaves;
- `content_documents` for full page sources and structured authored documents;
- `posts` and `post_revisions` for long-form publishing;
- `page_block_templates` and `page_compositions` for reusable composed pages;
- `site_navigation`, `site_branding`, `media_library`, and `seo_overrides` for site-wide authored metadata;
- `page_revisions`, `cms_activity`, `content_change_log`, and `content_update_sets` for history and provenance.

`api/content-core.php` contains site-neutral filesystem, block extraction, sanitization, optimistic-revision, and atomic-write primitives. `api/content-authority.php` owns canonical block/document commits and projection. `api/cms-pages.php` is the first guarded human/agent-compatible mutation endpoint using that authority.

Repository source remains useful as a portable seed, code-reviewed proposal, and database-free test fixture. It does not silently outrank a newer accepted database value.

### 4. Reconciliation

`api/content-sync.php` implements three-way repository reconciliation. For content that tracks source lineage, the runtime retains both the accepted content hash and the last effective source hash.

When a repository candidate changes:

1. if canonical SQL still equals the prior effective repository source, the new repository candidate advances SQL;
2. if canonical SQL has diverged, the SQL value is preserved and only source lineage advances;
3. every applied or preserved transition is recorded in `content_change_log`.

This is deliberately not last-write-wins.

Deliberate supersession uses immutable, compare-and-swap update sets under `database/content-updates/`. An update names the predecessor it expects so it cannot erase an unexpected newer value. A standing update set may also normalize a lagging repository candidate until the adopter’s checked-in source catches up.

### 5. Projection

Authenticated writes resolve into canonical state first. Deterministic projectors then materialize public HTML and configured structured documents. `php database/reconcile.php <source-ref>` currently performs the portable core sequence:

1. bootstrap canonical page/document state when needed;
2. reconcile repository candidates;
3. apply immutable update sets;
4. project configured documents;
5. project configured pages with canonical block overlays.

Additional projectors for posts, discovery, navigation, composed pages, feeds, and other derived outputs attach after this authority layer; they must not become alternate authored-state stores.

Anonymous requests do not depend on MySQL. A static host or CDN can serve the public projection while the CMS remains a private authoring plane.

## Core versus adopter code

The extraction intentionally preserves the reference implementation’s top-level paths:

- `api/` — reusable runtime and mutation/read APIs;
- `cms/` — reusable administration UI;
- `database/` — schema, migrations, bootstrap, reconciliation, rebuild;
- `tests/` — product contracts and security regression;
- `config/` — adopter-owned public structure.

The path compatibility is strategic. General work developed first inside an adopter repository can be upstreamed with small diffs if the adopter keeps site behavior behind the configuration boundary.

## AI-native operation contract

AI-native does not mean that an LLM owns content state. It means automation can reason about and mutate explicit durable objects under the same rules as a human operator.

A mature operation surface should therefore:

1. identify a typed target rather than a screen coordinate;
2. include the target’s expected revision/hash when replacing state;
3. identify the origin and origin reference;
4. validate authorization and consequences before mutation;
5. write canonical state and durable provenance atomically where practical;
6. return the new identity/revision and the required projection work;
7. remain replay-safe or explicitly reject stale replays.

Browser automation is an adapter of last resort, not the CMS architecture.

## Deployment boundary

The core must be deployable on ordinary PHP/MySQL hosting and must not encode credentials, a hosting vendor, or a particular Git provider. Deployment adapters may automate repository-to-host synchronization, but publication remains an explicit operator decision.
