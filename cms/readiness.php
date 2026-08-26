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
<title>Readiness — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/editor.css">
</head>
<body data-cms-view="readiness">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php">SEO</a><a href="/cms/readiness.php" aria-current="page">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="workspace">
<aside class="workspace-list"><section class="section-card"><p class="eyebrow">Production posture</p><h2 id="readiness-state">Checking…</h2><p id="readiness-summary" class="muted"></p><button id="refresh-readiness" class="secondary" type="button">Run checks again</button></section></aside>
<section class="workspace-main">
<div class="toolbar"><div><p class="eyebrow">Read-only diagnostics</p><h1>Readiness</h1></div><span id="readiness-pill" class="pill">Checking</span></div>
<p class="muted">These checks do not initialize, migrate, publish, send mail, or deploy anything. Resolve blocking failures before treating the CMS as production-ready. Host-specific checks may be supplied only by trusted repository adapters.</p>
<div id="readiness-list" class="revision-list" aria-live="polite"></div>
<p id="readiness-status" class="status" role="status" aria-live="polite"></p>
</section>
</main>
<script src="/cms/readiness.js" defer></script>
</body>
</html>
