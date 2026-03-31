# PMS Modernization and Multitenant Change Document

## Scope Summary

This document defines the full change specification for upgrading the PMS from single-tenant to shared-database multitenant architecture, redesigning core UI, and extending business workflows for properties, units, invoices, payments, and settings.

The target is:
- Shared DB multitenancy with strict row-level isolation by organization.
- One user belongs to one organization.
- One super admin can access and manage all organizations.
- Updated menus, naming, and icons.
- New system theme with blue-first branding.
- New image systems for properties and units.
- Invoice items workflow with FIFO payment allocation.
- Improved forms and redesigned dashboard/reports UI.

---

## 1) Multitenancy Architecture (Shared Database)

### 1.1 Tenancy Model
- Keep one shared MySQL database.
- Add `organizations` table.
- Add `org_id` to all business/domain tables.
- All non-super-admin queries must be scoped by `org_id`.
- Super admin can query across all organizations.

### 1.2 Access and Identity Rules
- Each user belongs to exactly one organization (`users.org_id` not nullable for normal users).
- Super admin user can access all organizations.
- Add a user flag to identify super admin role/capability (recommended: `users.is_super_admin TINYINT(1)`).
- Session must store:
  - `user_id`
  - `org_id` (for regular users)
  - `is_super_admin`
  - optional `active_org_id` when super admin filters by organization.

### 1.3 Organization Bootstrap
- Create a default organization.
- Backfill all current records to default organization (`org_id = default_org_id`).
- Backfill current users to default org except super admin (or also default with super privileges).

### 1.4 Data Isolation Enforcement
- Every `SELECT`, `INSERT`, `UPDATE`, `DELETE` must enforce tenant scope.
- Do not trust UI-provided `org_id`; derive tenant scope from authenticated session.
- For super admin actions:
  - allow explicit organization context selection,
  - otherwise require a selected organization for create/update actions.
- Add helper functions to centralize tenancy checks:
  - `current_org_id()`
  - `is_super_admin()`
  - `tenant_where_clause(alias_or_table)`
  - `assert_record_in_tenant(table, id)`

### 1.5 Tables Requiring `org_id`

Core:
- `users` (single-company ownership for normal users)
- `system_settings` (org-specific settings)
- `property_types`
- `unit_types` (new)
- `amenities` (new)
- `charge_types`
- `properties`
- `property_images` (new)
- `units`
- `unit_images` (new)
- `unit_amenities` (new pivot)
- `tenants`
- `guarantees` (to be renamed to guarantors in UI)
- `leases`
- `invoices`
- `invoice_items` (new)
- `payments_received`
- `payment_allocations` (new)
- `expenses`
- `vendors`
- `maintenance_requests`
- `maintenance_assignments`
- `auto_invoice_log`

RBAC:
- `roles`, `permissions`, `role_permissions` may remain global.
- `user_roles` remains user-linked (indirectly tenant-scoped through `users`).

### 1.6 Indexing and Constraints
- Add composite indexes with tenant prefix for frequent filtering:
  - `(org_id, id)`
  - `(org_id, status)`
  - `(org_id, created_at)`
  - table-specific unique keys, e.g. `UNIQUE(org_id, email)` if needed.
- Add composite foreign key strategy where appropriate, or enforce with application checks.
- Add DB check/business rule in app layer:
  - Unit cannot be both `occupied` and `is_listed = 1`.

---

## 2) Information Architecture, Menu, Labels, and Icons

### 2.1 Final Menu Structure
- `Dashboard`
- `Properties`
  - `Properties`
  - `Units`
- `Tenants`
  - `Tenants`
  - `Lease` (directory/list page moved under Tenants section naming context not submenu under old layout)
  - `Guarantors`
- `Finance` (renamed from Accounting)
  - `Invoices` (renamed from Rent Invoices)
  - `Payments Received`
  - `Expenses`
- `Maintenance`
- `Reports`
- `Settings`

Note: In routing, Lease should be standalone in navigation behavior (not nested under tenant-only flow). In tenancy domain, lease still links tenant/unit/property.

### 2.2 Terminology Changes
- Guarantees -> Guarantors.
- All Properties -> Properties.
- Units List -> Units.
- Tenant Directory -> Tenants.
- Lease Directory -> Lease.
- Rent Invoices -> Invoices.
- Accounting -> Finance.

### 2.3 Icon Refresh (Recommended)
- Dashboard: `bi-speedometer2`
- Properties: `bi-buildings`
- Units: `bi-door-open`
- Tenants: `bi-people`
- Lease: `bi-file-earmark-text`
- Guarantors: `bi-person-check`
- Finance: `bi-cash-coin`
- Invoices: `bi-receipt`
- Payments: `bi-wallet2`
- Expenses: `bi-coin`
- Maintenance: `bi-tools`
- Reports: `bi-bar-chart-line`
- Settings: `bi-gear`

---

## 3) Brand, Theme, and Typography

### 3.1 Required Color Direction
- Sidebar primary: `#1d3354`.
- Remove green accents globally.
- Replace button/link/hover/active states with blue palette.

### 3.2 Recommended Theme Tokens
- Sidebar: `#1d3354`
- Sidebar hover/active: `#274a78`
- Primary action: `#2f66c2`
- Primary hover: `#2655a5`
- Focus ring: `rgba(47, 102, 194, 0.25)`
- Top bar (recommended matching contrast): `#243b5f`
- Surface cards (dark mode style already used): harmonize with existing dark surfaces.

### 3.3 Font Recommendation
- Use `Manrope` as primary UI font for modern readability.
- Fallback stack: `Manrope, Inter, "Noto Sans", sans-serif`.
- Apply consistently across sidebar, topbar, tables, forms, modals.

### 3.4 Style Normalization
- Remove modal headers with non-system mismatched colors.
- Normalize all form controls, spacing, border radius, and focus styles.
- Ensure tables, badges, and buttons align with unified design language.

---

## 4) Properties Module Changes

### 4.1 Properties List Redesign
- Replace current table-first list with card/grid presentation matching provided reference.
- Include per-property:
  - cover image
  - property name/type/location
  - occupancy summary
  - units available/revenue quick stats
- Retain search/filter/sort behavior.

### 4.2 Add Property Form
- Add fields:
  - `region`
  - `district`
- Keep existing location fields as needed (`country/city/address` alignment).
- Remove property logo upload from basic form.

### 4.3 Property Images
- Introduce `property_images` table with:
  - `id`, `org_id`, `property_id`, `file_path`, `is_cover`, `sort_order`, timestamps.
- One property can have multiple images.
- Exactly one image can be marked as cover.

### 4.4 Property Show Page
- New dedicated property details page matching reference design:
  - summary cards (units, occupied, available, revenue)
  - details panel
  - occupancy panel
  - gallery section
  - location section (country/city/region/district)

### 4.5 Property Edit Modal
- Keep edit as modal.
- Two tabs:
  - `Basic Info`
  - `Images`
- Images tab supports upload/list/delete/set cover image.

---

## 5) Units Module Changes

### 5.1 Unit Master Data
- Add settings master table `unit_types` (similar to property types).
- Add settings master table `amenities`.

### 5.2 Unit Schema Enhancements
- Add columns:
  - `floor_number`
  - `room_count`
  - `is_listed` (boolean)
  - `unit_type_id` (optional migration path from free-text `unit_type`)
- Keep occupancy status.
- Enforce rule: if `status = occupied`, then `is_listed = 0`.

### 5.3 Unit Amenities and Images
- Add `unit_amenities` pivot table (`unit_id`, `amenity_id`, `org_id`).
- Add `unit_images` table:
  - `id`, `org_id`, `unit_id`, `file_path`, `is_cover`, `sort_order`, timestamps.
- One cover image per unit.

### 5.4 Unit Add/Edit UX
- Add/edit forms include:
  - floor number
  - number of rooms
  - amenities checklist
  - list on website checklist (`is_listed`)
- Edit remains modal with tabs:
  - `Basic Info`
  - `Images`

---

## 6) Tenants, Guarantors, and Lease

### 6.1 Tenants
- Keep tenant CRUD and improve add tenant form visual design.
- Maintain existing tenant data fields.

### 6.2 Guarantors
- Rename UI labels and menu from Guarantees to Guarantors.
- Table name can remain `guarantees` initially to reduce migration risk, with naming abstraction in code.

### 6.3 Lease
- Keep lease logic and pages but align nav/menu naming.
- Ensure all lease queries are tenant-scoped with `org_id`.

---

## 7) Finance: Invoices and Payments Redesign

### 7.1 Invoice Data Model Upgrade
- Existing invoice header table remains `invoices`.
- Add `invoice_items` table:
  - `id`, `org_id`, `invoice_id`, `charge_type_id` (optional), `description`, `qty`, `unit_price`, `line_total`, `sort_order`, timestamps.
- Invoice total must be derived from sum of items.
- Keep lease linkage on invoice header.

### 7.2 Invoice Form Redesign
- Completely redesign add/edit invoice form.
- Preserve core header fields (lease selection, dates, notes, etc.).
- Add dynamic line items grid:
  - description
  - qty
  - unit price
  - auto line total
  - add/remove item rows
- Auto-calculate subtotal/total from items.

### 7.3 Invoice Show Page
- New invoice view page showing:
  - header details
  - tenant/lease references
  - itemized lines
  - totals
  - payment summary.

### 7.4 Payments Data Model Upgrade
- Keep `payments_received` as payment headers.
- Add `payment_allocations` table:
  - `id`, `org_id`, `payment_id`, `invoice_item_id`, `allocated_amount`, timestamps.

### 7.5 FIFO Allocation Logic
- On payment create/update:
  - fetch unpaid invoice items ordered by item sequence (FIFO),
  - allocate amount sequentially,
  - persist per-item allocations,
  - recompute invoice status (`unpaid`, `partial`, `paid`).

### 7.6 Payments Form and View Redesign
- Redesign payment modal/page to display selected invoice items and balances.
- Show:
  - total invoice
  - already paid
  - remaining
  - per-item outstanding.
- Add payment view page with allocation breakdown.

---

## 8) Maintenance, Vendors, Expenses, and Form Consistency

### 8.1 Maintenance Request Form
- Redesign to match system style.
- Remove mismatched colored modal headers.
- If selected unit is occupied, auto-fill requester with tenant name (editable override).

### 8.2 Assign Request Form
- Redesign for consistency with new style and spacing.

### 8.3 Vendors
- Ensure vendor form exists and is integrated in maintenance workflow.
- Tenant-scoped vendor records by `org_id`.

### 8.4 Expenses
- Redesign expense form UI to match system standards.
- Preserve current expense metrics and behavior.

---

## 9) Settings Enhancements

### 9.1 New Settings Sections
- Add `Unit Types` management page.
- Add `Amenities` management page.
- Keep `Property Types` and other existing settings.

### 9.2 Tenant Scope in Settings
- `system_settings` should be org-scoped (except potential global keys for super admin platform config).
- For organization branding, color, and logo, values should be organization-specific.

---

## 10) Reports and Dashboard

### 10.1 Reports
- Pure UI redesign only.
- Keep existing report metrics and calculations unchanged.
- Improve layout, visual hierarchy, and filter UX.

### 10.2 Dashboard
- Full redesign (new information layout recommended) while keeping current data integrity.
- Recommended dashboard sections:
  - Occupancy snapshot
  - Receivables and collections
  - Lease health (active/expiring)
  - Maintenance pipeline
  - Recent activity feed
- Keep data sources from current modules, improve presentation and card/charts composition.

---

## 11) Security and Quality Requirements

- Enforce org scoping in all endpoints/controllers.
- Prevent cross-tenant record access by ID tampering.
- Add server-side validation for new line-item and allocation workflows.
- Normalize SQL style toward prepared statements in touched modules.
- Add migration scripts for schema updates and data backfill.
- Add regression tests/checklist for tenant isolation and finance calculations.

---

## 12) Delivery Strategy

Recommended implementation in phases:
1. Multitenancy foundation (`organizations`, `org_id`, session/auth scoping, backfill).
2. Menu/labels/theme/font/global component styling.
3. Properties and units schema + images + redesigned modals/pages.
4. Settings extensions (`unit_types`, `amenities`).
5. Invoice items + payment allocations + redesigned finance views.
6. Maintenance/expense/vendor form redesign and requester automation.
7. Reports/dashboard redesign and final QA hardening.

