# cPanel email provider setup

Status: **M-019 provider/onboarding contract**. The `AINCMS_MAIL_*` settings described here are implemented on the M-019 branch; production use still waits for milestone assurance and merge.

AI Native CMS treats a cPanel mailbox as an **outgoing mail transport**. The CMS remains the authority for lists and subscription state. Creating a cPanel mailbox does not create, synchronize, or own an Audience list.

This guide follows cPanel & WHM documentation current on 2026-08-27.

## 1. Create a sender mailbox

In cPanel:

1. Open **Email → Email Accounts**.
2. Create a dedicated mailbox for site mail, for example `updates@example.com`, or choose an existing mailbox whose use is appropriate.
3. Use a unique strong mailbox password. Do not reuse the cPanel account password.

Official cPanel guide:

- https://docs.cpanel.net/knowledge-base/email/how-to-create-and-connect-to-an-email-account/

A dedicated sender mailbox makes rotation and troubleshooting easier and avoids tying application delivery to a person’s normal inbox password.

## 2. Get the exact secure SMTP settings

In **Email Accounts**, find the mailbox and click **Connect Devices**. Under **Mail Client Manual Settings**, use the **Secure SSL/TLS Settings (Recommended)** values.

cPanel’s current generic documentation lists:

- username: the full email address;
- outgoing server: commonly `mail.example.com`, but cPanel may show the server hostname instead depending on the certificate installed for the account/domain;
- SMTP port: `465` for its Secure SSL/TLS configuration;
- password: the mailbox password.

Do **not** guess the hostname. Copy the exact outgoing server cPanel displays for the mailbox so TLS certificate validation can succeed.

Official cPanel guide:

- https://docs.cpanel.net/cpanel/email/set-up-mail-client/

AI Native CMS also supports configurable ports/security modes for providers that expose STARTTLS or a different authenticated SMTP configuration. The CMS does not hard-code `mail.<domain>` or port 465 as universal truth.

## 3. Put credentials in private runtime configuration

Store mail credentials through the same private configuration boundary used for database/runtime secrets. The populated file must stay outside the public document root and out of Git.

```ini
AINCMS_MAIL_TRANSPORT=smtp
AINCMS_MAIL_HOST=mail.example.com
AINCMS_MAIL_PORT=465
AINCMS_MAIL_SECURITY=ssl
AINCMS_MAIL_USERNAME=updates@example.com
AINCMS_MAIL_PASSWORD=replace_with_mailbox_password
AINCMS_MAIL_FROM=updates@example.com
AINCMS_MAIL_FROM_NAME=Example Site
```

Use the hostname, port, and security mode shown by your provider. `AINCMS_MAIL_FROM` must be a valid sender address permitted by the account/provider.

The CMS never saves the SMTP password in canonical SQL, writes it into public configuration, echoes it into the browser, or includes it in readiness/audit output.

## 4. Recheck Audience onboarding

When schema v10 is active, open **CMS → Audience**. The Email delivery card reports only safe configuration state: transport, host, port, security, sender, and whether the username is present. Password values are never returned.

The broader onboarding workspace adds an Email delivery step when at least one active Audience list exists. Opening onboarding checks configuration presence only; it does not authenticate to SMTP or send mail.

## 5. Send one explicit test email

In **CMS → Audience**, enter a recipient in the Email delivery card and choose **Send test**.

The action:

- accepts only the test recipient address from the browser;
- reads SMTP host/username/password server-side from private configuration;
- sends one bounded test message;
- uses certificate/peer verification for encrypted SMTP;
- reports connection, TLS, authentication, or delivery-stage failure without revealing secret values;
- records only the transport name in the CMS audit event.

A successful SMTP transaction proves that the CMS handed the message to the configured server. It does not prove inbox placement.

## 6. Check cPanel Email Deliverability

Open **Email → Email Deliverability** in cPanel and review the sender domain.

cPanel uses this interface to surface mail-related DNS/authentication problems. Resolve SPF and DKIM issues before relying on confirmation messages. cPanel also supports DMARC configuration; DMARC depends on valid SPF and DKIM alignment.

Official cPanel guide:

- https://docs.cpanel.net/cpanel/email/email-deliverability-in-cpanel/

If DNS is hosted somewhere other than the cPanel server, cPanel may show suggested DNS records that must be copied to the authoritative DNS provider rather than installed locally.

## 7. Keep the provider role narrow

M-019 sends transactional Audience confirmation/test messages only. It does not turn the cPanel mailbox into a campaign platform.

The CMS owns list definitions, pending/confirmed/unsubscribed state, consent timestamps, confirmation-token hashes, and list exports/operator actions. cPanel SMTP owns authenticated transport of a message from the CMS to the mail server.

This separation lets an adopter replace cPanel SMTP with another provider later without migrating or redefining Audience membership.

## Troubleshooting

### TLS or certificate failure

Return to **Connect Devices** and verify the outgoing hostname. cPanel may use the server hostname instead of `mail.<domain>` when that is the name covered by the server’s certificate. Do not disable certificate verification as a workaround.

### Authentication failure

Verify that the username is the full mailbox address and that the password is the mailbox password, not the cPanel account password. Rotate the mailbox password if its handling is uncertain, then update only the private runtime configuration.

### Connection timeout/refused

Confirm the port/security combination shown by the provider and ask the host whether outbound SMTP connections from PHP are restricted. Do not silently fall back to an insecure transport.

### Mail sends but does not arrive reliably

Check cPanel **Email Deliverability** for SPF/DKIM/DMARC issues, then inspect the host’s delivery logs or support tooling. A successful application send is not the same as accepted inbox delivery.

### PHP `mail()` works but SMTP is not configured

AI Native CMS retains a deliberate local `mail` adapter for hosts that choose it, but authenticated SMTP is the documented cPanel provider path. The CMS does not silently downgrade from SMTP to `mail()` when SMTP configuration fails.
