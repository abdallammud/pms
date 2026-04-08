# Kaad PMS Property Management System (PMS)

A full-featured, multi-tenant web application for managing residential and commercial properties — built on PHP, MySQL, Bootstrap 5, and jQuery.

---

## Overview

Kaad PMS PMS is a comprehensive property management platform designed for property owners, managers, and real estate companies. It centralises everything — from listing properties and managing tenants to billing, maintenance tracking, and financial reporting — in a single, clean web interface.

The system supports **multiple organisations** under one installation (SaaS-ready), with strict data isolation between tenants and a role-based access control system that can be tuned per user.

---

## Key Highlights

- **Multi-organisation (multi-tenant)** — manage unlimited organisations; super admins can switch between orgs in real time
- **Role-Based Access Control (RBAC)** — fine-grained permission system with custom roles per organisation
- **Clean URL routing** — `/property/1`, `/invoice/5`, `/payment/3` etc. via `.htaccess` rewrites
- **AJAX-first UI** — all lists use server-side DataTables; modals submit via AJAX — no full-page reloads
- **FIFO payment allocation** — payments are automatically split across invoice line items in order
- **Bulk operations** — bulk rent invoice generation, bulk delete, bulk status changes
- **Dark / light / themed** — Bootstrap 5 with multiple theme variants (dark, blue, semi-dark, bordered)
- **Photo uploads** — tenant ID photos, guarantor photos, unit images, property cover images with multi-image gallery
- **Print-to-PDF** — fully styled invoice print window generated client-side

---

## Modules

### 1. Dashboard

The command-centre view that loads on login.

| Widget | Description |
|--------|-------------|
| **KPI Cards** | Total properties, occupied units, active leases, open maintenance requests |
| **Receivables Chart** | Visual breakdown of outstanding invoice balances |
| **Income vs Expense** | Chart.js bar/line chart comparing monthly income to expenses |
| **Lease Summary** | Upcoming lease expirations and recently signed leases |
| **Maintenance Queue** | Latest open maintenance requests with priority badges |

---

### 2. Properties

Manage your property portfolio with detailed records per property and per unit.

#### Properties List
- Filterable card grid with search by name, city, or owner
- Filter by property type and occupancy status
- Property count badge that updates as you filter
- Quick-view card with cover image, location, unit summary, and owner/manager tags

#### Property Detail Page (`/property/{id}`)
- Cover image displayed prominently with a full image gallery
- Details panel: property type, full address (street, city, district, region), owner, manager, phone, date added
- Description callout box
- KPI cards: total units, occupied, vacant, occupancy rate
- Units table with status indicators and linked lease information

#### Property Management
- **Add / Edit property** — name, type, address, city, district, region, description, owner, manager assignment
- **Multiple property images** — upload, reorder, set cover image, delete individual images
- **Property types** — custom configurable types (Residential, Commercial, etc.)

#### Units
- Full units list with property filter and status filter
- **Add / Edit unit** — unit number, type (studio, 1BR, 2BR…), floor, area (sqm), rent amount, amenities
- **Unit images** — upload gallery per unit, set cover image
- **Amenities** — tag units with configurable amenity labels (Parking, WiFi, Gym, etc.)
- Unit occupancy status automatically reflects active lease state
- Bulk actions: activate, deactivate, delete

---

### 3. Tenants

Full tenant directory with identity and employment records.

- **Tenant list** — searchable DataTable with quick-actions
- **Add / Edit tenant** — personal info (name, DOB, gender, nationality), contact details, ID documents (type, number, expiry), employment/work info, tenant status
- **ID photo upload** — drag-and-drop photo zone with instant preview
- **Bulk actions** — delete, change status

---

### 4. Guarantors

Manage guarantors (co-signers) linked to tenant records.

- **Guarantors list** — searchable DataTable
- **Add / Edit guarantor** — personal info, identity documents, employment info, status
- **ID photo upload** — same modern photo upload zone as tenants
- Sections: Personal Information, Identity Documents, Employment / Work Information

---

### 5. Leases

Full lease lifecycle management from signing to expiry.

- **Leases list** — DataTable with status badges (active, expired, terminated)
- **Add lease** (full-page form) — select property → unit → tenant, set start/end dates, monthly rent, deposit amount, payment day, terms and conditions
- **Edit lease** — all fields editable; unit and tenant cascading dropdowns
- **View lease** (full-page detail) — lease summary, linked tenant, unit/property info, payment history
- **Lease conditions** — configurable standard clauses that auto-populate new lease forms
- Bulk actions: terminate, delete

---

### 6. Accounting

The financial engine of the system, split into three sub-sections.

#### Invoices
- **Invoice list** — DataTable with invoice number, tenant, amount, status (unpaid / partial / paid), due date
- **Add invoice** — select lease, choose invoice type (Rent or Other Charges), add multiple line items with description, quantity, unit price, tax; line totals calculate live
- **Bulk rent generation** — generate rent invoices for all active leases in one click for a selected month
- **Duplicate check** — warns if a rent invoice already exists for the selected lease/month
- **Invoice Detail Page** (`/invoice/{id}`)
  - Full invoice card: tenant, unit, property, charge type, billing period, status badge
  - Line items table with subtotal, tax, and grand total
  - Payments received section showing each payment applied
  - Balance due banner
  - **Print / PDF** — opens a fully formatted print window with company logo, contact info, invoice details, billed-to section, line items, payment summary, and footer; print or save as PDF directly from the browser
  - **Record Payment** button (opens payment modal pre-loaded with this invoice)

#### Payments Received
- **Payments list** — receipt number, invoice reference, tenant, amount, method, date
- **Record payment modal** — two-panel layout:
  - *Left panel:* select invoice, received date, amount, payment method (Cash / Mobile / Bank Transfer), notes
  - *Right panel:* automatically fetches and displays all invoice line items with their balances the moment an invoice is selected
  - **FIFO allocation preview** — as you type the amount, the preview shows exactly how much will be applied to each line item in order
  - Over-payment warning if amount exceeds invoice balance
- **Payment Detail Page** (`/payment/{id}`) — receipt info card, invoice reference link, full allocation breakdown table
- Edit and delete payments (recalculates invoice status automatically)

#### Expenses
- **Expenses list** — DataTable with category, property, amount, date
- **Add / Edit expense** — description, category, property (optional), amount, date, notes
- Bulk delete

---

### 7. Maintenance

Track and manage property maintenance requests from submission to resolution.

#### Requests
- **Requests list** — DataTable with request title, property, unit, priority (Low / Medium / High / Urgent), status (Open / In Progress / Resolved / Closed), and requester
- **Create request** — title, description, property, unit (optional), priority, status; requester auto-filled from the logged-in user
- **Assign request** — assign a pending request to a vendor or staff member with notes; due date tracking
- **Status badges** — colour-coded priority and status indicators
- Bulk actions: close, delete

#### Vendors
- **Vendors list** — name, contact, specialty, status
- **Add / Edit vendor** — company name, contact person, phone, email, specialty, notes, status
- Vendor directory used when assigning maintenance requests

---

### 8. Reports

Dedicated reports hub with six report types.

| Report | Description |
|--------|-------------|
| **Rent Collection** | Invoices raised vs collected per period, collection rate |
| **Occupancy Report** | Unit occupancy rates by property and date range |
| **Tenant Report** | Tenant directory with lease and payment status |
| **Outstanding Balances** | All unpaid and partially paid invoices with aging |
| **Income & Expense** | Period comparison of all income (payments) vs expenses |
| **Maintenance Cost** | Maintenance request counts and associated costs |

Each report has date-range filters and property filters. Reports render in a dedicated full-page view.

---

### 9. Settings

Full system configuration, accessible by administrators.

#### Users & Roles
- **Users list** — name, email, role, status
- **Add / Edit user** — assign role, set password, activate/deactivate
- **Roles list** — custom roles with configurable permission sets
- **Role permissions** — granular permission flags (e.g. `property_manage`, `invoice_manage`, `report_view`, `settings_manage`) checked per role
- **View role permissions** — read-only permission summary for any role

#### System Settings
- Organisation name, address, contact email, phone
- **Logo upload** — upload and replace organisation logo (shown in header and on printed invoices)
- Currency and regional settings
- **Transaction number series** — configure prefix and starting number for invoice, receipt, and other document numbers

#### Lease Conditions
- Configurable standard terms and conditions text that auto-populates new lease agreements

#### Property Types
- Add, edit, delete property type labels (e.g. Residential, Commercial, Mixed-Use)

#### Unit Types
- Add, edit, delete unit type labels (e.g. Studio, 1 Bedroom, Penthouse)

#### Amenities
- Add, edit, delete amenity labels (e.g. Parking, Swimming Pool, Gym, WiFi)

#### Charge Types
- Add, edit, delete charge type labels used for "Other Charges" invoices (e.g. Maintenance Fee, Utilities, Penalty)

#### Organizations *(Super Admin only)*
- List and manage all organisations in the system
- Add / edit organisation records
- **Org switcher** in the top navigation bar lets super admins switch between organisations without logging out

---

## Technical Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP (custom MVC-style framework, no external PHP framework) |
| **Database** | MySQL / MariaDB |
| **Frontend framework** | Bootstrap 5 |
| **Icons** | Bootstrap Icons, Font Awesome |
| **JavaScript** | jQuery, modular JS files per feature |
| **Tables** | DataTables (server-side processing) |
| **Charts** | Chart.js |
| **Rich text** | TinyMCE |
| **Date picker** | Pikaday |
| **Select dropdowns** | Bootstrap-select (with live search) |
| **Alerts** | SweetAlert |
| **Notifications** | Custom toaster |
| **Font** | Manrope, Inter, Noto Sans (Google Fonts) |
| **URL routing** | Apache mod_rewrite (`.htaccess`) |

---

## Architecture

```
pms/
├── app/                        # Backend — controllers, auth, DB, helpers
│   ├── init.php                # Bootstrap: DB, auth, session, autoload
│   ├── autoload.php            # Menu config, routing, view resolution
│   ├── auth.php                # Login, session, RBAC helpers
│   ├── db.php                  # MySQLi connection
│   ├── Model.php               # Base data layer
│   ├── *_controller.php        # Feature controllers (one per module)
│   └── utilities.php           # Helper functions (baseUri, tenant_where_clause…)
│
├── views/                      # HTML/PHP view templates
│   ├── partials/               # Shared layout (header, sidebar, footer, topbar)
│   ├── dashboard/
│   ├── properties/             # Properties, units + modals
│   ├── tenants/                # Tenants, guarantors, leases + modals
│   ├── accounting/             # Invoices, payments, expenses + modals
│   ├── maintenance/            # Requests, vendors + modals
│   ├── reports/
│   └── settings/               # All settings pages + modals
│
├── public/
│   ├── css/                    # styles.css (custom overrides), themes, utilities
│   ├── js/
│   │   ├── modules/            # Feature JS (one file per module)
│   │   └── *.js                # Shared: main, script, utilities, toaster, dashboard
│   ├── images/                 # Logos and uploaded images
│   └── plugins/                # DataTables, Select2, Chart.js, TinyMCE, etc.
│
├── migrations/                 # SQL migration files
├── .htaccess                   # Clean URL rewrite rules
└── index.php                   # Single entry point
```

---

## Multi-Tenancy

The system uses a **shared database, scoped data** model. Every core table (`properties`, `units`, `tenants`, `leases`, `invoices`, `payments_received`, `expenses`, `maintenance_requests`, etc.) carries an `org_id` column. All queries are automatically scoped to the current organisation via the `tenant_where_clause()` helper, ensuring complete data isolation between organisations.

Super admins can view and manage all organisations from a single login and switch context using the org switcher in the navigation bar.

---

## Access Control

Permissions are defined as string slugs and assigned to roles. Roles are assigned to users per organisation. Menu items, actions, and data operations are all gated behind permission checks.

Example permission slugs:
- `property_manage` — add/edit/delete properties and units
- `tenant_manage` — manage tenants and guarantors
- `lease_manage` — create and edit leases
- `invoice_manage` — create invoices, record payments
- `expense_manage` — log and manage expenses
- `maintenance_manage` — create and assign maintenance requests
- `report_view` — access the reports module
- `settings_manage` — change system settings, manage users and roles
- `super_admin` — full access across all organisations

---

## Getting Started

### Requirements

- PHP 7.4+ (8.x recommended)
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- XAMPP, Laragon, or any standard LAMP stack

### Installation

1. Clone or copy the project into your web server's document root (e.g. `htdocs/pms`)
2. Import the database schema and run migration files from the `migrations/` folder in order
3. Configure database credentials in `app/db.php`
4. Set the correct base path in `app/config.php` if needed
5. Ensure `mod_rewrite` is enabled and `.htaccess` is allowed (`AllowOverride All`)
6. Open `http://localhost/pms` in your browser and log in

---

## License

Proprietary — All rights reserved. © Kaad PMS Property Management System
