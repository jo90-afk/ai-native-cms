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
<title>SEO — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="seo">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/onboarding.php">Onboarding</a><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php" aria-current="page">SEO</a><a href="/cms/redirects.php">Redirects</a><a href="/cms/readiness.php">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="workspace">
<aside class="workspace-list">
<section class="section-card" aria-labelledby="seo-quality-heading"><p class="eyebrow">Site-wide quality</p><h2 id="seo-quality-heading">Inspecting…</h2><p id="seo-quality-summary" class="muted"></p><div id="seo-site-findings" class="revision-list" aria-live="polite"></div></section>
<div id="seo-list" aria-label="SEO targets"></div>
</aside>
<section class="workspace-main">
<div class="toolbar"><div><p class="eyebrow">Search and social metadata</p><h1 id="seo-heading">Choose a page</h1></div><button id="save-seo" type="button" disabled>Save SEO</button></div>
<section id="seo-page-quality" class="section-card" hidden><div class="toolbar"><div><p class="eyebrow">Page quality</p><h2 id="seo-page-score">Not scored</h2></div></div><div id="seo-page-findings" class="revision-list" aria-live="polite"></div></section>
<form id="seo-form" class="field-grid" hidden>
<label class="field wide">Browser title<input id="seo-title" maxlength="512" required></label>
<label class="field wide">Description<textarea id="seo-description" rows="3" maxlength="4000" required></textarea></label>
<section class="section-card wide"><h2>Discovery controls</h2><div class="seo-controls">
<label class="check-row"><input id="seo-index" type="checkbox" checked> Allow indexing</label>
<label class="check-row"><input id="seo-follow" type="checkbox" checked> Follow links</label>
<label class="check-row"><input id="seo-archive" type="checkbox" checked> Allow archive</label>
<label class="field">Snippet limit<input id="seo-snippet" type="number" min="-1" max="1000" value="-1"></label>
<label class="field">Image preview<select id="seo-image-preview"><option value="large">Large</option><option value="standard">Standard</option><option value="none">None</option></select></label>
<label class="field">Video preview limit<input id="seo-video-preview" type="number" min="-1" max="3600" value="-1"></label>
</div></section>
<section class="section-card wide"><h2>Canonical URL</h2><label class="field">Mode<select id="seo-canonical-mode"><option value="self">Use this page</option><option value="custom">Custom URL</option></select></label><label class="field">Canonical<input id="seo-canonical" type="url"></label><p id="seo-expected" class="muted"></p></section>
<section class="section-card wide"><h2>Social copy</h2><label class="field">Mode<select id="seo-social-mode"><option value="inherit">Use browser title and description</option><option value="custom">Custom social copy</option></select></label><div class="field-grid"><label class="field wide">Open Graph title<input id="seo-og-title" maxlength="512"></label><label class="field wide">Open Graph description<textarea id="seo-og-description" rows="2" maxlength="4000"></textarea></label><label class="field wide">Twitter title<input id="seo-twitter-title" maxlength="512"></label><label class="field wide">Twitter description<textarea id="seo-twitter-description" rows="2" maxlength="4000"></textarea></label></div></section>
</form>
<p id="seo-status" class="status" role="status" aria-live="polite"></p>
</section>
</main>
<script src="/cms/seo.js" defer></script>
</body>
</html>
