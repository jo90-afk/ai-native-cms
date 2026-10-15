# AI Native CMS 0.1.0-rc.5

`0.1.0-rc.5` is the third public release candidate of AI Native CMS. It keeps schema 10 and the existing explicit 8→9→10 upgrade path, while promoting the development work accepted after rc.4 into a new published prerelease.

## What changed since rc.4

### Public Markdown and LLM discovery

- Eligible canonical public pages now receive deterministic adjacent Markdown alternates.
- Public HTML advertises its Markdown alternate and compact LLM discovery relation idempotently.
- `llms.txt` prefers verified Markdown alternates while keeping expanded `llms-full.txt` optional.
- Discovery revalidates same-origin canonicals, source paths, symlinks, noindex state, authored-file collisions, and cleanup of stale generated alternates.
- Fresh-site setup normalizes managed-page canonical metadata to the configured public origin before strict discovery.

### Publication correctness

- Authenticated public-content writers coordinate through one private publication lock.
- Canonical MySQL persistence and successful public publication are now separate observable outcomes.
- If a projection fails after canonical state has committed, the request reports `saved=true` and `published=false` instead of claiming the mutation was unsaved or published.
- A bounded private `cms-publication` pending state blocks subsequent public writers until recovery is resolved.
- Successful complete public finalization clears request-local saved state.
- Writing, Branding, Navigation, SEO, Page Composer, Redirects, Blocks, and Media participate in the publication coordination boundary appropriate to their mutation semantics.
- Redirect writes now converge on complete public finalization rather than bypassing it with a direct redirect-only projection.
- The reusable core uses the generic `AINCMS_PUBLICATION_STATE_ROOT` contract; adopter-specific host paths and serving-generation mechanics remain deployment-adapter concerns.

## Compatibility

- Fresh installs remain schema 10.
- Published `0.1.0-rc.3` remains the frozen schema-8 upgrade fixture.
- Sequential upgrades remain 7→8→9→10.
- No new database migration is required when upgrading from rc.4 to rc.5 because the canonical schema version is unchanged.

## Release assurance

The rc.5 publication candidate must pass the cumulative repository contract, deterministic build checks, the dedicated publication-outcome behavior suite, a clean schema-10 packaged rehearsal, and the published rc.3 → v9 → v10 upgrade rehearsal on the exact reviewed head before publication.

The release remains source-available under Apache License 2.0 subject to Commons Clause License Condition v1.0. It is a prerelease intended for evaluation, site builds, and governed production trials with tested backups and explicit review of deployment/provider configuration.
