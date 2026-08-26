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
    ],
];
