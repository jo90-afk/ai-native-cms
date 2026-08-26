<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/runtime.php';
secureCmsHeaders();$user=requireCmsAuth(false);$siteName=(string)siteConfigValue('site','name','Site');$csrf=cmsCsrfToken();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="cms-csrf" content="<?=htmlspecialchars($csrf,ENT_QUOTES|ENT_HTML5,'UTF-8')?>">
<title>Composer — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="composer">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php" aria-current="page">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php">SEO</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="workspace">
<aside class="workspace-list">
<label class="field">Page<select id="composer-page"></select></label>
<button id="refresh-library" class="secondary" type="button">Refresh templates</button>
<div class="section-card"><h2>New page</h2><label class="field">Filename<input id="new-path" placeholder="services.html"></label><label class="field">Label<input id="new-label" placeholder="Services"></label><label class="field">Browser title<input id="new-title" placeholder="Services"></label><label class="field">Shell<select id="new-shell"></select></label><label class="field">Parent<select id="new-parent"></select></label><label class="check-row"><input id="new-nav" type="checkbox"> Add to primary navigation</label><button id="start-new-page" type="button">Start new page</button></div>
<div class="section-card"><h2>Add block</h2><label class="field">Template<select id="template-select"></select></label><button id="add-block" type="button">Add block</button></div>
</aside>
<section class="workspace-main">
<div class="toolbar"><div><p class="eyebrow">Typed page composition</p><h1 id="composer-heading">Choose a page</h1></div><div class="toolbar-actions"><span id="composition-state" class="pill">Not loaded</span><button id="save-composition" type="button" disabled>Save composition</button></div></div>
<p class="muted">Structure comes from trusted templates. New pages are canonical compositions with an explicit shell and optional parent; browser requests never contain structural HTML.</p>
<section id="page-metadata" class="section-card" hidden><h2>Page identity</h2><div class="field-grid"><label class="field">Label<input id="page-label" maxlength="191"></label><label class="field">Browser title<input id="page-title" maxlength="512"></label><label class="field">Shell<select id="page-shell"></select></label><label class="field">Parent<select id="page-parent"></select></label></div></section>
<div id="composition-blocks" class="revision-list" aria-live="polite"></div>
<p id="composer-status" class="status" role="status" aria-live="polite"></p>
</section>
</main>
<script src="/cms/composer.js" defer></script>
</body>
</html>
