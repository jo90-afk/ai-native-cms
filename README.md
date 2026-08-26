# AI Native CMS

AI Native CMS is a static-first publishing system being extracted from a production CMS into a site-neutral public product.

The core design is deliberately conservative: MySQL owns accepted authored state, authenticated operators and agents mutate that state through guarded operations, and public HTML/JSON/XML are deterministic projections. Anonymous readers do not need a database connection.

## Why “AI native”

The product is being shaped so an AI agent can operate the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes should be expressed against typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

That work is incremental. This branch establishes the extraction boundary and the first reusable runtime slice; it does not yet claim feature parity with the reference CMS.

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
- page registry and labels;
- theme assets and visual identity;
- site-specific templates and project integrations;
- deployment credentials and host-specific secrets.

Optional extensions are kept outside the core when they are not general CMS concerns.

## Compatibility strategy

The repository intentionally keeps the reference implementation’s `api/`, `cms/`, and `database/` topology. General features can therefore move between an adopter site and this repository with small, reviewable diffs. Site-specific values must enter through configuration or adapters rather than forks of core behavior.

See `docs/ARCHITECTURE.md` and `docs/UPSTREAMING.md`.

## Current foundation

The first extraction milestone includes:

- a genericized database/runtime security layer derived from the production implementation;
- the site-neutral schema for canonical content, revisions, composition, navigation, branding, media, SEO, provenance, and publishing;
- an example site configuration replacing hard-coded editable-page lists;
- a public-release CI contract that rejects known personal-site identifiers and secrets from product code;
- a Lattice project capsule describing the mandate and readiness conditions.

## Requirements

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ for repository validation

No framework, Node runtime, or database connection is required for anonymous public reads.

## Development status

Pre-release extraction. The repository is private while the production implementation is being separated from adopter-specific content and configuration. Making the repository public is an explicit release boundary, not an automatic outcome of merging development work.
