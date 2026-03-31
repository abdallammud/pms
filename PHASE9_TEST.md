# Phase 9 – QA, Backward Compatibility & Launch Checklist

---

## 9.1 Full Multitenancy Isolation QA

### Create two test organizations: Org A and Org B

| # | Module | Test |
|---|--------|------|
| 1 | Properties | Org A properties not visible when logged into Org B |
| 2 | Units | Units scoped to property; no cross-org visibility |
| 3 | Tenants | Tenant list empty for Org B if all belong to Org A |
| 4 | Guarantors | Same isolation as tenants |
| 5 | Leases | Active leases scoped per org |
| 6 | Invoices | Invoice list returns only current org records |
| 7 | Payments | Payments tied to invoices; org-scoped |
| 8 | Invoice Items | `invoice_items.org_id` matches parent invoice's org |
| 9 | Payment Allocations | `payment_allocations.org_id` matches payment's org |
| 10 | Expenses | Only current org's expenses shown |
| 11 | Maintenance Requests | Requests scoped to org |
| 12 | Vendors | Org A vendors not selectable in Org B |
| 13 | Roles / Permissions | Org A roles not available to Org B users |
| 14 | Settings (property types, unit types, amenities) | Each org has own settings records |

---

## 9.2 Super-Admin QA

| # | Test |
|---|------|
| 1 | Log in as super-admin (`is_super_admin = 1`) |
| 2 | Organizations menu appears in Settings |
| 3 | Can create / edit / delete organizations |
| 4 | Org switcher in top bar works |
| 5 | Switching to Org B shows Org B data everywhere |
| 6 | Dashboard stats update on org switch |
| 7 | Regular user cannot access `/organizations` route |

---

## 9.3 Unit Business Rule

| # | Test |
|---|------|
| 1 | Create a unit with status = `occupied`; check `is_listed` checkbox | Save blocked client-side |
| 2 | POST directly with `status=occupied&is_listed=1` | Server returns error |
| 3 | Set unit to `vacant`; check `is_listed` | Saves successfully |
| 4 | Mark listed unit as occupied | Error; `is_listed` cleared or blocked |

---

## 9.4 Invoice Items & FIFO Payment QA

| # | Test |
|---|------|
| 1 | Create invoice with 3 line items | Items saved in `invoice_items`; invoice total = sum |
| 2 | Pay partially | FIFO: first item filled first |
| 3 | Verify `payment_allocations` records created | COUNT = number of items touched |
| 4 | Verify item `amount_paid` and `balance` correct | |
| 5 | Invoice status = `partial` | |
| 6 | Pay remaining balance | Invoice status = `paid` |
| 7 | Delete first payment | Allocations removed; items reverted; status = `partial` |
| 8 | Delete all payments | All items at full balance; status = `unpaid` |
| 9 | Edit invoice items (add item) | Old allocations cleared; new items created; total updated |
| 10 | Try to pay more than balance | Rejected; error message shown |

---

## 9.5 Cover Image Behavior

| # | Test |
|---|------|
| 1 | Upload 3 property images | First uploaded is auto-set as cover |
| 2 | Set a different image as cover | `is_cover` switches; old cover loses flag |
| 3 | Delete the cover image | Next remaining image auto-promoted as cover |
| 4 | Delete all images | No cover image; property card shows default placeholder |
| 5 | Same tests for unit images | Same behavior |

---

## 9.6 Menu Naming & Route Regression

| # | Route | Expected |
|---|-------|----------|
| 1 | `/properties` | Properties card grid |
| 2 | `/property/{id}` | Property show page |
| 3 | `/units` | Properties page → Units tab |
| 4 | `/tenants` | Tenants list |
| 5 | `/leases` | Lease list (standalone; no tenants redirect) |
| 6 | `/invoices` | Invoice list |
| 7 | `/invoice/{id}` | Invoice show page |
| 8 | `/payments_received` | Payments list |
| 9 | `/payment/{id}` | Payment show page |
| 10 | `/unit_types` | Unit Types settings |
| 11 | `/amenities` | Amenities settings |
| 12 | `/organizations` | Blocked for non-super-admin |

---

## 9.7 Reports Calculation Regression

| # | Test |
|---|------|
| 1 | Rent Collection report | Totals match `payments_received` sum for period |
| 2 | Outstanding Balance report | Matches invoices where `status IN ('unpaid','partial')` |
| 3 | Income vs Expense | Income = payments; Expense = `expenses` table sum |
| 4 | Unit Occupancy | Counts match `units.status` |
| 5 | Maintenance Report | Correct counts by status |

---

## 9.8 UI / UX Regression

| # | Check |
|---|-------|
| 1 | Sidebar brand color is `#1d3354` |
| 2 | No green colors in sidebar, buttons, or badges (except success states) |
| 3 | Font is Manrope globally |
| 4 | All modal headers are neutral (no colored headers) |
| 5 | Bootstrap Icons used throughout (no Font Awesome) |
| 6 | Responsive: sidebar collapses on mobile |
| 7 | DataTables have correct column counts |

---

## 9.9 Security Checks

| # | Check |
|---|-------|
| 1 | Cannot access any page without logging in |
| 2 | Cannot access a page without having the required permission |
| 3 | Cannot edit/delete another org's records by manipulating IDs |
| 4 | File uploads restricted to images; oversized files rejected |
| 5 | SQL inputs are escaped / parameterized |

---

## 9.10 Performance Sanity

| # | Check |
|---|-------|
| 1 | Dashboard loads in < 3 seconds with 100+ records |
| 2 | DataTables use server-side processing (not loading all rows) |
| 3 | Image uploads limited to 5 MB per image |
