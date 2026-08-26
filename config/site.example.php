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
    'writing' => [
        // Published posts materialize at <route_root>/<slug>/index.html.
        'route_root' => 'writing',
        'index_path' => 'content/posts/index.json',
        // Optional repository-owned HTML template. Supported placeholders:
        // {{site_name}}, {{title}}, {{dek}}, {{category}}, {{date}},
        // {{canonical}}, {{reading_minutes}}, {{body_html}}.
        'article_template' => '',
    ],
    'media' => [
        // Existing images inside these public roots may be cataloged and selected
        // by typed composer variables. Uploads are restricted to raster images.
        'public_roots' => ['assets'],
        'upload_root' => 'assets/uploads',
        'max_upload_bytes' => 8388608,
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
        // after_pages, finalize. after_pages runs after core post + SEO projection.
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
