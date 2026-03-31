# FINALIZING — Remaining Work & Gap Analysis

**Date:** March 31, 2026  
**Status:** A1, A2, A3, A4, B1, B5 — all completed March 31, 2026.

---

## Overview

This document captures every remaining task before the system can be considered complete. It is organized into two parts:

- **Part A** — The four specific features requested by the client
- **Part B** — System-wide gaps discovered during full codebase analysis

---

## Part A — Requested Features

---

### A1. Reports: PDF Export + Excel Rebrand with New Colors ✅

**Completed Mar 31, 2026.**

**What was done:**
- Created `prints/excel/_branding.php` — shared loader for `doc_logo_path` and `brand_primary_color`.
- All 7 Excel files (`prints/excel/`) updated: hardcoded logo override removed, `4E73DF` color replaced with `$brandColorHex` from DB.
- Created `prints/report_pdf.php` — TCPDF PDF generator for all 6 report types (rent collection, unit occupancy, tenant report, outstanding balance, income vs expense, maintenance, maintenance expense). Supports dynamic logo, brand color, column auto-scaling, multi-page pagination with repeated headers.
- Added "Download PDF" button alongside "Download Excel" in `views/reports/report_display.php`.
- Added report PDF routes to `pdf.php`.

**Test document:** `A1_TEST.md`

---

### A2. Brand Color Selection in Settings ✅

**Completed Mar 31, 2026.**

**What was done:**
- Migration: `migrations/20260331_a2_brand_color.sql` — seeds `brand_primary_color = 1d3354` for all orgs.
- `save_brand_color` action added to `settings_controller.php` — validates 6-char hex, upserts to `system_settings`.
- `app/init.php` now loads `$GLOBALS['brandPrimaryColor']` on every page load.
- `views/partials/app_header.php` injects `<style>` block setting `--brand-primary`, `--sidebar-bg`, `.btn-primary` overrides from the stored color.
- Branding settings section updated with a color picker (`<input type="color">`) and "Save Color" button.
- `settings.js` wired: loads color from `get_settings` on page load, saves via `save_brand_color`, live-updates CSS vars on success.

**Test document:** `A2_TEST.md`

---

### A3. Communication Module (SMS) ✅

**Fully completed Mar 31, 2026.**

**SMS Provider:** Hormuud via [1s2u.com API v2.0](https://1s2u.com/sms/API-V2.0.pdf)

**Static defaults** (in `$GLOBALS['SMS']`, `app/helpers.php`):
| Key | Value |
|---|---|
| `msgProvider` | `hormuud` |
| `sms_sid` | `SOSTEC` |
| `sms_signature` | `SOSTEC` |
| `sms_subject` | `SMS Subject` |
| `api_url` | `https://api.1s2u.io/bulksms` |
| `userAllowance` | `10` |
| `msgAllowance` | `on` |

**Dynamic per-org** (in `system_settings`):
| Setting Key | Description |
|---|---|
| `sms_username` | API username |
| `sms_password` | API password (base64) |
| `sms_sender_name` | Sender ID shown on recipient's phone |
| `sms_enabled` | Toggle: `yes` / `no` |

**What was done:**
- Migration: `migrations/20260331_a3_communication.sql` — `sms_log` table + `communication_manage` permission + admin role assignment.
- `app/communication_controller.php` — actions: `send_sms` (calls 1s2u API, logs result), `get_sms_log` (DataTables server-side), `get_contact_list` (org-scoped tenant list).
- `views/communication/communication.php` — compose panel with Select2 tenant picker, phone field, message body, char counter + sent messages DataTable.
- `public/js/modules/communication.js` — page init, Select2 tenant search, `submitSms()`, global `openSmsModal(tenantId, tenantName, tenantPhone)`, modal handler, `submitModalSms()`.
- `views/partials/app_footer.php` — `#sendSmsModal` HTML added globally; `communication.js` loaded on every page.
- `views/settings/system_settings.php` — "Communication" sidebar group + SMS settings panel (username, sender name, password, enabled toggle).
- `app/settings_controller.php` — `save_sms_settings` action (upserts all 4 dynamic keys).
- **Integration buttons:** tenant list row action (chat icon), lease view tenant card, invoice show ("Remind Tenant"), payment show.

**Test document:** `A3_TEST.md`

---

### A4. Two-Logo System (System Logo vs. Document Logo) ✅

**Completed Mar 31, 2026.**

**What was done:**
- Migration: `migrations/20260331_a4_two_logo.sql` — seeds `doc_logo_path` for all orgs from existing `logo_path`.
- `save_branding` in `settings_controller.php` updated to accept `logo_type = system | document`, saves to `logo_path` or `doc_logo_path` accordingly; old file deleted on replacement for both types.
- `app/init.php` now loads `$GLOBALS['docLogoPath']` (URL), `$GLOBALS['docLogoLocalPath']` (filesystem-relative path), and `$GLOBALS['brandPrimaryColor']`.
- Branding settings section completely rewritten: Brand Color picker + System Logo zone + Document Logo zone side by side.
- `settings.js` updated: `previewLogoZone()`, `uploadLogoZone()`, `saveBrandColor()`; `loadSettings()` populates both logo previews and color picker.
- `prints/print_lease.php`: reads `doc_logo_path` dynamically; removed the dead `$logoPath = "./public/images/logo.jpg"` static override.
- All Excel files, report PDF, and invoice PDF use `doc_logo_path`.

**Test document:** `A4_TEST.md`

---

## Part B — System-Wide Gaps (Found During Full Analysis)

---

### B1. Auto-Invoice Settings Cannot Be Saved ✅

**Fixed Mar 31, 2026.** `case 'save_settings':` added to `settings_controller.php`.

---

### B2. No Individual Tenant Show/Profile Page

**Status:** Not started. Future sprint.

**Fix required:**
- Build `views/tenants/tenant_show.php` — personal info, ID docs, linked leases, invoice/payment history, Send SMS button.
- Add `get_tenant_show` action to `tenant_controller.php`.
- Wire routing in `autoload.php` and `.htaccess`.

---

### B3. No Individual Guarantor Show Page

**Status:** Not started. Future sprint.

---

### B4. No Individual Unit Show Page

**Status:** Not started. Future sprint.

---

### B5. Invoice PDF Is Browser-Print Only (No Server-Side PDF) ✅

**Completed Mar 31, 2026.**

**What was done:**
- Created `prints/print_invoice.php` — TCPDF invoice PDF with org header, doc logo, invoice meta, items table, totals block, payment history, footer.
- Added `invoice` route to `pdf.php`.
- Invoice show page: "Download PDF" (red) button added; "Remind Tenant" SMS button added.

**Test document:** `B5_TEST.md`

---

### B6. Dead Accounting Pages (Bills, Payments Made)

**Status:** Decision needed. Future sprint.

---

### B7. Dead JS References to Non-Existent Controllers

**Status:** Not started. Future sprint.

---

### B8. No Email Notification System

**Status:** Deferred to future version.

---

### B9. No Lease Renewal Workflow

**Status:** Deferred to future version.

---

### B10. Roles & Permissions — `communication_manage` ✅

**Completed Mar 31, 2026** — migration inserts permission and assigns to admin roles.

---

### B11. Report Display Page — No Per-Report PDF Button ✅

**Completed Mar 31, 2026** (part of A1).

---

## Summary Table

| # | Feature | Type | Priority | Status |
|---|---------|------|----------|--------|
| A1 | Reports Excel rebrand + PDF export | New feature | High | **Done** ✅ (Mar 31) |
| A2 | Brand color picker in settings | New feature | High | **Done** ✅ (Mar 31) |
| A3 | SMS / Communication module | New module | High | **Done** ✅ (Mar 31) |
| A4 | Two-logo system (system + document) | Enhancement | High | **Done** ✅ (Mar 31) |
| B1 | Auto-invoice save action missing | Bug fix | High | **Fixed** ✅ (Mar 31) |
| B2 | Tenant show/profile page | Missing page | Medium | Not started |
| B3 | Guarantor show page | Missing page | Low | Not started |
| B4 | Unit show page | Missing page | Low | Not started |
| B5 | Invoice server-side PDF (TCPDF) | Enhancement | Medium | **Done** ✅ (Mar 31) |
| B6 | Dead accounting pages (bills, payments_made) | Cleanup | Low | Decision needed |
| B7 | Dead JS controller references | Bug / cleanup | Low | Not started |
| B8 | Email notification system | New feature | Low | Future sprint |
| B9 | Lease renewal workflow | New feature | Low | Future sprint |
| B10 | `communication_manage` permission | Dependency | High | **Done** ✅ (Mar 31) |
| B11 | PDF button on report display page | Dependency | High | **Done** ✅ (Mar 31) |

---

## Migrations to Run (in order)

```
migrations/20260331_a4_two_logo.sql        — A4: doc_logo_path
migrations/20260331_a2_brand_color.sql     — A2: brand_primary_color
migrations/20260331_a3_communication.sql   — A3: sms_log + communication_manage
```

---

## Remaining Work (Future Sprint)

| Task | Notes |
|---|---|
| B2 — Tenant show page | Full profile view with leases, payments, Send SMS |
| B3 — Guarantor show page | Same pattern as B2 |
| B4 — Unit show page | Unit details, amenities, maintenance history |
| B6 — Dead accounting pages | Decide: build out or remove bills.php / payments_made.php |
| B7 — utilities.js cleanup | Remove dead payroll/hrm references |
| B8 — Email notifications | Triggered emails for rent due, lease expiry, payment receipts |
| B9 — Lease renewal workflow | Create new lease from expired, linked to previous |

---

*This document is updated as each step is completed.*
