# A4 — Two-Logo System: Test Plan

**Feature:** Two-Logo System (System Logo vs. Document Logo)  
**Date:** March 31, 2026  
**Status:** Implemented

---

## What Was Built

| File | Change |
|---|---|
| `migrations/20260331_a4_two_logo.sql` | Seeds `doc_logo_path` for all existing orgs |
| `app/settings_controller.php` | `save_branding` now accepts `logo_type=system\|document`; new `save_brand_color` action |
| `app/init.php` | Loads `$GLOBALS['docLogoPath']`, `$GLOBALS['docLogoLocalPath']`, `$GLOBALS['brandPrimaryColor']` |
| `views/partials/app_header.php` | Injects `--brand-primary` / `--sidebar-bg` CSS vars from `brandPrimaryColor` |
| `views/settings/system_settings.php` | Branding section split into System Logo zone + Document Logo zone + color picker |
| `prints/print_lease.php` | Uses `doc_logo_path`; removed static `$logoPath = "./public/images/logo.jpg"` dead-override |

---

## Pre-conditions

1. XAMPP running, PMS accessible at `http://localhost/pms` (or configured base URL).
2. Run the migration: `migrations/20260331_a4_two_logo.sql` against the live database.
3. Logged in as a user with `settings_manage` permission.

---

## Test Cases

### TC-A4-01 — Migration runs without error
- Open phpMyAdmin (or mysql CLI) and run `20260331_a4_two_logo.sql`.
- **Expected:** No SQL errors. `system_settings` table now contains a `doc_logo_path` row for each org that had a `logo_path`.

---

### TC-A4-02 — Settings → Branding shows two upload zones

1. Navigate to **Settings → Branding**.
2. **Expected:** Page shows three sections:
   - **Brand Color** — a color picker input and "Save Color" button.
   - **System Logo** — upload zone with label "Used in the navigation sidebar and header."
   - **Document Logo** — upload zone with label "Used on invoices, reports, and lease documents."

---

### TC-A4-03 — Upload System Logo

1. In the **System Logo** zone, click the upload area.
2. Select a white/light PNG file (≤ 1 MB).
3. Click **Upload System Logo**.
4. **Expected:**
   - Toast/alert: "Logo uploaded successfully."
   - Preview image updates inside the upload zone.
   - `system_settings` row `logo_path` updated in DB.
   - Sidebar logo in app topbar/sidebar updates after page reload.

---

### TC-A4-04 — Upload Document Logo

1. In the **Document Logo** zone, click the upload area.
2. Select a full-colour PNG file (≤ 1 MB).
3. Click **Upload Document Logo**.
4. **Expected:**
   - Toast: "Logo uploaded successfully."
   - `system_settings` row `doc_logo_path` updated in DB.
   - Preview shows the uploaded image.

---

### TC-A4-05 — Lease PDF uses Document Logo

1. Open any existing lease and click **Print / Download PDF**.
2. **Expected:** The generated PDF shows the document logo (uploaded in TC-A4-04), not the old static `logo.jpg`.
3. **Regression:** If no document logo was uploaded, it falls back to the system logo (not a blank / broken image).

---

### TC-A4-06 — File size limit enforced

1. Try uploading a file > 1 MB in either zone.
2. **Expected:** Error message "File size exceeds 1MB limit." No file is saved.

---

### TC-A4-07 — Invalid file type rejected

1. Try uploading a `.pdf` or `.docx` file.
2. **Expected:** Error "Invalid file type. Allowed: jpg, png, gif, bmp."

---

### TC-A4-08 — Old logo file deleted on replacement

1. Note the filename of the current system logo in DB (`logo_path` value).
2. Upload a new system logo.
3. **Expected:** Old file is deleted from `public/images/`; new file appears; DB updated.

---

## Rollback

Run the following SQL to revert:
```sql
DELETE FROM system_settings WHERE setting_key = 'doc_logo_path';
```
Restore `prints/print_lease.php` from git.
