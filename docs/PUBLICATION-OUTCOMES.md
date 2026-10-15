# Canonical saves and publication outcomes

AI Native CMS treats **canonical persistence** and **public publication** as separate outcomes.

A CMS request may commit accepted state to MySQL and then fail while rebuilding or projecting public files. Once canonical state has committed, the system must never report that request as though nothing was saved, and it must never report publication success until the complete projection boundary succeeds.

## Core contract

Public-state writers acquire the shared publication lock before mutation. The lock lives under a private state root outside the public site. Configure that root with `AINCMS_PUBLICATION_STATE_ROOT`; otherwise the core uses a private sibling `.ai-native-cms` directory.

After a canonical mutation commits, a writer calls `PublicationOutcome::saved(...)`. A successful `contentFinalizePublicProjections()` calls `PublicationOutcome::published()` and clears the request-local state.

If an exception occurs after the canonical save, `runtimePublicationError(...)` returns a `503` response that explicitly reports:

- `saved: true`
- `published: false`
- whether private pending-publication recovery state was recorded
- a non-retryable result until recovery completes

The recovery marker is a private `public-update-pending` directory of kind `cms-publication`. It contains bounded operational context, not authored page/post content or credentials. While that marker exists, follow-on public-content writers fail before mutation.

## Covered core writers

The generic boundary currently covers public canonical mutations from:

- Writing and revision restore
- Branding
- Navigation
- SEO
- Page Composer create/save
- Redirect create/update/delete
- Block preset mutations (writer exclusion during pending recovery)
- Media mutations (writer exclusion during pending recovery)

Redirect changes converge on the same complete public finalization boundary as other public-state writers.

## Host recovery and atomic serving generations

The core owns the save-versus-publication semantics and the private pending-state contract. **How a host rebuilds, snapshots, stages, atomically switches, verifies, or rolls back a serving generation remains a deployment-adapter concern.**

A host adapter that supports immutable serving generations should:

1. acquire the same publication lock or otherwise prove mutually exclusive ownership of the configured private state root;
2. retain `public-update-pending` until canonical MySQL has been rebuilt into a verified public candidate;
3. prepare and verify any immutable snapshot/generation before switching reader traffic;
4. switch atomically where the host supports it;
5. clear pending recovery state only after the adapter can prove publication completion;
6. leave the previous verified generation serving when preparation or switching fails.

Host-specific executable paths, document roots, control-panel commands, and routing selectors belong in deployment adapters/private configuration, not in core CMS code.

## Why this is an upstream primitive

This contract was extracted from production proving-ground behavior, but the upstream implementation deliberately removes site identity, host-vendor assumptions, private paths, and site-specific projection logic. It keeps the reusable invariant: **accepted canonical state and successful public publication are distinct facts, and recovery must preserve that distinction.**
