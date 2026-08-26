# Release-candidate process

AI Native CMS `0.1.0-rc.3` is an **internal release candidate**, not a public release. It opens a final pre-release usability/governance cycle after the schema-v8 rc.2 extraction and parity work. `0.1.0-rc.2` remains historical evidence for the verified schema-v8 core and deployment-adapter line.

A candidate is a reproducible review artifact. It does not make the repository public, create a Git tag or GitHub Release, publish a package, authorize deployment, or adopt the core into production.

## Candidate identity

`VERSION` defines the semantic candidate version. `release/release.json` defines product/channel/schema/runtime metadata, license selection, and explicit distribution-boundary flags. `tools/build_release.py` refuses mismatched version/schema/license metadata or a candidate whose metadata crosses the private-public boundary.

## License

The license is now selected but the candidate remains private and unpublished.

AI Native CMS uses **Apache License 2.0 subject to the Commons Clause License Condition v1.0**. This is a **source-available** license, not an OSI-approved open-source license. It permits use, modification, derivative works, and commercial use while withholding the right to sell AI Native CMS itself, or a product/service whose value derives entirely or substantially from AI Native CMS functionality. Attribution and license notices must be preserved according to the license terms.

The candidate includes:

- `LICENSE` — the combined project license and Commons Clause condition;
- `LICENSE-APACHE-2.0.txt` — the Apache 2.0 base terms;
- `NOTICE` — project attribution notice.

The practical-intent summary in `LICENSE` is explanatory only; the binding terms are Apache 2.0 plus the Commons Clause condition.

## Build

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits deterministic files under `dist/`:

- `ai-native-cms-0.1.0-rc.3.zip`
- `ai-native-cms-0.1.0-rc.3.manifest.json`
- `ai-native-cms-0.1.0-rc.3.sha256`

Sorted file order, fixed ZIP metadata, deterministic compression, and exact source-ref provenance make repeated builds byte-for-byte comparable.

## Candidate contents

The source candidate includes reusable runtime/product code, `README.md`, `SECURITY.md`, license/NOTICE files, release metadata, `api/`, `cms/`, generic config examples, schema/bootstrap/migrations/reconciliation/readiness, root redirect runtime/map seed, portable `docs/`, and optional deployment adapter examples under `adapters/`.

It excludes `.git/`, `.github/`, `.lattice/`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, populated INI files, symlinks, credentials, and known reference-adopter residue.

Schema-v8 candidates must include `api/redirects.php`, `api/cms-redirects.php`, `cms/redirects.php`, `database/migrations/7-to-8.php`, `__redirect.php`, and `__redirect-map.php`.

Reference deployment adapters remain examples, not automatic deployment. The candidate includes `adapters/apache/public.htaccess.example`, `adapters/apache/private.htaccess.example`, and `docs/DEPLOYMENT-ADAPTERS.md` so an adopter can implement unresolved-path redirect interception and conservative caching/compression without making Apache a core runtime assumption.

## Manifest

The embedded/external `RELEASE-MANIFEST.json` records product/version/channel, exact reviewed source revision, schema version, runtime requirements, license/distribution flags, package root, and per-file byte length/SHA256. The outer `.sha256` hashes the complete ZIP.

## Verification

CI must pass the cumulative product gate on the exact candidate head. For the review artifact verify at minimum:

1. CI is green on the exact reviewed head;
2. ZIP SHA256 matches the emitted checksum;
3. embedded and external manifests are identical and record that head;
4. schema version is 8 and the explicit 7→8 migration plus redirect runtime are packaged;
5. `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` are packaged and metadata records the selected source-available license;
6. reference deployment adapters are packaged but no host-specific credentials/provider assumptions are embedded;
7. no adopter/local configuration, private residue, or secret material exists;
8. installation/upgrade documentation accurately covers fresh install, explicit migration, readiness, source reconciliation, backup, and paired rollback;
9. deployment documentation accurately separates portable CMS authority from host interception/cache/compression behavior;
10. the final onboarding, repository operations, and LLM-collaboration rehearsals have been completed before any public tag/publication decision.

The CI artifact remains short-lived and private. It is evidence for review, not publication.

## Final pre-release blockers

A public release is blocked until all of the following are satisfied:

- **Friendly onboarding and site build-out:** a new adopter can move from repository checkout to a coherent starter site without understanding internal architecture or editing database state by hand. Credential/bootstrap security remains CLI-owned; authenticated browser onboarding uses existing canonical mutation contracts.
- **GitHub-to-host operations:** documentation covers repository initialization, branches/PRs, configuration ownership, database backup/migration, deployment-adapter selection, push/pull-to-host workflows, rollback, and provider-neutral deployment expectations.
- **LLM collaboration without governance loss:** the repository ships a concise agent contract and practical workflow for iterative design, content, and feature work. Agents must respect schema/migration boundaries, canonical SQL vs repository source, structural-template ownership, secret boundaries, tests, review, and release parity.
- **Empty-site rehearsal:** perform the documented path from a clean repository/candidate to a hosted, usable site, including one representative LLM-assisted design/content/feature iteration and rollback/recovery check.

Completion of these blockers creates a fresh inspected rc.3 release candidate and returns the project to the Principal publication gate.

## Public-distribution boundary

The license decision has been made, but `release/release.json` remains `public:false` and no public-release authorization is implied. Changing repository visibility, creating a tag/GitHub Release, publishing a package/download, or deploying/adopting the core remain separate Principal decisions.

## Source-parity gate

Before a future candidate is promoted to a public-release decision, compare the production proving-ground repository with the recorded parity point. A material reusable **core** delta reopens delegated extraction work. Host/transport adapters and site-only changes do not automatically block release.

Until the pre-release blockers, parity review, and explicit publication decisions are complete, `0.1.0-rc.3` remains an internal review identity only.
