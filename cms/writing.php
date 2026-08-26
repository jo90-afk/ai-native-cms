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
<title>Writing — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="writing">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/onboarding.php">Onboarding</a><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php" aria-current="page">Writing</a><a href="/cms/seo.php">SEO</a><a href="/cms/redirects.php">Redirects</a><a href="/cms/readiness.php">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="workspace">
<aside class="workspace-list">
<div class="toolbar-actions"><button id="new-post" type="button">New post</button></div>
<div id="post-list" aria-label="Posts"></div>
</aside>
<section class="workspace-main">
<div class="toolbar">
<div><p class="eyebrow">Long-form publishing</p><h1 id="writing-heading">Choose a post</h1></div>
<div class="toolbar-actions"><span id="post-state" class="pill">No post</span><button id="save-post" type="button" disabled>Save</button></div>
</div>
<form id="post-form" class="field-grid" hidden>
<label class="field">Title<input id="post-title" maxlength="512" required></label>
<label class="field">Slug<input id="post-slug" maxlength="191" required><small>Lowercase URL-safe identifier.</small></label>
<label class="field wide">Dek<textarea id="post-dek" rows="3" maxlength="4000"></textarea></label>
<label class="field">Category<input id="post-category" maxlength="100" value="writing"></label>
<label class="field">Category label<input id="post-category-label" maxlength="191" value="Writing"></label>
<label class="field">Publish date<input id="post-date" type="date"></label>
<label class="field">Status<select id="post-status"><option value="draft">Draft</option><option value="published">Published</option></select></label>
<label class="field wide">Tags<input id="post-tags"><small>Comma-separated.</small></label>
<label class="field wide">Thesis<textarea id="post-thesis" rows="3"></textarea></label>
<label class="field wide">Related slugs<input id="post-related"><small>Comma-separated.</small></label>
<label class="field wide">Body (Markdown)<textarea id="post-body" class="body-editor"></textarea><small>Raw HTML is escaped. Supported Markdown is intentionally bounded.</small></label>
</form>
<p id="writing-status" class="status" role="status" aria-live="polite"></p>
<section id="revisions-card" class="section-card" hidden><h2>Revision history</h2><div id="revision-list" class="revision-list"></div></section>
</section>
</main>
<script src="/cms/writing.js" defer></script>
</body>
</html>
