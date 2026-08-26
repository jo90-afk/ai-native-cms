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
        'route_root' => 'writing',
        'index_path' => 'content/posts/index.json',
        'article_template' => '',
    ],
    'media' => [
        'public_roots' => ['assets'],
        'upload_root' => 'assets/uploads',
        'max_upload_bytes' => 8388608,
    ],
    'navigation' => [
        // Leave empty to derive an initial navigation from configured repository pages.
        // Canonical edits thereafter live in MySQL.
        'primary' => [],
    ],
    'branding' => [
        // Identity projection targets only these classes when present in public HTML.
        'mark' => '',
        'identity_classes' => [
            'mark' => 'brand-mark',
            'name' => 'brand-name',
        ],
        // Optional stylesheet for adopter-declared CSS custom-property overrides.
        // Leave blank if the site only wants CMS-managed identity text.
        'stylesheet' => '',
        'tokens' => [
            // 'accent' => ['css'=>'--accent','type'=>'color','default'=>'#3366ff'],
            // 'radius' => ['css'=>'--radius','type'=>'length','default'=>16,'min'=>0,'max'=>48,'unit'=>'px'],
            // 'content-width' => ['css'=>'--content-width','type'=>'length','default'=>1200,'min'=>640,'max'=>1800,'unit'=>'px'],
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
        // Optional trusted repository-owned projectors. Hooks receive
        // ($root, $context) and may return an array of deterministic results.
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
