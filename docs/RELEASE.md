# Release-candidate process

AI Native CMS `0.1.0-rc.3` is an **internal release candidate**, not a public release. Schema version is **8**. A candidate is a reproducible review artifact: it does not make the repository public, create a Git tag or GitHub Release, publish a package, authorize deployment, or adopt the core into production.

## Candidate identity

`VERSION` defines the candidate version. `release/release.json` defines product/channel/schema/runtime metadata, license selection, and explicit distribution-boundary flags. `tools/build_release.py` refuses mismatched version/schema/license metadata or a candidate whose metadata crosses the private-public boundary.

## License

AI Native CMS uses **Apache License 2.0 subject to the Commons Clause License Condition v1.0**. This is a **source-available** license, not an OSI-approved open-source license. It permits use, modification, derivative works, and commercial use while withholding the right to sell AI Native CMS itself, or a product/service whose value derives entirely or substantially from AI Native CMS functionality. Attribution and license notices must be preserved according to the terms.

The candidate includes `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE`. The practical-intent summary in `LICENSE` is explanatory; the binding terms are Apache 2.0 plus the Commons Clause condition.

## Deterministic build

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits:

- `ai-native-cms-0.1.0-rc.3.zip`
- `ai-native-cms-0.1.0-rc.3.manifest.json`
- `ai-native-cms-0.1.0-rc.3.sha256`

Sorted file order, fixed ZIP metadata, deterministic compression, and exact source-ref provenance make repeated builds byte-for-byte comparable.

The manifest records product/version/channel, exact source revision, schema version, runtime requirements, license/distribution flags, package root, and per-file byte length/SHA256. The outer checksum hashes the complete ZIP.

## Candidate contents and exclusions

The candidate includes reusable runtime/product code, starter public site, `README.md`, `AGENTS.md`, `SECURITY.md`, license/NOTICE files, release metadata, `api/`, `cms/`, generic configuration examples, schema/bootstrap/migrations/reconciliation/readiness, SEO audit/projection support, redirect runtime/map seed, portable docs, and optional deployment adapter examples.

It excludes `.git/`, `.github/`, `.lattice/`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, populated INI files, symlinks, credentials, and known adopter-specific residue.

Schema-v8 candidates include the explicit 7→8 migration plus canonical/static redirect machinery. Reference deployment adapters remain examples, not automatic deployment behavior.

## Release verification gate

Every reviewed candidate head must pass the cumulative product gate and the clean release rehearsal.

The cumulative gate covers all historical structural contracts, executable PHP behavior, PHP/JavaScript/Python syntax, deterministic candidate build, and private artifact upload.

The clean rehearsal is implemented by `.github/workflows/release-rehearsal.yml` and `tests/release_rehearsal.sh`. It runs from the **packaged candidate**, not a preconfigured source checkout, against a clean MySQL 8 service and proves:

1. two builds from the same exact source revision are byte-identical;
2. package provenance, version/schema, license/NOTICE, exclusions, residue rules, and `public:false` remain correct;
3. `setup/site.php` creates only public non-secret adopter configuration;
4. shipped bootstrap creates schema 8 plus a persisted owner, then explicit reconciliation initializes canonical repository content;
5. readiness reaches zero blocking failures;
6. authenticated HTTP login and state-derived onboarding work from the clean candidate;
7. the starter site is complete without hand-authoring initial HTML;
8. a representative agent-owned repository structural/design change and small feature can be isolated on a Git branch and validated without crossing canonical-state boundaries;
9. a representative canonical content change uses the content-authority store with expected-hash protection and deterministic projection;
10. canonical redirect state projects a database-free anonymous routing map/runtime;
11. paired filesystem + database backup/restore removes rehearsal mutations and returns the installation to green readiness.

This rehearsal is a release assurance test, not a deployment or publication action.

## M-014 evidence

The first complete M-014 implementation head `43a981e4d995294c48f4022fc724bb2c48392da4` passed:

- clean-candidate push run `33006245237`;
- cumulative PR validation run **#189** (`33006335119`);
- PR-triggered clean-candidate run `33006335107`.

The rehearsal produced two identical `0.1.0-rc.3` candidates containing 96 packaged files. The candidate ZIP SHA256 was `bf5aca65a8339db1bc153dad120ebc5caa9ccf9bc05825eb7a6bc9b611ab3c9b`. Before and after paired restore, readiness reported 18 pass, 2 expected nonblocking warnings, and 0 blocking failures. Authenticated onboarding reported all five required steps complete and all six starter files present.

These values identify rehearsal evidence for the implementation head. The final documentation head must independently pass the cumulative and rehearsal workflows before merge.

## Proving-ground parity gate

Immediately before release-gate merge and again before any publication decision, compare the production proving ground with the recorded parity point. A material reusable **core** delta reopens delegated extraction. Governance/evidence-only, host-only, or adopter-specific changes do not automatically block release.

The post-rehearsal proving-ground check found only a governance/evidence-only update after the reusable SEO parity merge; no newer reusable CMS-core delta was present at that check.

## Pre-release engineering blockers

M-011 through M-014 now have technical evidence:

- friendly onboarding and coherent starter site;
- understandable/reversible repository-to-host operations;
- governed LLM-assisted design/content/feature iteration;
- clean empty-site candidate rehearsal with deterministic build, real database bootstrap, authenticated onboarding, canonical content mutation, redirect transport boundary, and paired rollback.

After the final M-014 documentation head is green and merged, the project is at the **Principal publication gate**.

## Public-distribution boundary

The selected license does not authorize publication. `release/release.json` remains `public:false`.

The following remain separate Principal decisions:

1. changing repository visibility;
2. creating a public tag/GitHub Release;
3. publishing a package/download;
4. deploying or adopting the core in production.

Until one or more of those actions is explicitly authorized, `0.1.0-rc.3` remains a private internal release candidate.
