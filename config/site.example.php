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
        'documents' => [
            // 'content/site.json' => ['type'=>'site-data','label'=>'Site data','format'=>'json'],
        ],
        'generated_roots' => ['writing/'],
        'excluded_roots' => ['cms/','api/','database/','tests/'],
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
        'primary' => [],
    ],
    'branding' => [
        'mark' => '',
        'identity_classes' => ['mark'=>'brand-mark','name'=>'brand-name'],
        'stylesheet' => '',
        'tokens' => [
            // 'accent' => ['css'=>'--accent','type'=>'color','default'=>'#3366ff'],
            // 'radius' => ['css'=>'--radius','type'=>'length','default'=>16,'min'=>0,'max'=>48,'unit'=>'px'],
        ],
    ],
    'redirects' => [
        // Optional repository-owned compatibility aliases. They are merged into
        // the generated static map and visible in CMS Redirects, but remain read-only.
        // Manual and post-slug-history redirects are canonical in MySQL.
        'system_aliases' => [
            // ['source'=>'/old-docs/','target'=>'/docs/','status'=>301,'preserveQuery'=>true,'managedBy'=>'system','note'=>'Historical documentation route.'],
        ],
    ],
    'readiness' => [
        'adapters' => [
            // ['id'=>'shared-host','script'=>'adapters/readiness-shared-host.php','callable'=>'sharedHostReadiness'],
        ],
    ],
    'projection' => [
        'outputs' => ['feed.xml','sitemap.xml','sitemap.txt','site-index.json','llms.txt','llms-full.txt'],
        // Hooks are deterministic presentation/integration only. after_seo is the
        // correct phase for sitemap/discovery adapters that must consume final SEO.
        'hooks' => [
            // 'after_pages' => [
            //     ['id'=>'page-derivatives','script'=>'adapters/pages.php','callable'=>'projectPageDerivatives'],
            // ],
            // 'after_seo' => [
            //     ['id'=>'discovery','script'=>'adapters/discovery.php','callable'=>'projectDiscovery'],
            // ],
        ],
    ],
];
