<?php
declare(strict_types=1);

/**
 * Copy to config/site.php in an adopter repository and keep secrets out of it.
 * This file describes public structure, not credentials.
 */
return [
    'site' => [
        'name' => 'Example Site',
        'base_url' => 'https://example.com',
        'owner_display_name' => 'Site Owner',
    ],
    'cms' => [
        'editable_pages' => [
            'index.html' => 'Home',
            'about.html' => 'About',
            'writing.html' => 'Writing',
        ],
        // Optional structured authored files that should participate in the same
        // canonical-SQL/repository-reconciliation model as editable pages.
        'documents' => [
            // 'content/site.json' => [
            //     'type' => 'site-data',
            //     'label' => 'Site data',
            //     'format' => 'json',
            // ],
        ],
        'generated_roots' => [
            'writing/',
        ],
        'excluded_roots' => [
            'cms/',
            'api/',
            'database/',
            'tests/',
        ],
    ],
    'projection' => [
        'outputs' => [
            'feed.xml',
            'sitemap.xml',
            'sitemap.txt',
            'site-index.json',
            'llms.txt',
            'llms-full.txt',
        ],
        // Optional trusted repository-owned PHP projectors. Hooks receive
        // ($root, $context) and may return an array of deterministic results.
        // Supported phases: before_documents, after_documents, before_pages,
        // after_pages, finalize.
        'hooks' => [
            // 'after_pages' => [
            //     [
            //         'id' => 'discovery',
            //         'script' => 'adapters/discovery.php',
            //         'callable' => 'projectDiscovery',
            //     ],
            // ],
        ],
    ],
];
