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
        'article_template' => 'templates/article.html',
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
        'mark' => 'A',
        'identity_classes' => ['mark'=>'brand-mark','name'=>'brand-name'],
        'stylesheet' => 'assets/styles.css',
        'tokens' => [
            'accent' => ['css'=>'--accent','type'=>'color','default'=>'#245c45'],
            'canvas' => ['css'=>'--canvas','type'=>'color','default'=>'#f7f5ef'],
            'surface' => ['css'=>'--surface','type'=>'color','default'=>'#ffffff'],
            'ink' => ['css'=>'--ink','type'=>'color','default'=>'#171918'],
            'muted' => ['css'=>'--muted','type'=>'color','default'=>'#626862'],
            'radius' => ['css'=>'--radius','type'=>'length','default'=>18,'min'=>0,'max'=>48,'unit'=>'px'],
            'contentWidth' => ['css'=>'--content-width','type'=>'length','default'=>1120,'min'=>720,'max'=>1440,'unit'=>'px'],
        ],
    ],
    'seo' => [
        // Used only for deterministic projection defaults. Canonical page-specific
        // titles/descriptions/canonicals/social-mode controls remain in seo_overrides.
        'author' => 'Site Owner',
        'social_image' => '/assets/share-card.svg',
        'locale' => 'en_US',
        'language' => 'en-US',
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
        // New installs project CMS-managed *.html keys to clean /slug/ routes.
        // Existing adopters without this key retain their prior flat-route behavior.
        'clean_managed_routes' => true,
        // Relocated HTML URLs are rebased by the projector. JavaScript-created
        // runtime URLs are not parsed; make those root-relative (for example
        // /assets/app.js or /api/action.php) when a page may live at /slug/.
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
