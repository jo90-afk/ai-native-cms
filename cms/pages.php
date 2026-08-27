<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/runtime.php';
secureCmsHeaders();requireCmsAuth(false);
header('Location: /cms/composer.php',true,302);
exit;
