# B5 — Invoice Server-Side PDF: Test Plan

**Feature:** TCPDF-based invoice PDF download  
**Date:** March 31, 2026  
**Status:** Implemented  
**Dependencies:** A4 (doc_logo_path), A2 (brand_primary_color)

---

## What Was Built

| File | Change |
|---|---|
| `prints/print_invoice.php` | TCPDF invoice PDF: org header with logo, invoice meta, items table, totals block, payment history, footer |
| `pdf.php` | Added `invoice` route → `prints/print_invoice.php` |
| `views/accounting/invoice_show.php` | Added **Download PDF** button; "Remind Tenant" SMS button (shown when tenant has a phone) |

---

## Pre-conditions

1. At least one invoice exists with items and at least one payment.
2. TCPDF at `public/tcpdf/tcpdf.php`.
3. A4 and A2 migrations run; document logo and brand color set.

---

## Test Cases

### TC-B5-01 — "Download PDF" button appears on invoice show page

1. Open any invoice detail page.
2. **Expected:** Top-right action area shows three buttons: **Print**, **Download PDF** (red), and **Record Payment** (if not fully paid).

---

### TC-B5-02 — PDF downloads with correct content

1. Click **Download PDF** on an invoice with items and at least one payment.
2. **Expected:**
   - File named `Invoice_INV-XXXXX.pdf` downloads.
   - PDF shows: org name + logo top-left, "INVOICE" title band in brand color.
   - Invoice number, date, due date, period.
   - "BILLED TO" block: tenant name, property, unit, phone.
   - Items table with Description / Qty / Unit Price / Line Total / Balance.
   - Totals block: Subtotal, Amount Paid, Balance Due (brand-color background on balance row).
   - Payment History table.
   - Footer: "Thank you…" + org name + generation timestamp.

---

### TC-B5-03 — Invoice with no items still generates PDF

1. Open an older single-line invoice (no `invoice_items` rows, just `total_amount` on the invoice record).
2. Click **Download PDF**.
3. **Expected:** PDF generates. Items table shows one row: "Rent" / 1 / total amount / balance.

---

### TC-B5-04 — Paid invoice: status badge shows "PAID"

1. Open a fully-paid invoice and download PDF.
2. **Expected:** Green status badge "PAID" appears in the invoice details section.

---

### TC-B5-05 — Overdue invoice: status badge shows "OVERDUE"

1. Open an overdue invoice and download PDF.
2. **Expected:** Red status badge.

---

### TC-B5-06 — Remind Tenant button invokes SMS modal

1. Open an invoice where the tenant has a phone number.
2. **Expected:** A blue **Remind Tenant** button appears.
3. Click it.
4. **Expected:** `#sendSmsModal` opens with tenant fields pre-filled and locked (implemented in A3).

---

## Rollback

Remove `prints/print_invoice.php`.  
Revert `invoice_show.php` action buttons to original single "Print / PDF" button.  
Remove `invoice` route from `pdf.php`.
