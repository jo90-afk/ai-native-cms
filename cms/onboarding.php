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
<title>Onboarding — AI Native CMS</title>
<link rel="stylesheet" href="/cms/cms.css">
<link rel="stylesheet" href="/cms/onboarding.css">
</head>
<body data-cms-view="onboarding">
<header class="app-header">
<div><p class="eyebrow">AI Native CMS</p><strong><?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></strong></div>
<nav class="cms-nav" aria-label="CMS"><a href="/cms/onboarding.php" aria-current="page">Onboarding</a><a href="/cms/pages.php">Pages</a><a href="/cms/composer.php">Composer</a><a href="/cms/media.php">Media</a><a href="/cms/navigation.php">Navigation</a><a href="/cms/branding.php">Branding</a><a href="/cms/writing.php">Writing</a><a href="/cms/seo.php">SEO</a><a href="/cms/redirects.php">Redirects</a><a href="/cms/readiness.php">Readiness</a></nav>
<div class="header-actions"><span class="user-label"><?=htmlspecialchars((string)($user['displayName']??$user['username']??'Owner'),ENT_QUOTES|ENT_HTML5,'UTF-8')?></span><button id="logout" class="secondary" type="button">Sign out</button></div>
</header>
<main class="onboarding-shell">
<section class="onboarding-intro" aria-labelledby="onboarding-title">
<div>
<p class="eyebrow">Build the site in a sensible order</p>
<h1 id="onboarding-title">Start with a working site. Make it specific.</h1>
<p class="muted">This guide reads the site’s actual configuration, canonical content, and readiness state. There is no one-time completion flag: leave, come back, or keep using it after launch.</p>
</div>
<div class="progress-card" aria-live="polite">
<strong id="progress-value">Inspecting site…</strong>
<span id="progress-detail" class="muted"></span>
</div>
</section>
<section class="authority-explainer" aria-labelledby="authority-title">
<div><p class="eyebrow">One model to remember</p><h2 id="authority-title">Structure lives in Git. Accepted content lives in the CMS.</h2></div>
<ul id="onboarding-principles"><li>Loading authority model…</li></ul>
</section>
<section aria-labelledby="steps-title">
<div class="section-heading"><div><p class="eyebrow">Your path</p><h2 id="steps-title">What to do next</h2></div><a class="secondary-link" href="/" target="_blank" rel="noopener">View public site ↗</a></div>
<div id="onboarding-steps" class="onboarding-steps" aria-live="polite"><p class="empty">Inspecting current state…</p></div>
</section>
<section class="onboarding-handoff" aria-labelledby="handoff-title">
<div><p class="eyebrow">When the basics are in place</p><h2 id="handoff-title">Keep iterating without losing the source of truth.</h2><p class="muted">Repository changes belong on branches and pull requests. Content stays in canonical CMS state. The shipped guides cover hosting and LLM-assisted design/content/feature work.</p></div>
<div class="handoff-actions"><a href="/docs/REPOSITORY-OPERATIONS.md">Repository & hosting guide</a><a href="/docs/LLM-COLLABORATION.md">LLM collaboration guide</a><a href="/cms/readiness.php">Readiness</a></div>
</section>
<p id="onboarding-status" class="status" role="status" aria-live="polite"></p>
</main>
<script src="/cms/onboarding.js" defer></script>
</body>
</html>
