# Collaborating with an LLM without losing CMS governance

AI Native CMS is designed to work well with coding and content agents, but the agent is not a new authority layer. The safest and most useful model is simple: an LLM can inspect, propose, implement, test, and explain changes, while the repository and canonical CMS contracts continue to decide what state is durable.

This guide applies whether you use ChatGPT, Codex, Claude, another coding agent, or a mix of tools.

## The four kinds of state

Before asking an agent to change something, identify which kind of state owns it.

### Repository-owned state

Use Git branches and pull requests for:

- PHP/JavaScript/Python application code;
- structural HTML shells and templates;
- CSS and responsive/accessibility behavior;
- configuration examples and adopter-owned `config/site.php`;
- database schema and explicit migrations;
- tests and contracts;
- documentation;
- deployment adapters.

These changes should be reviewable as diffs.

### Canonical CMS state

Use the CMS or its guarded APIs for accepted authored state such as:

- page copy;
- typed composition values;
- posts and post revisions;
- media metadata;
- navigation;
- branding values exposed by configured tokens;
- SEO overrides;
- governed redirects.

An agent should not create a private shadow database or bypass the same validation/revision rules a human user receives.

### Generated public projection

Static HTML, JSON, XML, indexes, and redirect maps are outputs. They may be inspected to diagnose a problem, but they are not a reverse source of truth.

If an agent fixes a generated file directly, the next rebuild may erase the fix. Find the repository or canonical source that owns the projection instead.

### Host/provider state

Web-server rewrites, caching, secret placement, deployment credentials, document-root configuration, and provider-specific operations belong to deployment adapters and operator configuration. Do not copy live host state into CMS authority.

## Start every substantial request with inspection

A good request tells the agent to inspect before editing. For example:

> Inspect the current implementation, relevant tests, architecture contract, and source-of-truth boundary for this feature. Then make the smallest reviewable change that satisfies the goal. Preserve canonical SQL/repository/projection boundaries, add regression coverage, and summarize durable state changes at the end.

This reduces a common failure mode where an agent invents a new mechanism because it did not discover the one already present.

## Request packets

The following patterns are deliberately short enough to reuse.

### Interface / design iteration

> Improve [screen/component] for [specific user consequence]. Treat structural templates, CSS, JavaScript behavior, responsive rules, and accessibility as repository-owned. Keep CMS-authored content and branding tokens in their existing canonical contracts. Work on a branch, preserve security and expected-hash behavior, add targeted regression coverage, and show the user-visible consequence of the diff.

Use this for layout, responsive behavior, navigation interaction, CMS workspace design, onboarding, and accessibility.

### Content iteration

> Revise [content/page/post] for [goal]. First identify whether this content is repository source or canonical CMS content. Preserve revision/provenance behavior. Do not edit generated public HTML as the durable source. If repository source changes, explain how it enters canonical state through reconciliation. If canonical content changes, use the existing CMS/store contract.

Use this when an agent is drafting or editing copy. It prevents a content task from accidentally becoming an architecture change.

### Feature work

> Add [feature]. Inspect the existing store/API/UI path and extend the owning authority layer instead of creating a parallel state model. State schema impact explicitly. If schema changes, add an explicit migration and rollback guidance. Preserve auth/CSRF/origin/rate-limit/schema-version/revision guards. Add focused behavior tests and a durable structural contract for the invariant. Work on a branch and do not merge until the cumulative gate is green.

### Bug fix

> Reproduce [bug] as an invariant failure. Identify whether the defect belongs to repository code, canonical state, projection, or host adapter. Fix the owning layer, not the generated symptom. Add regression coverage and keep unrelated behavior unchanged.

### Schema / migration work

> Change the canonical schema from version N to N+1 for [reason]. Add a versioned explicit migration with preflight checks and rollback/backup guidance. Update all mutation surfaces to require the new schema where needed. Do not use bootstrap repair as migration. Verify fresh install and upgrade paths separately.

### Release work

> Prepare the next internal release candidate from the exact reviewed head. Run the cumulative gate, build the deterministic artifact twice, verify source revision/checksum/manifest equality, inspect candidate contents for secrets/adopter residue/license files, repeat the production proving-ground parity check, and stop before visibility/tag/publication/deployment boundaries unless explicitly authorized.

## Iterative site design with an LLM

The repository/CMS split is especially useful for design collaboration.

Use repository changes for the visual system itself:

- shells and page structures;
- reusable block templates;
- layout CSS;
- responsive breakpoints;
- interaction code;
- accessible markup;
- the set and bounds of branding tokens.

Use canonical CMS state for the site-specific choices inside that system:

- page text;
- which trusted templates are composed on a page;
- media references;
- nav order/labels;
- selected branding-token values;
- SEO and redirects.

This means you can ask an LLM for a significant visual redesign without allowing it to turn arbitrary generated HTML into opaque database state. The structural diff remains reviewable in Git; the authored site state remains editable in the CMS.

## Iterative content with an LLM

An agent can be useful for drafting, restructuring, metadata, and editorial passes. Preserve these rules:

- accepted CMS content must still pass the normal sanitization and revision contracts;
- repository source is a proposal/fixture until reconciliation accepts it;
- do not let an agent bulk-rewrite canonical content merely because it found an older repository copy;
- when new information contradicts accepted content, make the change explicit and preserve the normal revision/provenance history;
- keep content and structural design changes in separate commits when practical so each can be reviewed independently.

## Iterative feature development

For a small feature, an LLM should usually touch one vertical slice: store/core logic, guarded API, UI, tests, and documentation. Large cross-cutting changes should be decomposed into milestones with separately verifiable boundaries.

Ask the agent to identify:

1. what owns the state;
2. what can mutate it;
3. what projection consumes it;
4. what can be rolled back;
5. what tests prove the invariant.

If it cannot answer those questions, it has not inspected enough of the system yet.

## Protecting credentials and production

Do not paste real database passwords, API keys, SSH private keys, hosting credentials, or other secrets into prompts when they are not required. Agents should work with environment-variable names, example configuration, or provider-side secret stores.

Repository automation must not:

- commit secrets;
- copy secret files into public roots;
- print secret values in logs;
- create a browser credential-writing surface as a convenience feature;
- deploy to production merely because implementation tests pass.

Deployment and public release remain explicit operator decisions.

## GitHub is the durable collaboration record

For repository changes, use a branch and PR even when an LLM performs nearly all implementation. The PR gives you:

- a bounded diff;
- a place for human or agent review;
- CI evidence tied to an exact head;
- a durable explanation of why the change exists;
- a reversible merge boundary.

For a long-running project, prefer repository state over relying on one chat thread to remember decisions. Important invariants should live in tests, architecture/docs, migrations, `AGENTS.md`, and the Lattice project capsule rather than only in conversation history.

## A safe default workflow

1. Describe the consequence you want, not a guessed implementation.
2. Tell the agent to inspect current state and identify authority boundaries.
3. Let it create a branch and implement a bounded slice.
4. Run targeted tests during iteration.
5. Review the diff and any migration/projection implications.
6. Run the cumulative gate before a milestone/release merge.
7. Record durable state in the repository.
8. Merge the verified head.
9. Deploy separately, with backup/rollback available.

That workflow preserves the main advantage of LLM collaboration—fast iteration—without turning conversational context into an unreviewable source of truth.
