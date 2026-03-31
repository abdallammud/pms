# Phase 7 – Test Plan
**Maintenance, Expenses, and Vendor Form Redesign**

---

## Pre-requisites
- Phases 1–6 applied and working.
- At least one property, vendor, and maintenance request exist.

---

## 7.1 Create Maintenance Request Modal

| # | Action | Expected |
|---|--------|----------|
| 1 | Open Maintenance → **Create Request** | Full-width modal with two-column card layout |
| 2 | Verify Priority buttons | Three toggle buttons (Low / Medium / High); Medium active by default |
| 3 | Click **High** priority | Button turns red; hidden field `priority` = "high" |
| 4 | Select a **Property** | Unit dropdown populates with units for that property |
| 5 | Select a **Vacant unit** | No auto-fill of requester; unit status icon shows empty house |
| 6 | Select an **Occupied unit** | Requester field auto-fills with tenant name; info hint shows |
| 7 | Edit requester manually after auto-fill | Auto-fill hint disappears |
| 8 | Click pencil (clear) button | Requester field cleared; user can type manually |
| 9 | Submit without required fields | HTML5 validation fires |
| 10 | Submit complete form | Request saved; success toast; DataTable refreshes |
| 11 | Edit an existing request | Status field becomes visible; form pre-populates |
| 12 | Close modal | Form resets; priority back to Medium |

---

## 7.2 Assign Request Modal

| # | Action | Expected |
|---|--------|----------|
| 1 | Click **Assign Request** button | Modal opens with two-column card layout |
| 2 | Open the request dropdown | Only shows requests from current org (pending/in-progress) |
| 3 | Select a request | Request preview card fills: reference, property, priority |
| 4 | Select vendor | Shows vendor service type as subtext |
| 5 | Assigned date defaults | Today's date |
| 6 | Submit | Assignment saved; success toast; DataTable refreshes |
| 7 | When opened via `assignRequest(id)` | Request pre-selected |

---

## 7.3 Vendor CRUD

| # | Action | Expected |
|---|--------|----------|
| 1 | Go to **Maintenance → Vendors / Staff** | Page loads with DataTable |
| 2 | Add a vendor | Form saves with org_id; vendor visible only in current org |
| 3 | Edit vendor | Form pre-fills; saves correctly |
| 4 | Delete vendor with active assignments | Error message; delete blocked |
| 5 | Delete vendor without assignments | Deleted; DataTable refreshes |
| 6 | Assign request dropdown | Shows only vendors from current org |

---

## 7.4 Expenses Form

| # | Action | Expected |
|---|--------|----------|
| 1 | Open Finance → Expenses → **Add Expense** | Improved modal with icons on inputs |
| 2 | Select **General** expense type | Property field hides and is no longer required |
| 3 | Select **Property Expense** type | Property field shows and becomes required |
| 4 | Property dropdown | Shows only properties from current org |
| 5 | Save expense | Saved with correct org_id |
| 6 | Edit existing expense | Form pre-fills correctly |

---

## 7.5 Multitenancy Regression

| # | Check |
|---|-------|
| 1 | Maintenance requests list only shows current org's records |
| 2 | Vendor list only shows current org's vendors |
| 3 | Assign request dropdown only lists current org's pending requests |
| 4 | Super-admin switching org updates all lists |
