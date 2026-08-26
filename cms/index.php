<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/runtime.php';
enforceCmsHttps(false);secureCmsHeaders();
$root=dirname(__DIR__);
if(cmsCurrentUser($root)){
    $target='/cms/onboarding.php';
    try{require_once $root.'/api/onboarding.php';if((bool)(onboardingState($root)['ready']??false))$target='/cms/pages.php';}catch(Throwable $e){}
    header('Location: '.$target);exit;
}
$siteName=(string)siteConfigValue('site','name','Site');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CMS sign in — <?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?></title>
<link rel="stylesheet" href="/cms/cms.css">
</head>
<body data-cms-view="login">
<main class="auth-shell">
<section class="auth-card" aria-labelledby="login-title">
<p class="eyebrow">AI Native CMS</p>
<h1 id="login-title">Sign in</h1>
<p class="muted">Manage <?=htmlspecialchars($siteName,ENT_QUOTES|ENT_HTML5,'UTF-8')?> through its private authoring surface.</p>
<form id="login-form" class="stack">
<label>Username<input id="username" name="username" autocomplete="username" required></label>
<label>Password<input id="password" name="password" type="password" autocomplete="current-password" required></label>
<button type="submit">Sign in</button>
<p id="login-status" class="status" role="status" aria-live="polite"></p>
</form>
</section>
</main>
<script src="/cms/cms.js" defer></script>
</body>
</html>
