# Extraction matrix

This is the bounded frontier for moving the production-proven CMS into the public product. It is a product map, not a backlog; Lattice derives executable work from the active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted | Structure only; no authored seed content |
| Repository page/document registry | `config/site.php` | adapter | extracted | Never hard-code adopter page or document names in core; keep repository source distinct from CMS-generated pages |
| Page block editing/revisions | `api/content-core.php`, `api/cms-pages.php` | core | extracted | Managed-page graph; sanitized bounded rich text; optimistic hashes; revision snapshot |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/content-sync.php` | core | extracted | Preserve canonical/source hashes, three-way reconciliation, immutable compare-and-swap update sets |
| Deterministic page/document projection | `api/content-authority.php`, `database/reconcile.php` | core | extracted | Reconcile -> update sets -> projection; anonymous reads remain static |
| Static rebuild/projector registry | `api/content-rebuild.php` | core + adapter hooks | extracted | Core owns phase order; trusted adopter scripts resolve inside repository root |
| CMS authentication + page editor UI | `api/cms-auth.php`, `cms/` | core | extracted | UI consumes guarded APIs; no second write model or third-party active runtime |
| Posts/drafts/revisions/publishing | `api/post-store.php`, `api/post-renderer.php`, `api/cms-writing.php`, `cms/writing.php` | core | extracted | MySQL canonical; optimistic hashes; bounded Markdown; static projection; adopter owns article template |
| SEO controls | `api/seo.php`, `api/cms-seo.php`, `cms/seo.php` | core | extracted | Canonical overrides reapply after projection; canonicals stay on configured public origin |
| Reusable block templates/composer | `api/composer.php`, `api/composition-store.php`, `api/cms-composer.php`, `cms/composer.php` | core | extracted | Structural HTML stays repository-owned; canonical composition stores template identities + typed values; generated pages consume but do not author templates |
| Media library | `api/media.php`, `api/cms-media.php`, `cms/media.php` | core | extracted | Canonical metadata + adopter-owned bytes; configured public roots; validated raster uploads only |
| New-page hierarchy | `api/composition-store.php`, Composer UI | core | extracted | CMS-created root-level routes use trusted shells/templates; validate parents and reject cycles; never feed generated routes back into repository lineage |
| Navigation | `api/navigation.php`, `api/cms-navigation.php`, `cms/navigation.php` | core | extracted | Ordered canonical SQL state; bounded safe destinations; optimistic hash; hierarchy-aware active projection into `#site-nav` only |
| Branding | `api/branding.php`, `api/cms-branding.php`, `cms/branding.php` | core + adapter token definitions | extracted | Core stores identity + adopter-declared bounded CSS custom properties, not one site’s CSS vocabulary |
| Production readiness | `api/cms-readiness.php` | core + adapter checks | next | Separate portable checks from host-specific checks; report actionable blockers without mutating production state |
| Shared-host bootstrap/setup | `database/bootstrap.php`, `setup/` | deployment adapter | next | Safe first-run schema/owner initialization; generic defaults; no hosting-vendor identity in core |
| Host repository updater | deployment adapter | adapter | later | Keep deployment authority explicit and host-specific |
| Newsletter/subscription | extension | optional extension | later | Generic capability; not required for CMS core readiness |
| Poetry-specific layout/visual projection | adopter extension | site/content-type adapter | excluded from core | Useful architecture may upstream; authored semantics remain adopter-owned |
| Project-record/Lattice public views | adopter extension | site integration | excluded from core | Lattice governs development; CMS must not require Lattice as public runtime |
| Personal portfolio content/theme | adopter repository | site-only | excluded | Never upstream authored content or identity |

## Immediate frontier after M-006

Extract portable bootstrap/setup and production readiness together. They form the final coherent core-operability layer before public-release decisions: a new adopter needs a safe way to initialize schema/owner state, and the product needs to distinguish portable CMS readiness from host-specific deployment checks without embedding one provider’s assumptions.
