#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
export AINCMS_DB_HOST="${AINCMS_DB_HOST:-127.0.0.1}"
export AINCMS_DB_PORT="${AINCMS_DB_PORT:-3306}"
export AINCMS_DB_NAME="${AINCMS_DB_NAME:-aincms}"
export AINCMS_DB_USER="${AINCMS_DB_USER:-aincms}"
export AINCMS_DB_PASSWORD="${AINCMS_DB_PASSWORD:-rehearsal-db-pass}"
export AINCMS_CMS_ENABLED=1
export AINCMS_CMS_USER=rehearsal-owner
export AINCMS_CMS_PASSWORD_HASH="$(php -r 'echo password_hash("rehearsal-owner-password", PASSWORD_DEFAULT);')"
export AINCMS_RATE_LIMIT_SECRET=0123456789abcdef0123456789abcdef
export AINCMS_ENV=development
export AINCMS_CMS_REQUIRE_HTTPS=0
export AINCMS_PUBLIC_ORIGIN=http://localhost:8080
export AINCMS_MAIL_TRANSPORT=log
export AINCMS_MAIL_FROM=updates@example.com
export AINCMS_MAIL_FROM_NAME='Rehearsal Site'
php -r 'require "api/runtime.php"; if((dbHealth()["schemaVersion"]??0)!==9) throw new RuntimeException("v10 rehearsal must start after schema-v9 rehearsal");'
php -r 'require "api/runtime.php"; $pdo=db(); $pdo->prepare("DELETE FROM subscribers WHERE list_key=?")->execute(["legacy-news"]); $pdo->prepare("INSERT INTO subscribers (list_key,email,status,requested_at,confirmed_at,created_at,updated_at) VALUES (?,?,\"confirmed\",UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP())")->execute(["legacy-news","legacy@example.com"]);'
php database/migrations/9-to-10.php --apply | tee /tmp/aincms-v10-migration.json
php database/migrations/9-to-10.php --apply | tee /tmp/aincms-v10-migration-second.json
cat >/tmp/aincms-v10-verify.php <<'PHP'
<?php
declare(strict_types=1);require $argv[1].'/api/audience.php';
if((dbHealth()['schemaVersion']??0)!==10)throw new RuntimeException('schema did not advance to 10');$tables=db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);foreach(['audience_lists','audience_subscriptions','subscribers_legacy_archive'] as $t)if(!in_array($t,$tables,true))throw new RuntimeException('missing '.$t);if(in_array('subscribers',$tables,true))throw new RuntimeException('legacy subscribers remained active');
$legacy=audienceListRecord('legacy-news');if(!$legacy||$legacy['status']!=='disabled')throw new RuntimeException('legacy list was not preserved disabled');$m=audienceSubscriptions('legacy-news','confirmed');if(count($m)!==1||$m[0]['email']!=='legacy@example.com')throw new RuntimeException('legacy confirmed member was not preserved');
$list=audienceSaveList(['key'=>'product-updates','label'=>'Product updates','publicLabel'=>'Product updates','description'=>'Occasional product updates.','confirmationSubject'=>'Confirm product updates','confirmationBody'=>'Confirm your request here: {confirmation_url}','status'=>'active']);$preset=blockPresetRecord(audienceSignupPresetKey('product-updates'));if(!$preset||!str_contains((string)$preset['sourceNote'],'Audience list product-updates')||!str_contains((string)$preset['html'],'/api/audience-subscribe.php'))throw new RuntimeException('Signup block was not generated');
audienceSubscribe('product-updates','person@example.com');$row=db()->query("SELECT body FROM mail_outbox WHERE recipient='person@example.com' ORDER BY id DESC LIMIT 1")->fetchColumn();if(!is_string($row)||!preg_match('/confirm=([A-Za-z0-9_-]+)/',$row,$match))throw new RuntimeException('confirmation message/token unavailable');$token=$match[1];if(!audienceConfirmationRecord($token))throw new RuntimeException('pending confirmation token not recognized');audienceConfirm($token);$confirmed=audienceSubscriptions('product-updates','confirmed');if(count($confirmed)!==1)throw new RuntimeException('explicit confirmation did not confirm member');audienceUnsubscribeOperator('product-updates','person@example.com');if(count(audienceSubscriptions('product-updates','unsubscribed'))!==1)throw new RuntimeException('unsubscribe suppression state missing');audienceSubscribe('product-updates','person@example.com');if(count(audienceSubscriptions('product-updates','pending'))!==1)throw new RuntimeException('explicit resubscribe did not return to pending confirmation');
echo json_encode(['ok'=>true,'schema'=>10,'legacyPreserved'=>true,'signupPreset'=>true,'doubleOptIn'=>true,'unsubscribePreserved'=>true],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),PHP_EOL;
PHP
php /tmp/aincms-v10-verify.php "$ROOT" | tee /tmp/aincms-v10-audience.json
python3 - /tmp/aincms-v10-migration.json /tmp/aincms-v10-migration-second.json <<'PY'
import json,sys
first=json.load(open(sys.argv[1]));second=json.load(open(sys.argv[2]));assert first['ok'] and first['changed'] is True;assert first['after']['schemaVersion']==10;assert first['after']['audienceLists'] and first['after']['audienceSubscriptions'] and not first['after']['legacySubscribers'];assert second['ok'] and second['changed'] is False
PY
export AINCMS_MAIL_TRANSPORT=smtp
export AINCMS_MAIL_HOST=127.0.0.1
export AINCMS_MAIL_PORT=2525
export AINCMS_MAIL_SECURITY=none
export AINCMS_MAIL_USERNAME=fake-user
export AINCMS_MAIL_PASSWORD=fake-password
export AINCMS_MAIL_FROM=updates@example.com
rm -f /tmp/aincms-smtp-message.txt
python3 tests/fake_smtp.py 127.0.0.1 2525 /tmp/aincms-smtp-message.txt & SMTP_PID=$!
sleep 0.2
php -r 'require "api/mail-transport.php"; mailTransportSend("smtp-probe@example.com","SMTP rehearsal","Authenticated fake SMTP path reached.");'
wait "$SMTP_PID"
grep -q 'Subject: SMTP rehearsal' /tmp/aincms-smtp-message.txt
grep -q 'Authenticated fake SMTP path reached.' /tmp/aincms-smtp-message.txt
echo 'PASS: schema-v10 Audience migration, consent flow, generated Signup block, and SMTP transport rehearsal'
