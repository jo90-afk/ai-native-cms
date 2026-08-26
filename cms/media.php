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
<title>Media — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="media">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/onboarding.php">Onboarding</a><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php" aria-current="page">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php">SEO</a><a href="/cms/redirects.php">Redirects</a><a href="/cms/readiness.php">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="workspace">
<aside class="workspace-list">
<form id="media-upload" class="section-card">
<h2>Upload image</h2>
<label class="field">File<input id="media-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required></label>
<label class="field">Title<input id="upload-title" maxlength="191"></label>
<label class="field">Alt text<input id="upload-alt" maxlength="1000"></label>
<label class="field">Caption<textarea id="upload-caption" rows="3"></textarea></label>
<button type="submit">Upload</button>
</form>
<button id="refresh-media" class="secondary" type="button">Refresh site assets</button>
</aside>
<section class="workspace-main">
<div class="toolbar"><div><p class="eyebrow">First-party asset catalog</p><h1>Media</h1></div><span id="media-count" class="pill">0 assets</span></div>
<p class="muted">File bytes live in adopter-controlled public roots. The CMS stores canonical metadata and validates uploaded image bytes before cataloging them.</p>
<div id="media-list" class="revision-list" aria-live="polite"></div>
<p id="media-status" class="status" role="status" aria-live="polite"></p>
</section>
</main>
<script src="/cms/media.js" defer></script>
</body>
</html>
