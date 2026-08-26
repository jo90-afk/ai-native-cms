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
<title>Pages — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="pages">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/pages.php" aria-current="page">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php">SEO</a><a href="/cms/readiness.php">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="editor-shell">
<aside class="page-panel">
<label for="page-select">Page</label>
<select id="page-select" aria-label="Page"></select>
<div id="authority-state" class="authority-state"></div>
</aside>
<section class="editor-panel" aria-labelledby="page-heading">
<div class="editor-toolbar">
<div><p class="eyebrow">Editable copy</p><h1 id="page-heading">Choose a page</h1></div>
<button id="save-page" type="button" disabled>Save changes</button>
</div>
<p class="muted">Only text leaves explicitly marked by the site are editable here. Structural HTML remains repository-owned or composer-template-owned.</p>
<div id="blocks" class="blocks" aria-live="polite"></div>
<p id="editor-status" class="status" role="status" aria-live="polite"></p>
</section>
</main>
<script src="/cms/cms.js" defer></script>
</body>
</html>
