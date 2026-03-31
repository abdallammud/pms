# Phase 5 – Test Plan
**Tenants, Guarantors & Lease UX Adjustments**

---

## Pre-requisites
- Phases 1–4 migrations applied and working.
- At least one tenant and one guarantor in the database.

---

## 5.1 Tenant Add Form Redesign

| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Navigate to **Tenants** and click **Add Tenant** | Modal opens with new modern layout |
| 2 | Verify form sections | "Personal Information", "Identity Documents", "Employment" section headers visible |
| 3 | Verify icons on inputs | Phone, email, ID Number inputs have Bootstrap Icon prefixes |
| 4 | Click the ID Photo upload zone | File picker opens; zone has dashed border |
| 5 | Select an image file | Preview appears inside the zone; remove button (×) appears |
| 6 | Click the × button | Preview cleared; placeholder restored |
| 7 | Submit form without required fields | HTML5 validation fires on Full Name, Phone, ID Number |
| 8 | Fill all required fields and save | Tenant saved; success toast shown; DataTable refreshes |
| 9 | Edit the same tenant | Existing ID photo previews correctly in the zone |

---

## 5.2 Guarantors Rename

| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Navigate to **Tenants → Guarantors** in sidebar | Sub-menu shows "Guarantors" (not "Guarantees") |
| 2 | Open the Guarantors page | Page title reads "Guarantors" |
| 3 | Click the Add button | Button label is "Add Guarantor" |
| 4 | Open Add modal | Modal title reads "Add Guarantor" |
| 5 | Save button label | "Save Guarantor" |
| 6 | Add a guarantor and save | Record saved; DataTable refreshes |

---

## 5.3 Lease Navigation (regression check)

| # | Action | Expected Result |
|---|--------|-----------------|
| 1 | Click **Lease** in the main sidebar | Standalone menu item (no dropdown) |
| 2 | URL after click | `/leases` – should load lease list, not redirect to tenants |
| 3 | Add Lease button | Opens `add_lease` form at `/add_lease` |
| 4 | View/Edit an existing lease | Links navigate correctly |
| 5 | Lease actions respect `org_id` | Users only see their org's leases |

---

## General Regression Checks
- Tenant table loads via DataTable with correct columns.
- Guarantor table loads via DataTable with correct columns.
- Both forms handle edit mode (populate existing data).
- Multitenancy: tenants/guarantors scoped to `org_id`.
