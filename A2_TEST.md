# A2 — Brand Color Selection: Test Plan

**Feature:** Brand Color Picker in Settings  
**Date:** March 31, 2026  
**Status:** Implemented

---

## What Was Built

| File | Change |
|---|---|
| `migrations/20260331_a2_brand_color.sql` | Seeds `brand_primary_color` = `1d3354` for all existing orgs |
| `app/settings_controller.php` | New `save_brand_color` action — validates hex, upserts `brand_primary_color` |
| `app/init.php` | Loads `$GLOBALS['brandPrimaryColor']` (hex without `#`) on every page request |
| `views/partials/app_header.php` | Injects `<style>` block that sets `--brand-primary`, `--sidebar-bg`, `.btn-primary` overrides |
| `views/settings/system_settings.php` | Color picker (`<input type="color">`) in the Branding section with "Save Color" button |
| `public/js/modules/settings.js` | `saveBrandColor()` — POSTs to `save_brand_color`, live-updates CSS variable on success |

---

## Pre-conditions

1. Run migration `20260331_a2_brand_color.sql` against the database.
2. Run migration `20260331_a4_two_logo.sql` if not already done (A4 must precede A2).
3. Logged in as user with `settings_manage` permission.

---

## Test Cases

### TC-A2-01 — Migration seeds default color

- Open phpMyAdmin → `system_settings` table.
- **Expected:** A row exists with `setting_key = 'brand_primary_color'` and `setting_value = '1d3354'` for each org.

---

### TC-A2-02 — Color picker pre-populated on page load

1. Navigate to **Settings → Branding**.
2. **Expected:** The color swatch shows `#1d3354` (dark blue) or whatever color was previously saved.

---

### TC-A2-03 — Saving a new color updates DB and CSS immediately

1. Click the color picker, choose a clearly different color (e.g. `#e63946` — red).
2. Click **Save Color**.
3. **Expected:**
   - Toast: "Brand color saved successfully."
   - Sidebar background color changes to red immediately (no page reload needed).
   - Buttons (`btn-primary`) change to red.
   - DB: `brand_primary_color` value = `e63946` (without `#`).

---

### TC-A2-04 — Color persists after page reload

1. After TC-A2-03, reload the page.
2. **Expected:** Sidebar and buttons remain red. The `<style>` block in `<head>` shows `--brand-primary: #e63946`.

---

### TC-A2-05 — Invalid color rejected

- This is handled client-side by `<input type="color">` (always returns a valid hex) and server-side regex in `save_brand_color`.
- To test server-side: send a raw POST to `save_brand_color` with `brand_primary_color=ZZZZZZ`.
- **Expected:** JSON `{"error":true,"msg":"Invalid color format. Use a 6-digit hex code."}`.

---

### TC-A2-06 — Color reflected in Excel exports (covered in A1 tests)

- Download any report Excel file after changing color.
- **Expected:** Table headers use the new brand color (tested in A1_TEST.md TC-A1-02).

---

## Rollback

```sql
UPDATE system_settings SET setting_value = '1d3354' WHERE setting_key = 'brand_primary_color';
```
