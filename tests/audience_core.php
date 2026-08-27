<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/api/audience.php';
function ok(bool $v,string $m): void {if(!$v)throw new RuntimeException($m);}
ok(audienceNormalizeKey(' Product-UPDATES ')==='product-updates','list key normalization failed');$bad=false;try{audienceNormalizeKey('bad_key');}catch(RuntimeException $e){$bad=true;}ok($bad,'invalid list key accepted');ok(audienceNormalizeEmail(' Person@Example.COM ')==='person@example.com','email normalization failed');
$html=audienceSignupPresetHtml(['key'=>'product-updates','publicLabel'=>'Updates <script>bad()</script>','description'=>'Occasional & useful']);ok(str_contains($html,'action="/api/audience-subscribe.php"'),'signup endpoint missing');ok(!str_contains($html,'<script>'),'signup label was not escaped');ok(str_contains($html,'value="product-updates"'),'list key missing from generated block');
putenv('AINCMS_ENV=development');putenv('AINCMS_MAIL_TRANSPORT=smtp');putenv('AINCMS_MAIL_HOST=mail.example.com');putenv('AINCMS_MAIL_PORT=465');putenv('AINCMS_MAIL_SECURITY=ssl');putenv('AINCMS_MAIL_USERNAME=updates@example.com');putenv('AINCMS_MAIL_PASSWORD=top-secret-test-value');putenv('AINCMS_MAIL_FROM=updates@example.com');putenv('AINCMS_MAIL_FROM_NAME=Example Site');$status=mailTransportStatus();ok($status['configured']===true,'valid SMTP config not recognized');ok(!str_contains(json_encode($status),'top-secret-test-value'),'mail status exposed password');
putenv('AINCMS_ENV=production');putenv('AINCMS_MAIL_SECURITY=none');$status=mailTransportStatus();ok($status['configured']===false,'unencrypted production SMTP was accepted');ok(str_contains(implode(' ',$status['issues']),'Unencrypted SMTP'),'unencrypted SMTP issue missing');
echo "PASS: Audience pure behavior and secret-redaction checks\n";
