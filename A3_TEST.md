# A3 — Communication Module (SMS): Test Plan

**Feature:** Full SMS Communication module — settings, controller, compose page, global modal, integration buttons  
**Date:** March 31, 2026  
**Status:** Implemented

---

## What Was Built

| File | Change |
|---|---|
| `migrations/20260331_a3_communication.sql` | Creates `sms_log` table; inserts `communication_manage` permission; assigns to admin roles |
| `app/communication_controller.php` | Actions: `send_sms`, `get_sms_log`, `get_contact_list` |
| `views/communication/communication.php` | Compose panel (tenant selector, phone, message, char counter) + sent log DataTable |
| `public/js/modules/communication.js` | JS: page init, Select2 tenant picker, `submitSms()`, global `openSmsModal()`, modal handler |
| `views/partials/app_footer.php` | `#sendSmsModal` HTML (global); `communication.js` loaded on every page |
| `app/autoload.php` | `communication` menu entry (auth: `communication_manage`) |
| `views/settings/system_settings.php` | "Communication" sidebar group + SMS settings panel (username, sender, password, enabled toggle) |
| `app/settings_controller.php` | `save_sms_settings` action (previously added) |
| `app/helpers.php` | `$GLOBALS['SMS']` static config (provider, SID, API URL) |
| `app/tenant_controller.php` | Send SMS button added to tenant list row actions |
| `views/tenants/view_lease.php` | "Send SMS to Tenant" button on tenant info card |
| `views/accounting/invoice_show.php` | "Remind Tenant" SMS button (shown when tenant has phone) |
| `views/accounting/payment_show.php` | "Send SMS to Tenant" button in action bar |

---

## Pre-conditions

1. Run migration `20260331_a3_communication.sql`.
2. SMS enabled in **Settings → SMS / Messaging** with valid username, password, and sender name.
3. At least one tenant with a phone number exists.
4. PHP `curl` extension enabled (for API calls).

---

## Test Cases

### TC-A3-01 — Migration runs cleanly

- Execute `20260331_a3_communication.sql`.
- **Expected:** `sms_log` table created. `permissions` table now contains `communication_manage`. Admin roles have it assigned.

---

### TC-A3-02 — Communication menu visible to admin

1. Log in as admin.
2. **Expected:** Left sidebar shows a **Communication** menu item with a chat icon.
3. Log in as a non-admin user without `communication_manage`.
4. **Expected:** Communication menu item is NOT visible (or shows 403 if navigated to directly).

---

### TC-A3-03 — SMS Settings save correctly

1. Go to **Settings → SMS / Messaging**.
2. **Expected:** Fields for Username, Sender Name, Password, and Enable toggle are visible.
3. Fill in credentials, click **Save SMS Settings**.
4. **Expected:** Toast "SMS settings saved successfully."
5. **DB check:** `system_settings` rows for `sms_username`, `sms_sender_name`, `sms_password`, `sms_enabled` updated.
6. Password field clears after save (never pre-filled on reload for security).

---

### TC-A3-04 — Communication page loads

1. Navigate to **Communication**.
2. **Expected:** Page shows: Compose panel (left) and Sent Messages DataTable (right).
3. The tenant selector is a searchable Select2 dropdown.
4. DataTable shows existing SMS log entries (or empty state).

---

### TC-A3-05 — Tenant selector auto-fills phone

1. On the Communication page, type a tenant's name in the Recipient selector.
2. Select a tenant from results.
3. **Expected:** Phone Number field auto-populates with the tenant's stored phone.

---

### TC-A3-06 — Character counter updates

1. Type text into the Message field.
2. **Expected:** Counter shows `N / 640 characters`. When > 160 chars, shows "2 SMS parts", etc.

---

### TC-A3-07 — Sending SMS (live API test)

> Requires valid Hormuud/1s2u credentials configured in SMS settings.

1. Enter a valid Somali phone number (+252…) in the Recipient field.
2. Type a short message.
3. Click **Send Message**.
4. **Expected:**
   - Toast: "SMS sent successfully."
   - Log table refreshes; new row appears with status badge **sent**.
   - `sms_log` DB row: `status = 'sent'`, `provider_response` contains the API response.

---

### TC-A3-08 — Failed send is logged

1. Configure invalid credentials in SMS settings.
2. Try sending an SMS.
3. **Expected:**
   - Error modal shown.
   - `sms_log` DB row: `status = 'failed'`, `provider_response` shows the error.

---

### TC-A3-09 — SMS disabled blocks sending

1. In SMS settings, disable SMS (`sms_enabled = no`).
2. Try sending from the Communication page.
3. **Expected:** Error "SMS is disabled. Enable it in Settings → Communication."

---

### TC-A3-10 — Tenant list: Send SMS button

1. Go to **Tenants** list.
2. **Expected:** Each tenant row with a phone number has a blue chat icon button.
3. Click it.
4. **Expected:** `#sendSmsModal` opens with that tenant's name and phone pre-filled and locked.

---

### TC-A3-11 — Lease view: Send SMS button

1. Open any active lease.
2. **Expected:** Tenant Information card has a "Send SMS to Tenant" button.
3. Click it.
4. **Expected:** SMS modal opens pre-filled with tenant info.

---

### TC-A3-12 — Invoice show: Remind Tenant button

1. Open an invoice where the tenant has a phone number.
2. **Expected:** "Remind Tenant" blue button visible in top-right action bar.
3. Click it.
4. **Expected:** SMS modal opens, tenant fields pre-filled and locked.

---

### TC-A3-13 — Payment show: Send SMS button

1. Open a payment record where the tenant has a phone.
2. **Expected:** "Send SMS to Tenant" button in top-right.
3. Click and verify modal opens pre-filled.

---

### TC-A3-14 — Modal free-form mode (no pre-fill)

1. Call `openSmsModal()` with no arguments (can test via browser console).
2. **Expected:** Modal opens with a searchable tenant dropdown (Select2), phone field editable.

---

## Rollback

```sql
DROP TABLE IF EXISTS sms_log;
DELETE FROM permissions WHERE permission_name = 'communication_manage';
DELETE FROM role_permissions WHERE permission_id = (SELECT id FROM permissions WHERE permission_name = 'communication_manage');
```
Remove `views/communication/` folder, `app/communication_controller.php`, `public/js/modules/communication.js`.  
Revert `app_footer.php`, `system_settings.php`, `tenant_controller.php`, `view_lease.php`, `invoice_show.php`, `payment_show.php`.
