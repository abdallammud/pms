# Phase 8 – Test Plan
**Reports and Dashboard Redesign**

---

## Pre-requisites
- At least a few properties, leases, invoices, payments, and maintenance requests exist.
- Phases 1–7 completed.

---

## 8.1 Dashboard – KPI Cards

| # | Check | Expected |
|---|-------|----------|
| 1 | Navigate to **Dashboard** | Six KPI cards render in a responsive row |
| 2 | **Properties** card | Matches `SELECT COUNT(*) FROM properties WHERE org_id=?` |
| 3 | **Total Units** card | Matches total units for org |
| 4 | **Occupancy Rate** card | Shows percentage (occupied / total) with sub-label |
| 5 | **Active Tenants** card | Matches distinct tenant count on active leases |
| 6 | **This Month Income** card | Sum of `payments_received.amount_paid` for current calendar month |
| 7 | **Outstanding** card | Sum of unpaid/partial invoice balances |
| 8 | Page loads without JS errors | Browser console clear |

---

## 8.2 Dashboard – Charts

| # | Chart | Expected |
|---|-------|----------|
| 1 | Income vs Expense bar chart | 6 bars per month; Income (navy), Expense (red); last 6 months |
| 2 | Occupancy doughnut | Occupied (navy) vs Vacant (grey); cutout doughnut style |
| 3 | Chart axis labels | Y-axis formatted as `$N` |
| 4 | Chart legend | Shows correctly at bottom / hidden as configured |

---

## 8.3 Dashboard – Lease Summary Widget

| # | Check | Expected |
|---|-------|----------|
| 1 | Four tiles: Active, Expiring Soon, Expired, Terminated | Count displayed in each tile |
| 2 | **Expiring Soon** count | Leases expiring within next 30 days |
| 3 | Colors | Active = green, Expiring = yellow, Expired = red, Terminated = grey |

---

## 8.4 Dashboard – Open Maintenance Widget

| # | Check | Expected |
|---|-------|----------|
| 1 | Table shows up to 5 open requests | Priority-sorted: High first |
| 2 | Priority badge colors | High = red, Medium = yellow, Low = green |
| 3 | Assigned / Unassigned | Correct vendor name or "Unassigned" |
| 4 | "View All" link | Navigates to Maintenance page |

---

## 8.5 Dashboard – Rent Receivables Widget

| # | Check | Expected |
|---|-------|----------|
| 1 | Shows unpaid/partial invoices | Up to 10 entries |
| 2 | Overdue due dates | Shown in red bold |
| 3 | Balance column | Correctly calculated `line_total − allocated` |
| 4 | Invoice ref link | Navigates to `/invoice/{id}` |
| 5 | Status badge | "Unpaid" (red) or "Partial" (yellow) |

---

## 8.6 Dashboard – Upcoming Lease Expirations

| # | Check | Expected |
|---|-------|----------|
| 1 | Shows leases expiring in next 30 days | Up to 5 entries |
| 2 | Expiry date highlighted | Yellow/orange |
| 3 | "View All" link | Navigates to Leases |

---

## 8.7 Dashboard – Recent Payments

| # | Check | Expected |
|---|-------|----------|
| 1 | Shows last 5 payments received | Correct tenant name, property, amount, date, method |
| 2 | Method badge | Cash = green, Mobile = cyan, Bank = blue |

---

## 8.8 Reports Hub Redesign

| # | Check | Expected |
|---|-------|----------|
| 1 | Navigate to **Finance → Reports** | Card grid layout with 7 report cards |
| 2 | Cards have icon, title, description, color | Visually distinct per report type |
| 3 | Quick Range dropdown | Changes start/end date fields |
| 4 | Click **View Report** on a card | Submits form with correct `report_type` and dates |
| 5 | Report display page loads | Existing report calculations unchanged |
| 6 | "This Month" quick range | Sets start = 1st of current month, end = today |
| 7 | "Last Month" | Correct previous month range |
| 8 | "This Quarter" | Correct quarter start/end |
| 9 | "This Year" | Jan 1 → Dec 31 of current year |

---

## 8.9 Multitenancy

| # | Check |
|---|-------|
| 1 | All dashboard stats are tenant-scoped |
| 2 | Super admin can switch org and see different stats |
| 3 | Receivables widget only shows current org's invoices |
