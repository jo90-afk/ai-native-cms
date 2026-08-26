# AI Native CMS

AI Native CMS is a static-first publishing system being extracted from a production CMS into a site-neutral public product.

The core design is deliberately conservative: MySQL owns accepted authored state, authenticated operators and agents mutate that state through guarded operations, and public HTML/JSON/XML are deterministic projections. Anonymous readers do not need a database connection.

## Why “AI native”

The product is shaped so an AI agent can operate the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes are expressed against typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

An AI agent does not receive a privileged second authority system. The same authentication, expected-revision, canonical-state, provenance, and projection contracts apply regardless of caller.

## Current usable slice

The repository now contains a working site-neutral CMS for repository pages, CMS-created composed pages, structured documents, first-party media, long-form Markdown publishing, canonical SEO, primary navigation, and bounded site branding:

- hardened PHP/MySQL owner authentication and request boundaries;
- schema-v7 canonical content, revision, provenance, composition, media, navigation, branding, and SEO tables;
- canonical editable page blocks plus full page-source documents;
- authenticated page editing with optimistic hashes and bounded rich-text sanitization;
- repository-owned page-block templates exposing bounded rich-text, link, and media variables;
- canonical typed page compositions with stale-write protection and deterministic re-projection;
- CMS-created root-level HTML pages built from trusted repository shells/templates with validated parent hierarchy;
- a first-party media catalog whose metadata is canonical SQL while image bytes remain adopter-owned files;
- validated JPEG/PNG/WebP/GIF uploads constrained to configured public roots and size limits;
- transactional long-form posts with immutable prior snapshots, stale-write rejection, revision restore, draft/published state, and deterministic public article projection;
- escaped bounded Markdown rendering with safe links and no raw-HTML passthrough;
- canonical SEO overrides with structured robots controls, same-origin canonical enforcement, inherited/custom social copy, and deterministic reapplication after rebuild;
- canonical primary navigation with bounded destinations, revision hashes, hierarchy-aware active states, and safe external-link projection;
- canonical site identity plus adopter-declared bounded CSS custom-property values rather than a core-owned design vocabulary;
- three-way repository reconciliation that preserves newer CMS edits and leaves composition-owned page leaves alone;
- immutable compare-and-swap release update sets;
- deterministic rebuild ordering and bounded adopter projector hooks;
- first-party `/cms/` workspaces for Pages, Composer, Media, Navigation, Branding, Writing, and SEO with no frontend framework or third-party active runtime dependency.

Portable bootstrap/setup and production-readiness checks are the main remaining core extraction work.

## Product boundary

Reusable core:

- hardened PHP/MySQL runtime and owner authentication;
- canonical authored-content state and revision/provenance records;
- repository page sources, canonical CMS-created pages, and editable page blocks;
- posts and post revisions;
- reusable page-block templates and compositions;
- media catalog, branding, navigation, and SEO state;
- deterministic static projection;
- guarded operations suitable for both human and agent callers.

Adopter-owned state:

- site copy and content seeds;
- repository page/document registry and labels;
- theme assets and visual identity;
- the exact CSS custom properties the CMS is allowed to control;
- article template and other site-specific templates;
- media file bytes and public asset roots;
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

1. Copy `config/site.example.php` to `config/site.php` and define the site name, public origin, editable repository pages, canonical structured documents, writing paths, media roots, optional navigation seed, and any branding identity/token definitions the CMS may control. Site configuration contains public structure, not credentials.
2. Create a MySQL database and import `database/schema.sql`.
3. Put private settings in environment variables or an INI file outside the public root. `database/private-config.example.ini` lists the supported `AINCMS_*` keys. At minimum configure the database, public origin, CMS bootstrap username/password hash, and rate-limit secret.
4. Ensure every configured repository page already exists and mark editable text leaves with stable `data-cms-id` values, for example `<h1 data-cms-id="hero.title">Title</h1>`. Public navigation projection targets `<nav id="site-nav">` when present; identity projection targets the configured brand classes when present.
5. Initialize/reconcile canonical state from the repository:

```bash
php database/reconcile.php initial-import
```

6. Serve the repository with PHP and open `/cms/` directly. The CMS intentionally does not require or add a public-site navigation link to itself.

The first successful bootstrap login persists the owner identity into MySQL. **Pages** edits canonical text leaves. **Composer** discovers top-level structural blocks from configured repository pages, recomposes existing pages, and can create new root-level HTML pages from trusted repository shells and typed templates. **Media** catalogs configured first-party asset roots and supports validated raster uploads. **Navigation** owns the canonical primary navigation. **Branding** manages site identity and only the bounded CSS custom properties explicitly declared by the adopter. **Writing** creates Markdown-backed drafts or published posts. **SEO** manages search/social metadata for managed pages and published articles.

## Repository pages, managed pages, and composition

The source model distinguishes two page classes. Repository pages are declared in `cms.editable_pages`; they are the portable page-source lineage and the only source from which structural templates are harvested. Managed pages are the broader runtime set: repository pages plus canonical CMS-created compositions.

That distinction prevents a generated page from becoming its own repository source on the next reconciliation. CMS-created pages can consume repository templates, participate in Pages/SEO/navigation/hierarchy, and be deterministically rebuilt, but they do not feed themselves back into Git-source or template authority.

The browser never submits structural HTML. Template discovery stores repository-owned top-level blocks in `page_block_templates` together with only the values the block exposes: bounded rich text, safe links, and first-party media references. A composition stores `templateKey`, a stable instance ID, normalized typed values, shell path, browser title, and optional parent in `page_compositions`.

When a composition is saved, editable leaf IDs are namespaced to the template instance. Surviving canonical page-block values are retained when blocks are reordered or recomposed, so structural work does not reset copy that was subsequently edited under **Pages**. The save also carries an expected composition hash; a stale operator receives a conflict rather than overwriting a newer composition.

New CMS-created routes are restricted to bounded lowercase root-level `.html` filenames and trusted repository shells. Parent assignment rejects missing/self/cyclic relationships. Shell title and same-origin canonical/social URL metadata are retargeted to the new route before projection.

## Media model

Media file bytes remain ordinary adopter-owned public assets. `media_library` stores canonical path identity, hashes, dimensions, MIME type, title, alt text, caption, and provenance. Existing JPEG/PNG/WebP/GIF/SVG files under configured `media.public_roots` may be cataloged; Composer can select them through typed media variables.

Uploads are more restrictive than catalog discovery: the CMS accepts JPEG, PNG, WebP, and GIF only, verifies MIME type and decoded image dimensions, enforces `media.max_upload_bytes`, generates its own filename, and writes only inside `media.upload_root`. SVG upload is intentionally not enabled.

## Navigation model

Primary navigation is canonical in `site_navigation`. Browser/agent saves carry the expected navigation hash, preserve the previous canonical value as a revision, and validate item count, stable IDs, labels, and destinations before persistence.

Same-site destinations may use root-relative paths or fragments; protocol-relative/traversal/control-character targets are rejected. External navigation is HTTPS-only and projects with `target="_blank" rel="noopener noreferrer"`. During projection the composition parent graph is loaded once and reused so a child page can mark the nearest navigated ancestor active without a database lookup per file.

Navigation replaces only the contents of a public `<nav id="site-nav">` element when one exists. It does not infer or rewrite arbitrary adopter navigation markup.

## Branding model

Branding is canonical in `site_branding`, but the public core deliberately does not own a universal theme schema. It manages site identity text plus only the CSS custom properties an adopter explicitly declares under `branding.tokens`.

Token definitions name one CSS custom property and one bounded type: `color`, `number`, or `length`. Colors require six-digit hex values; numeric values are clamped to configured ranges; length units come from trusted adopter configuration. Browser saves never submit arbitrary CSS. If a branding stylesheet is configured, the CMS writes a bounded generated override block between its own markers and leaves the rest of the stylesheet intact.

Identity projection changes only the configured brand mark/name classes when present in public HTML.

## Publishing model

`posts` is canonical. Every update to an existing post writes its previous canonical state to `post_revisions` before replacement. Browser/agent saves carry an expected revision hash; a stale caller receives a conflict rather than silently overwriting newer content.

Markdown is intentionally bounded. The core renderer supports headings, paragraphs, ordered/unordered lists, blockquotes, fenced code, emphasis, inline code, and safe links. Source HTML is escaped before controlled markup is produced. An adopter may provide a repository-owned article template through `writing.article_template`; the rendered Markdown body remains the only HTML-valued placeholder.

Drafts have no public article projection. Published posts materialize static HTML and appear in the public post index. Restoring a prior revision makes that snapshot the new current canonical post while preserving the previously current version in history.

## SEO model

SEO state is canonical in `seo_overrides`, not in whichever generated HTML happened to be edited most recently. The editor controls title, description, indexing/follow/archive behavior, preview limits, canonical mode, and inherited/custom Open Graph/Twitter copy.

Custom canonicals are restricted to the configured public origin. During rebuild, pages and compositions are regenerated first, then articles, then saved SEO overrides are applied, so publishing or recomposition cannot erase deliberate metadata.

## Repository reconciliation and rebuild

`php database/reconcile.php <source-ref>` performs the portable authority sequence:

1. bootstrap configured repository page/document state if needed;
2. compare incoming repository candidates with both canonical hashes and prior effective source hashes;
3. accept ordinary source changes only when canonical SQL still matches the prior source;
4. preserve divergent/newer canonical SQL and record the incoming source lineage;
5. skip repository leaf-block reconciliation for pages whose leaf structure is composition-owned, while continuing to reconcile their underlying repository page-source document;
6. apply immutable explicit update sets from `database/content-updates/`;
7. project canonical documents and repository pages;
8. project canonical compositions, including CMS-created pages;
9. project published long-form articles and their public post index;
10. reapply canonical SEO overrides;
11. project canonical primary navigation;
12. project canonical branding identity/token overrides;
13. run adopter projectors at the configured bounded phases.

This prevents a Git pull from silently undoing accepted CMS state and prevents regeneration from silently undoing composition, SEO, navigation, or branding state.

## Rebuild hooks

Adopters may register trusted repository-owned projectors in `config/site.php` under `projection.hooks`. Hooks run only at bounded phases (`before_documents`, `after_documents`, `before_pages`, `after_pages`, `finalize`), their scripts must resolve inside the repository root, and the callable is named explicitly. `after_pages` executes after core page, composition, publishing, SEO, navigation, and branding projection, so discovery/feed/sitemap adapters can consume final public targets.

Hooks are for deterministic presentation/integration work. They must not become alternate stores of authored truth.

## Development status

Pre-release extraction. The repository remains private while the portable setup/bootstrap and production-readiness layer is separated from host-specific behavior. Making the repository public, choosing a license, tagging a release, and adopting this core into a production site remain explicit release boundaries rather than automatic outcomes of development merges.
