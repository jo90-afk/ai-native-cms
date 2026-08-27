from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
def text(path): return (ROOT/path).read_text(encoding='utf-8')
migration=text('database/migrations/9-to-10.php')
aud=text('api/audience.php')
public=text('api/audience-subscribe.php')
mail=text('api/mail-transport.php')
cms=text('api/cms-audience.php')
ui=text('cms/audience.php')
onboarding=text('api/onboarding.php')
private=text('database/private-config.example.ini')
docs=text('docs/CPANEL-EMAIL.md')
assert "schema_version=10" in migration and 'audience_lists' in migration and 'audience_subscriptions' in migration
assert 'subscribers_legacy_archive' in migration and "'disabled'" in migration and 'legacy-subscribers' in migration
assert "status ENUM('pending','confirmed','unsubscribed')" in migration
assert 'audienceSignupPresetHtml' in aud and '/api/audience-subscribe.php' in aud and "'Audience'" in aud
assert "hash('sha256',$token)" in aud and 'time()-30*86400' in aud and 'time()-900' in aud
assert "if($method==='GET'&&isset($_GET['confirm']))" in public and "isset($_POST['confirm_token'])" in public
assert 'audienceConfirm(' not in public.split("if($method==='GET'&&isset($_GET['confirm']))",1)[1].split("if($method==='POST'&&isset($_POST['confirm_token']))",1)[0]
assert 'mailTransportStatus()' in cms and 'mailTransportConfig()' not in cms
assert "['configured'=>" in mail and "'usernameSet'" in mail and "'password'=>" not in mail.split('function mailTransportStatus',1)[1].split('function mailTransportValidateHeader',1)[0]
assert "'verify_peer'=>true" in mail and "'verify_peer_name'=>true" in mail and 'STARTTLS' in mail and 'AUTH LOGIN' in mail and 'Unencrypted SMTP is disabled outside development.' in mail
assert 'AINCMS_MAIL_PASSWORD' in private and 'Connect Devices' in private
assert 'activeAudienceLists' in onboarding and '/docs/CPANEL-EMAIL.md' in onboarding and 'mailConfigured' in onboarding
assert '/cms/audience.php' in ui and 'Export confirmed CSV' in ui and 'Send test' in ui
assert 'Email Accounts' in docs and 'Secure SSL/TLS' in docs and 'Email Deliverability' in docs and 'SPF' in docs and 'DKIM' in docs and 'DMARC' in docs
print('PASS: Audience authority, consent collection, mail transport, and cPanel onboarding contracts')
