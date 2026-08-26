# Extraction matrix

This is the bounded frontier for moving the production-proven CMS into the public product. It is a product map, not a backlog; Lattice derives executable work from the active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted | Structure only; no authored seed content |
| Editable-page/document registry | `config/site.php` | adapter | extracted | Never hard-code adopter page or document names in core |
| Page block editing/revisions | `api/content-core.php`, `api/cms-pages.php` | core | extracted | Configured page registry; sanitized bounded rich text; optimistic hashes; revision snapshot |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/content-sync.php` | core | extracted | Preserve canonical/source hashes, three-way reconciliation, immutable compare-and-swap update sets |
| Deterministic page/document projection | `api/content-authority.php`, `database/reconcile.php` | core | extracted | Reconcile -> update sets -> projection; anonymous reads remain static |
| Static rebuild/projector registry | `api/content-rebuild.php` | core + adapter hooks | extracted | Core owns phase order; trusted adopter scripts resolve inside repository root |
| CMS authentication + page editor UI | `api/cms-auth.php`, `cms/` | core | extracted | UI consumes guarded APIs; no second write model or third-party active runtime |
| Posts/drafts/revisions/publishing | `api/post-store.php`, `api/post-renderer.php`, `api/cms-writing.php`, `cms/writing.php` | core | extracted | MySQL canonical; optimistic hashes; bounded Markdown; static projection; adopter owns article template |
| SEO controls | `api/seo.php`, `api/cms-seo.php`, `cms/seo.php` | core | extracted | Canonical overrides reapply after projection; canonicals stay on configured public origin |
| Reusable block templates/composer | `api/composer.php`, `api/composition-store.php`, `api/cms-composer.php`, `cms/composer.php` | core | extracted | Structural HTML stays repository-owned; canonical composition stores template identities + typed values; composed pages bypass shell block reconciliation |
| Media library | `api/media.php`, `api/cms-media.php`, `cms/media.php` | core | extracted | Canonical metadata + adopter-owned bytes; configured public roots; validated raster uploads only |
| Navigation | `api/cms-navigation.php`, CMS navigation UI | core | next | Site-wide state canonical in SQL; projection hook is generic |
| Branding | `api/cms-branding.php`, CMS branding UI | core | next | Product stores bounded design tokens, not one site’s CSS vocabulary |
| New-page hierarchy | composition APIs/UI | core | next | Build on merged composition primitives; validate parents and reject cycles before projection/navigation changes |
| Production readiness | `api/cms-readiness.php` | core + adapter checks | planned | Separate portable checks from host-specific checks |
| Shared-host bootstrap/setup | `database/bootstrap.php`, `setup/` | deployment adapter | planned | Generic defaults; no hosting-vendor identity in core |
| Host repository updater | deployment adapter | adapter | later | Keep deployment authority explicit and host-specific |
| Newsletter/subscription | extension | optional extension | later | Generic capability; not required for CMS core readiness |
| Poetry-specific layout/visual projection | adopter extension | site/content-type adapter | excluded from core | Useful architecture may upstream; authored semantics remain adopter-owned |
| Project-record/Lattice public views | adopter extension | site integration | excluded from core | Lattice governs development; CMS must not require Lattice as public runtime |
| Personal portfolio content/theme | adopter repository | site-only | excluded | Never upstream authored content or identity |

## Immediate frontier after M-005

Add new-page hierarchy, canonical navigation, and bounded branding together. They form the next coherent site-wide layer: a new composed page needs a validated parent/navigation relationship, while site-wide branding must reapply deterministically across both repository pages and composed projections without owning adopter-specific CSS structure.
