# A1 — Reports Excel Rebrand + PDF Export: Test Plan

**Feature:** Excel rebrand with brand color + document logo; TCPDF PDF export for all 6 report types  
**Date:** March 31, 2026  
**Status:** Implemented  
**Dependencies:** A4 (doc_logo_path) and A2 (brand_primary_color) must be run first.

---

## What Was Built

| File | Change |
|---|---|
| `prints/excel/_branding.php` | Shared loader: reads `doc_logo_path` + `brand_primary_color` from DB; sets `$logoPath` and `$brandColorHex` |
| `prints/excel/rent_collection.php` | Uses `_branding.php`; removed static logo override; `4E73DF` → `$brandColorHex` |
| `prints/excel/income_expense.php` | Same |
| `prints/excel/outstanding_balance.php` | Same |
| `prints/excel/tenant_report.php` | Same |
| `prints/excel/unit_occupancy.php` | Same |
| `prints/excel/maintenance_report.php` | Same |
| `prints/excel/maintenance_expense.php` | Same |
| `prints/report_pdf.php` | TCPDF report PDF — all 6 report types, dynamic logo + brand color, auto-scaled columns |
| `pdf.php` | Added routes for all 6 report types and `invoice` |
| `views/reports/report_display.php` | Added "Download PDF" button next to "Download Excel" |

---

## Pre-conditions

1. A4 and A2 migrations run.
2. A document logo uploaded (Settings → Branding → Document Logo).
3. Brand color set (Settings → Branding → Brand Color).
4. TCPDF present at `public/tcpdf/tcpdf.php`.

---

## Test Cases

### TC-A1-01 — Excel: Document logo appears (not static logo.jpg)

1. Go to **Reports → Rent Collection**, run for any date range.
2. Click **Download Excel**.
3. Open the XLSX file.
4. **Expected:** The logo in cell A1 is the document logo uploaded in A4 (not `logo.jpg`).
5. **Regression:** If no doc logo is set, falls back to system logo (no broken image in Excel).

---

### TC-A1-02 — Excel: Header color matches brand color

1. Change brand color to `#e63946` (red) in Settings.
2. Download any Excel report.
3. **Expected:** Header row background and title text are red (`E63946`), not the old blue `4E73DF`.

---

### TC-A1-03 — PDF: Download PDF button visible on report display page

1. Navigate to **Reports**, run any report.
2. **Expected:** The report display page shows two buttons: **Download Excel** (green) and **Download PDF** (red).

---

### TC-A1-04 — PDF: Rent Collection PDF downloads and is readable

1. Run Rent Collection report for any date range.
2. Click **Download PDF**.
3. **Expected:**
   - Browser downloads a `.pdf` file named `Rent_Collection_Report_YYYY-MM-DD.pdf`.
   - PDF opens in reader, shows: org logo (top-left), report title (right), date range, table with columns Date / Receipt # / Tenant / Property / Unit / Amount.
   - Header row uses brand color.
   - Totals row at the bottom.

---

### TC-A1-05 — PDF: All 6 report types produce a PDF

Repeat TC-A1-04 for:
- Unit Occupancy
- Tenant Report
- Outstanding Balance
- Income vs Expense
- Maintenance Report
- Maintenance Expense

**Expected for each:** PDF downloads, correct columns, brand color headers, totals row where applicable.

---

### TC-A1-06 — PDF: Multi-page reports paginate correctly

1. Run a report that returns > 40 rows.
2. **Expected:** PDF spans multiple pages; column headers are repeated on each page. Footer shows "Page X of Y".

---

### TC-A1-07 — PDF: No branding set (empty DB)

1. In a fresh org with no logo and default color:
2. Download any report PDF.
3. **Expected:** PDF generates without PHP errors. Default color `#1D3354` used. No logo image (gracefully omitted).

---

## Rollback

- Revert `prints/excel/_branding.php` deletion (remove the file) to restore old behavior.
- Revert each Excel file to query `logo_path` and use hardcoded `4E73DF` color.
- Remove the PDF button from `report_display.php`.
