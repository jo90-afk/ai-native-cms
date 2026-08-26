# Extraction matrix

This is the bounded frontier for moving production-proven CMS behavior into a reusable product. It is a product map, not a backlog; Lattice derives executable work from active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted — v8 | Structure only; no authored seed content |
| Explicit schema upgrades | `database/migrations/` | core compatibility | extracted — 7→8 | Versioned CLI migration; backup/rollback documented; never route through bootstrap repair |
| Repository page/document registry | `config/site.php` | adapter | extracted | Never hard-code adopter page/document names in core; keep repository source distinct from CMS-generated pages |
| Page block editing/revisions | `api/content-core.php`, `api/cms-pages.php` | core | extracted | Managed-page graph; sanitized bounded rich text; optimistic hashes; revision snapshot |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/content-sync.php` | core | extracted | Preserve canonical/source hashes, three-way reconciliation, immutable compare-and-swap update sets; browser endpoints do not promote repository source into SQL |
| Deterministic page/document projection | `api/content-authority.php`, `database/reconcile.php` | core | extracted | Explicit reconciliation → accepted state → projection; anonymous reads remain static |
| Static rebuild/projector registry | `api/content-rebuild.php` | core + adapter hooks | extracted | One finalization boundary; core owns phase order; trusted adopter scripts resolve inside repository root; `after_seo` supports sitemap/discovery adapters |
| CMS authentication + page editor UI | `api/cms-auth.php`, `cms/` | core | extracted | UI consumes guarded APIs; no second write model or third-party active runtime |
| Posts/drafts/revisions/publishing | `api/post-store.php`, `api/post-renderer.php`, `api/cms-writing.php`, `cms/writing.php` | core | extracted | MySQL canonical; optimistic hashes; bounded Markdown; static projection; adopter owns article template; published slug history uses redirect authority |
| SEO controls | `api/seo.php`, `api/cms-seo.php`, `cms/seo.php` | core | extracted | Canonical overrides reapply after projection; canonicals stay on configured public origin; discovery adapters consume final SEO state |
| Canonical redirects | `api/redirects.php`, `api/cms-redirects.php`, `cms/redirects.php` | core | extracted | SQL authority; same-site bounded paths/statuses; optimistic record revisions; global graph serialization; collision/conflict/cycle rejection; configured system aliases read-only |
| Static redirect runtime | `__redirect.php`, `__redirect-map.php` | core projection | extracted | Generated deterministic map; anonymous routing is database-free; unknown paths 404; query/status semantics stay in projected runtime |
| Reusable block templates/composer | `api/composer.php`, `api/composition-store.php`, `api/cms-composer.php`, `cms/composer.php` | core | extracted | Structural HTML stays repository-owned; canonical composition stores template identities + typed values; generated pages consume but do not author templates |
| Media library | `api/media.php`, `api/cms-media.php`, `cms/media.php` | core | extracted | Canonical metadata + adopter-owned bytes; configured public roots; validated raster uploads only |
| New-page hierarchy | `api/composition-store.php`, Composer UI | core | extracted | CMS-created root-level routes use trusted shells/templates; validate parents and reject cycles; never feed generated routes back into repository lineage |
| Navigation | `api/navigation.php`, `api/cms-navigation.php`, `cms/navigation.php` | core | extracted | Ordered canonical SQL state; bounded safe destinations; optimistic hash; hierarchy-aware active projection into `#site-nav` only |
| Branding | `api/branding.php`, `api/cms-branding.php`, `cms/branding.php` | core + adapter token definitions | extracted | Core stores identity + adopter-declared bounded CSS custom properties, not one site’s CSS vocabulary |
| Portable database bootstrap | `database/bootstrap-core.php`, `database/bootstrap.php` | core | extracted | CLI-only schema + first owner; derive schema contract from `schema.sql`; no adopter content seed; no owner overwrite; no implicit migration |
| Production readiness | `api/readiness.php`, `api/cms-readiness.php`, `database/readiness.php`, `cms/readiness.php` | core + adapter checks | extracted | Read-only actionable report; no mutation or secret/grant-content disclosure; host-specific checks enter only through trusted repository-owned adapters |
| Browser credential-writing setup | deployment adapter | adapter | excluded from core | Core bootstrap does not write database credentials or expose a provider-specific setup surface |
| Redirect interception contract | `docs/DEPLOYMENT-ADAPTERS.md` | deployment adapter contract | extracted | Serve real files/dirs first; unresolved requests use static redirect projection; generated map is not a public asset; host layer does not become CMS authority |
| Apache public transport reference | `adapters/apache/public.htaccess.example` | reference adapter | extracted | Conservative versionless-asset caching, HTML revalidation, DEFLATE, unresolved redirect fallback; merge deliberately with adopter host/security rules |
| Apache private/preview reference | `adapters/apache/private.htaccess.example` | reference adapter | extracted | Same redirect/compression behavior with global `no-store, private`; no public max-age leakage |
| Other server/CDN adapters | `adapters/<target>/` | optional adapters | later | nginx/Caddy/CDN/edge implementations may follow the same contract; no platform is required by core |
| Internal release-candidate package | `VERSION`, `release/release.json`, `tools/build_release.py` | release engineering | extracted — rc.2 | Deterministic source ZIP + manifest + SHA256; exact reviewed-head provenance; include schema-v8 migration/redirect runtime/reference adapters; exclude governance/runtime/adopter state; preserve `public:false` and `licenseSelected:false` |
| Installation/backup/rollback guidance | `docs/INSTALLATION.md`, `docs/RELEASE.md` | release engineering | extracted | Fresh install is schema/owner bootstrap → explicit canonical import → readiness; v7 upgrades use explicit migration; rollback pairs database and code state |
| Host repository updater / automatic deployment | deployment adapter | adapter | later / optional | Keep provider authority explicit and host-specific; not required for CMS core or first public source release |
| Newsletter/subscription | extension | optional extension | later | Generic capability; not required for CMS core readiness |
| Poetry-specific layout/visual projection | adopter extension | site/content-type adapter | excluded from core | Useful architecture may upstream; authored semantics remain adopter-owned |
| Project-record/Lattice public views | adopter extension | site integration | excluded from core | Lattice governs development; CMS must not require Lattice as public runtime |
| Personal portfolio content/theme | adopter repository | site-only | excluded | Never upstream authored content or identity |

## Extraction status after M-010

The reusable core extraction now includes the production-proven schema-v8 redirect/projection hardening and a portable explicit 7→8 migration. The schema-v8 `0.1.0-rc.2` source candidate includes the database-free redirect runtime and optional reference deployment adapters while retaining the same private/unlicensed distribution boundary.

Cumulative run #76 passed M-001 through M-010 on verification commit `7f141f06390af75ad8fb47ba6d613cfa106d1372`. Its private rc.2 artifact was directly inspected: exact source provenance, checksum, embedded/external manifest equality, schema 8, migration/runtime/adapter inclusion, and exclusion/residue boundaries all passed.

The remaining extraction question is a **fresh production proving-ground parity check**, not another assumed core milestone. Compare current production `main` against the recorded PR #49/#50 parity point and classify intervening work as core / adapter / site-only. A material reusable core delta reopens extraction; adapter/site-only work does not automatically block release.

If no reusable core delta remains after that check, the project returns to the Principal release gate. Repository visibility, license selection, tag/GitHub Release creation, package publication, production deployment, credentials, and production adoption remain explicit separate decisions.