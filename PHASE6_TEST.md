# Phase 6 – Test Plan
**Invoice Items & Payment Allocation Overhaul**

---

## Pre-requisites
1. Run migration: `migrations/20260326_phase6_invoice_items.sql`
2. Confirm `invoice_items` and `payment_allocations` tables exist.
3. Existing invoices should each have one backfilled item row.
4. At least one active lease exists.

---

## 6.1 Migration Verification

| # | Check | Expected |
|---|-------|----------|
| 1 | `SELECT COUNT(*) FROM invoice_items` | ≥ 0 (≥ number of existing invoices if any existed) |
| 2 | `SELECT COUNT(*) FROM payment_allocations` | 0 (or populated if backfill was run with existing payments) |
| 3 | Each existing invoice has ≥ 1 item | `SELECT invoice_id, COUNT(*) FROM invoice_items GROUP BY invoice_id` |
| 4 | Item `balance` = `line_total − amount_paid` for backfilled rows | Verify a few rows manually |

---

## 6.2 Create Invoice with Line Items

| # | Action | Expected |
|---|--------|----------|
| 1 | Go to **Finance → Invoices** → click **Create Invoice** | Modal opens with line-items table on the right |
| 2 | Select a lease (Rent Invoice type) | First item row auto-fills with rent amount from lease |
| 3 | Click **Add Item** | New empty row appended to items table |
| 4 | Enter description, qty, unit price, tax % | Line total auto-calculates: `qty × price × (1 + tax%)` |
| 5 | Totals row at bottom | Subtotal, Tax, Grand Total update live |
| 6 | Remove a row (× button) with ≥ 2 rows | Row removed; totals recalculate |
| 7 | Try to remove last row | Alert: "must have at least one item" |
| 8 | Save Invoice | `invoice_items` rows created; `invoices.amount` = sum of items |
| 9 | Open the saved invoice in DataTable | Amount column matches total of items |

---

## 6.3 Edit Invoice – Line Items Preserved

| # | Action | Expected |
|---|--------|----------|
| 1 | Click edit on existing invoice | Modal opens; existing items loaded in table |
| 2 | Change a unit price | Totals update in real time |
| 3 | Add a new item, save | New item saved; invoice total updated |
| 4 | Edit invoice that already has partial payment | Items still editable; existing `payment_allocations` removed and recalculated |

---

## 6.4 Invoice Show Page

| # | Action | Expected |
|---|--------|----------|
| 1 | Click the **Eye (View)** icon on any invoice row | Navigates to `/invoice/{id}` |
| 2 | Page loads | Hero cards show: Tenant, Unit, Invoice date, Due date, Status badge |
| 3 | Summary cards | Subtotal, Tax, Total, Paid, Balance displayed correctly |
| 4 | Items table | Columns: Description, Qty, Unit Price, Tax %, Tax Amt, Line Total, Allocated, Balance |
| 5 | Payments Received section | Lists all payments for this invoice |
| 6 | Each payment row has a **view link** | Clicking navigates to `/payment/{id}` |
| 7 | "Record Payment" button for unpaid/partial invoice | Opens payment modal with invoice pre-selected |
| 8 | Paid invoice | "Record Payment" button not shown |

---

## 6.5 Record Payment – Invoice Items Panel

| # | Action | Expected |
|---|--------|----------|
| 1 | **Finance → Payments Received** → **Record Payment** | Modal opens; invoice dropdown shows only unpaid/partial invoices |
| 2 | Select an invoice | Summary bar appears: Total, Already Paid, Balance Due |
| 3 | Items panel appears | Table with all items, their line totals, already paid, balance |
| 4 | Amount Paid field pre-filled | Pre-filled with remaining balance |
| 5 | Change Amount Paid | "This Payment" column updates (FIFO preview) |
| 6 | Enter amount exceeding balance | Warning shown; Save disabled |
| 7 | Enter valid partial amount | FIFO fills first item(s) first; remaining items show — |
| 8 | Save Payment | Payment recorded; allocations written to `payment_allocations` |
| 9 | Open invoice show page | Amount paid/balance updated; items show allocated amounts |
| 10 | Invoice status | `partial` if partially paid; `paid` if fully paid |

---

## 6.6 FIFO Allocation Logic

| # | Scenario | Expected |
|---|----------|----------|
| 1 | Invoice with 3 items: $100, $50, $200. Pay $120 | Item 1 ($100) fully allocated; Item 2 gets $20; Item 3 unchanged |
| 2 | Pay remaining $230 | Item 2 gets $30; Item 3 gets $200; invoice status = `paid` |
| 3 | Delete first payment | `payment_allocations` removed; items reverted; invoice = `partial` |
| 4 | Delete second payment | All items back to unpaid; invoice = `unpaid` |

---

## 6.7 Payment Show Page

| # | Action | Expected |
|---|--------|----------|
| 1 | Click **Eye** on a payment row | Navigates to `/payment/{id}` |
| 2 | Page loads | Receipt number, Date, Amount, Method, Tenant, Unit displayed |
| 3 | Invoice Reference section | Links to `/invoice/{id}` |
| 4 | Allocation Breakdown table | Rows show item description, line total, and allocated amount |
| 5 | Total Allocated | Matches `amount_paid` on the payment |

---

## 6.8 Multitenancy & Security

| # | Check | Expected |
|---|-------|----------|
| 1 | `invoice_items` uses `org_id` | Items only visible for current org |
| 2 | `payment_allocations` uses `org_id` | Allocations scoped to org |
| 3 | Super-admin switching org | Invoice/payment data switches context |

---

## 6.9 Regression Checks

| # | Check |
|---|-------|
| 1 | Bulk generate rent invoices still works (each auto-creates one item) |
| 2 | Invoice list DataTable loads correctly with new View button |
| 3 | Receipt list DataTable loads correctly with new View button |
| 4 | Existing paid invoices remain paid after migration |
| 5 | Delete invoice cascades to delete its items and allocations |
