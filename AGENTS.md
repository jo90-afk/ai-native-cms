# AI Native CMS agent contract

Read this before changing the repository.

## Source-of-truth order

1. `database/schema.sql` + explicit migrations define database structure.
2. Canonical authored content/state lives in MySQL and is mutated through guarded CMS/API contracts.
3. Repository files own application code, structural templates/shells, configuration examples, migrations, tests, documentation, and deployment adapters.
4. Static HTML/JSON/XML and redirect maps are deterministic projections. Never treat generated output as reverse source authority.
5. Host/provider configuration is an adapter concern and must not silently become CMS authority.

When sources disagree, do not guess. Inspect the relevant schema, API/store, tests, and architecture documentation before editing.

## Working rules

- Make repository changes on a branch and keep diffs reviewable.
- Preserve authentication, CSRF, same-origin, rate-limit, expected-hash, revision, provenance, schema-version, and path-containment guards.
- Do not introduce a second content/state model for agents. Human and agent writers use the same canonical contracts.
- Do not promote generated/live files back into repository source authority from browser code.
- Structural HTML/template/CSS changes belong in the repository. CMS content and typed composition values belong in canonical state.
- A schema change requires an explicit migration, tests, current-schema guards, upgrade/rollback documentation, and release metadata review. `bootstrap --repair` is not a migration mechanism.
- Never request, print, commit, relocate, or synthesize real credentials. Use documented environment variables and example placeholders only.
- Do not weaken a failing contract just to make CI green. Determine whether the behavior or the historical assertion is wrong and preserve the real invariant.
- Update relevant tests and documentation whenever governed behavior changes.
- Keep anonymous public delivery database-free.
- Keep provider-specific logic behind adapters.

## Change packets

Before implementing, identify:

- goal and user-visible consequence;
- mutation class: repository / canonical SQL / generated projection / host adapter;
- files and contracts expected to change;
- schema impact and migration requirement;
- security/authority boundaries involved;
- acceptance tests;
- rollback path.

### Design / interface work

Repository-owned structure, templates, CSS, JavaScript behavior, accessibility, and responsive layout may change on a branch. Do not move arbitrary structural HTML into browser-submitted CMS state. Branding exposed to the CMS remains bounded by adopter-declared tokens.

### Content work

Use the CMS/content APIs for accepted canonical content. Repository-authored page/source changes are proposals that enter canonical state through reconciliation. Do not edit generated public HTML and call it canonical content.

### Feature work

Inspect the existing store/API/UI path first. Extend the current authority model rather than adding a parallel one. Add focused behavior tests plus a structural contract when the invariant should survive refactors.

### Bug fixes

Reproduce the violated invariant, fix the narrowest authority layer that owns it, and add regression coverage. Avoid compensatory patches in generated output.

### Release work

Run the full cumulative gate on the exact candidate head, build the deterministic candidate, inspect provenance/checksum/manifest/residue/license contents, and repeat the production proving-ground parity check before publication.

## LLM collaboration pattern

A strong iteration is:

1. inspect current state;
2. state the bounded intended change;
3. branch;
4. implement through the owning authority layer;
5. run targeted tests, then cumulative CI at a release checkpoint;
6. review the diff for authority/security drift;
7. update durable docs/state;
8. merge only verified work.

Conversation memory is context, not authority. The repository, canonical database contracts, tests, migrations, and recorded project state win.

See `docs/LLM-COLLABORATION.md` for operator-facing examples and `docs/ARCHITECTURE.md` for the system model.
