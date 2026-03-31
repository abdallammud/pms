# PMS Upgrade Task Plan

## Execution Rules

- Apply multitenancy (`org_id`) before major feature updates to avoid rework.
- Every backend change must enforce tenant scope for non-super-admin users.
- Keep edits in modals where requested.
- Keep reports metrics unchanged (UI-only redesign).
- Dashboard can evolve in layout/UX and KPI composition.

---

## Phase 1 - Foundation: Multitenancy and Organization Context

### 1.1 Database Migration Tasks
- [ ] Create `organizations` table.
- [ ] Add `is_super_admin` and `org_id` to `users`.
- [ ] Add `org_id` columns to domain tables:
  - `system_settings`, `property_types`, `charge_types`
  - `properties`, `units`, `tenants`, `guarantees`, `leases`
  - `invoices`, `payments_received`, `expenses`
  - `vendors`, `maintenance_requests`, `maintenance_assignments`, `auto_invoice_log`
- [ ] Create new tables needed later (can be created now):
  - `property_images`
  - `unit_types`
  - `amenities`
  - `unit_amenities`
  - `unit_images`
  - `invoice_items`
  - `payment_allocations`
- [ ] Add tenant-aware indexes (`org_id` prefixed indexes).
- [ ] Seed default organization and backfill existing records to it.

### 1.2 Auth and Session Tasks
- [ ] Update login/session bootstrap to include `org_id` and `is_super_admin`.
- [ ] Add helper functions:
  - `is_super_admin()`
  - `current_org_id()`
  - `active_org_id()`
  - `enforce_tenant_scope(...)`
- [ ] Add optional super-admin organization context selector behavior.

### 1.3 Controller and Query Hardening Tasks
- [ ] Refactor all module controllers to scope queries by `org_id`.
- [ ] Ensure all creates automatically include session tenant `org_id`.
- [ ] Add record ownership checks before update/delete/view by ID.
- [ ] Verify DataTables list endpoints are tenant-filtered.

---

## Phase 2 - Navigation, Naming, Icons, and Global UI Theme

### 2.1 Menu and Labels
- [ ] Update menu config in autoload/menu builder:
  - Accounting -> Finance
  - Rent Invoices -> Invoices
  - Tenant Directory -> Tenants
  - Lease Directory -> Lease
  - Guarantees -> Guarantors
  - All Properties -> Properties
  - Units List -> Units
- [ ] Place `Guarantors` under `Tenants`.
- [ ] Make `Lease` navigation behavior standalone from old nested flow.

### 2.2 Icon Refresh
- [ ] Replace menu icons with updated icon set for better semantics.
- [ ] Review topbar quick-action icons for consistency with new labels.

### 2.3 Theme and Typography
- [ ] Set primary sidebar color to `#1d3354`.
- [ ] Remove green color usage across buttons, badges, links, hovers, active states.
- [ ] Apply recommended topbar color token and contrast-safe variants.
- [ ] Set global font family to `Manrope, Inter, "Noto Sans", sans-serif`.
- [ ] Normalize modal headers (remove mismatched colored headers).

---

## Phase 3 - Properties Module Redesign and Image System

### 3.1 Data Layer
- [ ] Add `region` and `district` columns to `properties`.
- [ ] Remove dependence on `properties.logo` in forms/UI logic.
- [ ] Create `property_images` CRUD endpoints and cover image logic.

### 3.2 Properties Page
- [ ] Redesign properties listing to card-based layout matching provided screenshot.
- [ ] Include occupancy and summary metrics on cards.
- [ ] Preserve filtering/search/pagination behavior.

### 3.3 Property Show Page
- [ ] Build property detail/show page based on provided design.
- [ ] Include:
  - overview cards
  - property details
  - occupancy section
  - image gallery
  - location details (country/city/region/district)

### 3.4 Property Add/Edit Modal
- [ ] Update add property form:
  - add `region`, `district`
  - remove property logo input
- [ ] Keep edit as modal with tabs:
  - `Basic Info`
  - `Images`
- [ ] Add image upload/delete/set-cover interactions in Images tab.

---

## Phase 4 - Units Enhancements and Settings Extensions

### 4.1 Settings Master Data
- [ ] Add settings pages and APIs for:
  - `Unit Types`
  - `Amenities`
- [ ] Add permission checks and menu entries where needed.

### 4.2 Units Schema and Logic
- [ ] Add fields to units:
  - `floor_number`
  - `room_count`
  - `is_listed`
  - optional `unit_type_id` mapping path
- [ ] Enforce business rule:
  - unit cannot be `occupied` and `is_listed = 1` simultaneously.
- [ ] Add `unit_amenities` relation.
- [ ] Add `unit_images` with cover image support.

### 4.3 Unit Add/Edit UI
- [ ] Update add/edit unit forms to include:
  - floor number
  - room count
  - amenities checklist
  - list on website checkbox
- [ ] Keep edit in modal with tabs:
  - `Basic Info`
  - `Images`

---

## Phase 5 - Tenants, Guarantors, and Lease UX Adjustments

### 5.1 Tenants UX
- [ ] Improve tenant add form visual layout and spacing.
- [ ] Keep existing data fields and validation.

### 5.2 Guarantors
- [ ] Update all UI text and routes to `Guarantors`.
- [ ] Keep DB table compatibility if table rename is deferred.

### 5.3 Lease Navigation and Consistency
- [ ] Align lease pages/actions with updated menu structure and naming.
- [ ] Apply multitenancy checks to all lease endpoints.

---

## Phase 6 - Invoice Items and Payment Allocation Overhaul

### 6.1 Invoice Model Upgrade
- [ ] Create `invoice_items` table with qty/unit_price/line_total/ordering.
- [ ] Update invoice save logic:
  - create invoice header
  - create many invoice items
  - compute header totals from items
- [ ] Support lease selection and existing key metadata.

### 6.2 Invoice UI Redesign
- [ ] Rebuild invoice add/edit form with dynamic item rows.
- [ ] Auto-calculate line totals and invoice total in UI and backend.
- [ ] Add invoice show page with itemized layout and totals.

### 6.3 Payment Model Upgrade
- [ ] Create `payment_allocations` table.
- [ ] Implement FIFO allocation engine across unpaid invoice items.
- [ ] Recompute invoice header payment status after each payment update/delete.

### 6.4 Payments UI Redesign
- [ ] Redesign payment received form:
  - show invoice items and item balances
  - show summary totals (invoice total, paid, balance)
- [ ] Add payment view page with allocation breakdown.

---

## Phase 7 - Maintenance, Expenses, and Vendor Form Redesign

### 7.1 Maintenance Request Form
- [ ] Redesign modal/page to match system theme.
- [ ] Add auto-requester behavior:
  - when selected unit is occupied, requester auto-fills tenant
  - allow manual override.

### 7.2 Assign Request Form
- [ ] Redesign assign request form to match UI standards.

### 7.3 Vendors
- [ ] Ensure vendor add/edit form is available and aligned with design system.
- [ ] Ensure `org_id` scoping in vendor CRUD and dropdown usage.

### 7.4 Expenses
- [ ] Improve expense form design and interaction consistency.
- [ ] Keep expense business logic and report compatibility intact.

---

## Phase 8 - Reports and Dashboard Redesign

### 8.1 Reports (UI-only)
- [ ] Redesign reports landing and report display pages.
- [ ] Keep existing report calculations and exported data unchanged.
- [ ] Improve filters and visual card/list design.

### 8.2 Dashboard (Recommended redesign)
- [ ] Build a new dashboard layout featuring:
  - occupancy snapshot cards
  - rent collection/receivables widgets
  - lease status summary
  - maintenance queue summary
  - recent transactions/activity
- [ ] Reuse current data sources where possible.
- [ ] Ensure tenant-scoped metrics for normal users and cross-org capability for super admin.

---

## Phase 9 - QA, Backward Compatibility, and Launch

### 9.1 QA Checklist
- [ ] Verify tenant isolation across every module.
- [ ] Verify super admin can view/switch organizations safely.
- [ ] Verify unit listing rule (`occupied` cannot be listed).
- [ ] Verify invoice item totals and FIFO payment allocations.
- [ ] Verify cover-image behavior for properties and units.
- [ ] Verify menu renaming and links do not break routes.

### 9.2 Data and Release
- [ ] Prepare migration rollback strategy.
- [ ] Run full DB backup before production migration.
- [ ] Validate legacy data after backfill.
- [ ] Execute staged deployment (schema -> backend -> frontend).
- [ ] Post-deploy smoke tests across all modules.

---

## Suggested Work Order (Sprint-Friendly)

- Sprint 1: Phase 1 + Phase 2
- Sprint 2: Phase 3 + Phase 4
- Sprint 3: Phase 5 + Phase 6
- Sprint 4: Phase 7 + Phase 8 + Phase 9

