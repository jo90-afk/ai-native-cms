# AI Native CMS

AI Native CMS is a static-first publishing system being extracted from a production CMS into a site-neutral public product.

The core design is deliberately conservative: MySQL owns accepted authored state, authenticated operators and agents mutate that state through guarded operations, and public HTML/JSON/XML are deterministic projections. Anonymous readers do not need a database connection.

## Why “AI native”

The product is shaped so an AI agent can operate the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes are expressed against typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

An AI agent does not receive a privileged second authority system. The same authentication, expected-revision, canonical-state, provenance, and projection contracts apply regardless of caller.

## Current usable slice

The repository now contains a working site-neutral CMS for configured static pages, structured documents, and long-form Markdown publishing:

- hardened PHP/MySQL owner authentication and request boundaries;
- schema-v7 canonical content, revision, provenance, composition, media, navigation, branding, and SEO tables;
- canonical editable page blocks plus full page-source documents;
- authenticated page editing with optimistic hashes and bounded rich-text sanitization;
- transactional long-form posts with immutable prior snapshots, stale-write rejection, revision restore, draft/published state, and deterministic public article projection;
- escaped bounded Markdown rendering with safe links and no raw-HTML passthrough;
- canonical SEO overrides with structured robots controls, same-origin canonical enforcement, inherited/custom social copy, and deterministic reapplication after rebuild;
- three-way repository reconciliation that preserves newer CMS edits;
- immutable compare-and-swap release update sets;
- deterministic rebuild ordering and bounded adopter projector hooks;
- first-party `/cms/` workspaces for Pages, Writing, and SEO with no frontend framework or third-party active runtime dependency.

Composer/templates, media management, global navigation, branding controls, new-page hierarchy, bootstrap UI, and production readiness are still being extracted.

## Product boundary

Reusable core:

- hardened PHP/MySQL runtime and owner authentication;
- canonical authored-content state and revision/provenance records;
- page blocks and structured content documents;
- posts and post revisions;
- reusable page-block templates and compositions;
- media catalog, branding, navigation, and SEO state;
- deterministic static projection;
- guarded operations suitable for both human and agent callers.

Adopter-owned state:

- site copy and content seeds;
- page/document registry and labels;
- theme assets and visual identity;
- article template and other site-specific templates;
- custom deterministic projectors and project integrations;
- deployment credentials and host-specific secrets.

Optional extensions remain outside the core when they are not general CMS concerns.

## Compatibility strategy

The repository intentionally keeps the reference implementation’s `api/`, `cms/`, and `database/` topology. General features can therefore move between an adopter site and this repository with small, reviewable diffs. Site-specific values must enter through configuration or adapters rather than forks of core behavior.

See `docs/ARCHITECTURE.md` and `docs/UPSTREAMING.md`.

## Minimal setup for the current slice

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ for repository validation

Node is used only for repository-side JavaScript syntax validation; it is not a production runtime dependency.

1. Copy `config/site.example.php` to `config/site.php` and define the site name, public origin, editable HTML pages, canonical structured documents, and writing paths. Site configuration contains public structure, not credentials.
2. Create a MySQL database and import `database/schema.sql`.
3. Put private settings in environment variables or an INI file outside the public root. `database/private-config.example.ini` lists the supported `AINCMS_*` keys. At minimum configure the database, public origin, CMS bootstrap username/password hash, and rate-limit secret.
4. Ensure every configured editable page already exists and mark editable text leaves with stable `data-cms-id` values, for example `<h1 data-cms-id="hero.title">Title</h1>`.
5. Initialize/reconcile canonical state from the repository:

```bash
php database/reconcile.php initial-import
```

6. Serve the repository with PHP and open `/cms/` directly. The CMS intentionally does not require or add a public-site navigation link.

The first successful bootstrap login persists the owner identity into MySQL. Pages can then be edited under **Pages**. **Writing** creates Markdown-backed drafts or published posts; publishing materializes `<writing.route_root>/<slug>/index.html` plus the configured public post index. **SEO** manages search/social metadata for configured pages and published articles.

## Publishing model

`posts` is canonical. Every update to an existing post writes its previous canonical state to `post_revisions` before replacement. Browser/agent saves carry an expected revision hash; a stale caller receives a conflict rather than silently overwriting newer content.

Markdown is intentionally bounded. The core renderer supports headings, paragraphs, ordered/unordered lists, blockquotes, fenced code, emphasis, inline code, and safe links. Source HTML is escaped before controlled markup is produced. An adopter may provide a repository-owned article template through `writing.article_template`; the rendered Markdown body remains the only HTML-valued placeholder.

Drafts have no public article projection. Published posts materialize static HTML and appear in the public post index. Restoring a prior revision makes that snapshot the new current canonical post while preserving the previously current version in history.

## SEO model

SEO state is canonical in `seo_overrides`, not in whichever generated HTML happened to be edited most recently. The editor controls title, description, indexing/follow/archive behavior, preview limits, canonical mode, and inherited/custom Open Graph/Twitter copy.

Custom canonicals are restricted to the configured public origin. During rebuild, pages and articles are regenerated first and saved SEO overrides are applied afterward, so publishing cannot erase deliberate metadata.

## Repository reconciliation

`php database/reconcile.php <source-ref>` performs this portable sequence:

1. bootstrap configured page/document state if needed;
2. compare incoming repository candidates with both canonical hashes and prior effective source hashes;
3. accept ordinary source changes only when canonical SQL still matches the prior source;
4. preserve divergent/newer canonical SQL and record the incoming source lineage;
5. apply immutable explicit update sets from `database/content-updates/`;
6. project canonical documents/pages;
7. project published long-form articles and their public post index;
8. reapply canonical SEO overrides;
9. run adopter projectors at the configured bounded phases.

This prevents a Git pull from silently undoing an accepted CMS edit and prevents regeneration from silently undoing SEO state.

## Rebuild hooks

Adopters may register trusted repository-owned projectors in `config/site.php` under `projection.hooks`. Hooks run only at bounded phases (`before_documents`, `after_documents`, `before_pages`, `after_pages`, `finalize`), their scripts must resolve inside the repository root, and the callable is named explicitly. `after_pages` executes after the core page, publishing, and SEO projections, so discovery/feed/sitemap adapters can consume final public targets.

Hooks are for deterministic presentation/integration work. They must not become alternate stores of authored truth.

## Development status

Pre-release extraction. The repository remains private while additional production-proven modules are separated from adopter-specific behavior. Making the repository public, choosing a license, tagging a release, and adopting this core into a production site are explicit release boundaries rather than automatic outcomes of development merges.
