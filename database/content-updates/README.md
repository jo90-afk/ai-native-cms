# Content update sets

Use this directory only for deliberate canonical supersession that must survive ordinary repository reconciliation.

Each `*.php` file returns one immutable array:

```php
<?php
return [
    'id' => 'release:example-001',
    'origin' => 'release',
    'originRef' => 'issue-or-release-reference',
    'standing' => true,
    'changes' => [
        [
            'kind' => 'block',
            'page' => 'index.html',
            'block' => 'hero.title',
            'old' => 'Expected predecessor',
            'new' => 'Accepted replacement',
        ],
        [
            'kind' => 'document-replace',
            'key' => 'content/site.json',
            'old' => 'one uniquely expected fragment',
            'new' => 'replacement fragment',
        ],
    ],
];
```

Rules:

- IDs are permanent. Never reuse an applied ID with different contents.
- Name the expected predecessor; do not use an update set as an unconditional overwrite.
- Use `standing => true` only when a known accepted correction must also normalize a lagging repository candidate until checked-in source catches up.
- A mismatch preserves the newer canonical SQL value and records the outcome rather than forcing the update.
- Ordinary repository changes do not belong here; `contentSyncRepository()` handles those through three-way reconciliation.
