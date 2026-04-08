# Kaad PMS — Product Description & Features Guide

---

## 1. Product Overview

**Product Name:** Kaad PMS  
**Tagline:** *Take Control of Your Property Management*  
**Category:** SaaS Property Management System  
**Target Market:** Property owners, property managers, real estate companies, and landlord associations in emerging and established markets.

### What Is Kaad PMS?

Kaad PMS is a comprehensive, cloud-ready property management platform that centralises every aspect of property operations — from listing properties and managing tenants, to automating billing, tracking maintenance, and generating financial reports — all in a single, elegant web interface.

Built for scale, it supports **multiple organisations** under one installation (multi-tenant SaaS), strict data isolation, and fine-grained role-based access control, making it ideal for property management companies overseeing diverse portfolios as well as individual landlords managing a handful of units.

### One-Liner Description (for listings / ads)

> **Kaad PMS** is a modern, all-in-one property management system that helps landlords, property managers, and real estate companies manage properties, tenants, leases, invoicing, payments, maintenance, and reporting — from a single dashboard.

---

## 2. Key Selling Points

| # | Selling Point | Why It Matters |
|---|---------------|----------------|
| 1 | **All-in-One Platform** | No need for separate tools — properties, tenants, leases, accounting, maintenance, and reports live under one roof. |
| 2 | **Multi-Organisation (SaaS-Ready)** | Manage unlimited companies/organisations from a single installation with complete data isolation. Perfect for franchise models or property management firms with multiple brands. |
| 3 | **Smart Invoicing & FIFO Payments** | Bulk-generate rent invoices for all active leases in one click. Payments are automatically allocated to the oldest invoice line items first (FIFO), keeping books perfectly balanced. |
| 4 | **Real-Time Dashboard** | KPI cards, income vs. expense charts, occupancy rates, lease expiration alerts, and maintenance queues update in real time with no page reloads. |
| 5 | **Role-Based Access Control** | Create custom roles with granular permissions (e.g., "Accountant" can manage invoices but not properties). Ensure every team member sees only what they need. |
| 6 | **Print-Ready Financial Documents** | Generate branded invoices and receipts with your company logo, contact info, and line-item details — print or save as PDF directly from the browser. |
| 7 | **Modern, Responsive UI** | Premium glassmorphism design with dark/light themes, smooth animations, and AJAX-first interactions. Works beautifully on desktop, tablet, and mobile. |
| 8 | **Maintenance Tracking** | Log, assign, and track maintenance requests from submission to resolution with priority levels, vendor assignment, and status workflows. |
| 9 | **Comprehensive Reporting** | Six built-in reports — Rent Collection, Occupancy, Tenant, Outstanding Balances, Income & Expense, and Maintenance Cost — with date-range and property filters. |
| 10 | **Zero Vendor Lock-In** | Self-hosted on your own server. You own your data, your infrastructure, and your customisations. No recurring per-unit SaaS fees. |

---

## 3. Detailed Feature Breakdown

### 3.1 Dashboard

The command-centre that loads immediately after login. Provides a bird's-eye view of your entire portfolio.

| Widget | Description |
|--------|-------------|
| **KPI Cards** | Total Properties, Total Units, Occupied Units, Vacant Units, Active Tenants, Total Rent Collected, This Month's Income, Outstanding Balance, Occupancy Percentage, New Maintenance Requests, In-Progress Maintenance |
| **Income vs. Expense Chart** | Chart.js bar/line chart comparing monthly income against expenses for the last 6 months |
| **Occupancy Doughnut** | Visual breakdown of occupied vs. vacant units across your portfolio |
| **Lease Summary** | Counts of active, expiring (next 30 days), expired, and terminated leases |
| **Receivables Table** | Top 10 unpaid or partially paid invoices ranked by due date |
| **Maintenance Queue** | Latest open maintenance requests with priority badges and assigned vendor |
| **Recent Payments** | Last 5 payments received with tenant name, property, amount, date, and method |
| **Upcoming Lease Expirations** | Leases expiring within 30 days with tenant, property, and unit details |

**Key Detail:** All data loads via AJAX — no full page reloads. The dashboard is always up-to-date.

---

### 3.2 Property Management

Manage your entire property portfolio with rich detail at every level.

#### Properties List
- **Card grid layout** with cover images, location, unit count, and owner/manager tags
- **Live search** by property name, city, or owner
- **Filters** by property type and occupancy status
- **Dynamic count badge** that updates as filters are applied

#### Property Detail Page
- **Hero cover image** with a full image gallery
- **Details panel:** property type, full address (street, city, district, region), owner, manager, phone, date added
- **Description callout** box for property notes
- **KPI cards:** total units, occupied, vacant, occupancy rate
- **Units table** with status indicators and linked lease info

#### Property Management Actions
- **Add / Edit property** — name, type, address, city, district, region, description, owner, manager assignment
- **Multiple property images** — upload, reorder, set cover image, delete individual photos
- **Custom property types** — Residential, Commercial, Mixed-Use, or any custom type you define

---

### 3.3 Units

Each property contains units, each with its own details, images, and amenities.

- **Full units list** with property and status filters
- **Add / Edit unit** — unit number, type (studio, 1BR, 2BR, 3BR, penthouse, etc.), floor, area (sqm), monthly rent amount, amenities
- **Unit images** — upload gallery per unit with cover image selection
- **Amenities tagging** — tag units with configurable labels (Parking, WiFi, Gym, Swimming Pool, Security, etc.)
- **Automatic occupancy** — unit status automatically reflects active lease state
- **Bulk actions** — activate, deactivate, or delete multiple units at once

---

### 3.4 Tenant Management

A complete tenant directory with personal, identity, and employment records.

- **Searchable DataTable** with quick-action buttons
- **Add / Edit tenant** — full personal info (name, DOB, gender, nationality), contact details, ID documents (type, number, expiry), employment/work information, status management
- **ID photo upload** — drag-and-drop photo zone with instant preview
- **Bulk actions** — delete, change status for multiple tenants

---

### 3.5 Guarantors

Manage co-signers linked to tenant records.

- **Searchable DataTable** listing all guarantors
- **Add / Edit guarantor** — personal info, identity documents (type, number, expiry), employment info, status
- **ID photo upload** — same modern drag-and-drop photo zone as tenants
- **Organized sections:** Personal Information, Identity Documents, Employment / Work Information

---

### 3.6 Lease Management

Full lease lifecycle from signing to expiry.

- **Leases DataTable** with colour-coded status badges (Active, Expired, Terminated)
- **Add lease** — full-page form: select property → unit → tenant, start/end dates, monthly rent, deposit amount, payment day, terms and conditions
- **Edit lease** — all fields editable with cascading property → unit → tenant dropdowns
- **View lease** — full-page detail: lease summary, linked tenant info, unit/property details, complete payment history
- **Lease conditions** — configurable standard clauses that auto-populate into new lease forms
- **Bulk actions** — terminate or delete multiple leases

---

### 3.7 Accounting — Invoices

The financial engine of the system.

- **Invoice DataTable** — invoice number, tenant, total amount, status (Unpaid / Partial / Paid), due date
- **Add invoice** — select lease, choose type (Rent or Other Charges), add multiple line items with description, quantity, unit price, tax percentage; line totals calculate live
- **Bulk rent generation** — generate rent invoices for ALL active leases in one click for a selected billing period
- **Duplicate prevention** — warns if a rent invoice already exists for the same lease and month
- **Invoice Detail Page:**
  - Full invoice card with tenant, unit, property, charge type, billing period, status badge
  - Line items table with subtotal, tax, and grand total
  - Payments received section showing each payment applied
  - Balance due banner
  - **Print / PDF** — opens a fully formatted print window with company logo, contact info, billed-to section, line items, payment summary, and footer
  - **Record Payment** button that opens a pre-loaded modal

---

### 3.8 Accounting — Payments Received

- **Payments DataTable** — receipt number, invoice reference, tenant, amount, payment method, date
- **Record payment modal** (two-panel layout):
  - **Left panel:** select invoice, received date, amount, payment method (Cash / Mobile Money / Bank Transfer), notes
  - **Right panel:** automatically fetches and displays all invoice line items with their remaining balances the moment an invoice is selected
  - **FIFO allocation preview** — as you type the payment amount, the preview shows exactly how much will be applied to each line item in FIFO order
  - **Over-payment warning** if amount exceeds invoice balance
- **Payment Detail Page** — receipt info card, invoice reference link, full allocation breakdown table
- **Edit and delete** payments (invoice status recalculates automatically)

---

### 3.9 Accounting — Expenses

- **Expenses DataTable** — category, property, amount, date
- **Add / Edit expense** — description, category, property (optional), amount, date, notes
- **Bulk delete** for multiple expense entries

---

### 3.10 Maintenance

Track and manage property maintenance from submission to resolution.

#### Requests
- **Requests DataTable** — title, property, unit, priority (Low / Medium / High / Urgent), status (Open / In Progress / Resolved / Closed), requester
- **Create request** — title, description, property, unit (optional), priority, status; requester auto-filled from logged-in user
- **Assign request** — assign to a vendor or staff member with notes and due date
- **Colour-coded badges** for priority and status
- **Bulk actions** — close or delete multiple requests

#### Vendors
- **Vendors DataTable** — company name, contact, specialty, status
- **Add / Edit vendor** — company name, contact person, phone, email, specialty, notes, status
- **Vendor directory** used when assigning maintenance requests

---

### 3.11 Reports Hub

Six powerful reports, all with date-range and property filters, rendered in a dedicated full-page view.

| Report | What It Shows |
|--------|---------------|
| **Rent Collection** | Invoices raised vs. collected per period, overall collection rate |
| **Occupancy Report** | Unit occupancy rates by property and date range |
| **Tenant Report** | Tenant directory with associated lease and payment status |
| **Outstanding Balances** | All unpaid and partially paid invoices with aging analysis |
| **Income & Expense** | Period comparison of all income (payments) vs. all expenses |
| **Maintenance Cost** | Maintenance request counts and associated costs per property |

---

### 3.12 Settings & Administration

Complete system configuration accessible by administrators.

#### Users & Roles
- **Users list** — name, email, role, status
- **Add / Edit user** — assign role, set password, activate/deactivate
- **Custom roles** with configurable permission sets
- **Granular permissions** — `property_manage`, `tenant_manage`, `lease_manage`, `invoice_manage`, `expense_manage`, `maintenance_manage`, `report_view`, `settings_manage`, `super_admin`
- **View role permissions** — read-only permission summary per role

#### System Settings
- Organisation name, address, contact email, phone
- **Logo upload** — upload and replace org logo (shown in header and printed documents)
- Currency and regional settings
- **Transaction number series** — configure prefix and starting number for invoices, receipts, and other documents

#### Configurable Lookups
- **Lease Conditions** — standard terms & conditions for auto-population in new leases
- **Property Types** — Residential, Commercial, Mixed-Use, etc.
- **Unit Types** — Studio, 1 Bedroom, 2 Bedroom, Penthouse, etc.
- **Amenities** — Parking, Swimming Pool, Gym, WiFi, Security, etc.
- **Charge Types** — Maintenance Fee, Utilities, Penalty, etc.

#### Multi-Organisation Management *(Super Admin only)*
- List and manage all organisations in the system
- Add / edit organisation records
- **Org switcher** in the top navigation bar — switch between organisations without logging out

---

## 4. Technical Specifications

| Layer | Technology |
|-------|------------|
| **Backend** | PHP (custom MVC framework — no external PHP framework dependency) |
| **Database** | MySQL / MariaDB |
| **Frontend** | Bootstrap 5, jQuery, modular JavaScript |
| **Charts** | Chart.js |
| **Data Tables** | DataTables with server-side processing |
| **Rich Text** | TinyMCE |
| **Fonts** | Manrope, Inter, Noto Sans (Google Fonts) |
| **Icons** | Bootstrap Icons, Font Awesome |
| **Alerts** | SweetAlert |
| **Deployment** | Apache with mod_rewrite, PHP 7.4+ (8.x recommended) |

---

## 5. Branding & Visual Identity

### Logo

The Kaad PMS logo features a stylised house outline with the letter "K" integrated into the design and a small dot accent. Below, the text reads **"Kaad PMS"** in bold, with **"Property Management System"** as a subtitle.

**Logo Files Included:**
- `kaad_pms_logo.png` — Standard dark logo (for light backgrounds)
- `kaad_pms_logo_white.png` — White/inverted logo (for dark backgrounds)

---

### Color Palette

The brand uses a premium Navy Blue palette with carefully calibrated accent colours:

#### Primary Colors

| Swatch | Name | HEX | RGB | Usage |
|--------|------|-----|-----|-------|
| 🟦 | **Primary Navy** | `#1d3354` | `29, 51, 84` | Sidebar, headers, main brand colour |
| 🟦 | **Primary Dark** | `#162844` | `22, 40, 68` | Hover states, dark accents, submenu backgrounds |
| 🟦 | **Primary Mid** | `#243f68` | `36, 63, 104` | Submenu hover, interactive states |
| 🔵 | **Accent Blue** | `#2e62a8` | `46, 98, 168` | Active items, links, badges, call-to-action buttons |

#### Extended Blue Palette (Light Shades)

| Swatch | Name | HEX | Usage |
|--------|------|-----|-------|
| 🔵 | Light 1 | `#1e3a5f` | Subtle dark variations |
| 🔵 | Light 2 | `#224575` | Secondary interactive states |
| 🔵 | Light 3 | `#27528c` | Chart accent |
| 🔵 | Light 4 | `#2e62a8` | Primary accent (same as Accent Blue) |
| 🔷 | Light 5 | `#3a74c4` | Button hover states |
| 🔷 | Light 6 | `#5089d4` | Highlighted elements |
| 🔷 | Light 7 | `#7aabdf` | Soft accent backgrounds |
| 🔷 | Light 8 | `#a9c9ec` | KPI card accent backgrounds |
| 🔷 | Light 9 | `#d0e3f5` | Panel backgrounds |
| ⬜ | Light 10 | `#e3eff9` | Subtle card backgrounds |
| ⬜ | Light 11 | `#f0f6fc` | Page background tint |
| ⬜ | Light 12 | `#f7fafd` | Lightest background |

#### UI Status Colors

| Swatch | Name | HEX | Usage |
|--------|------|-----|-------|
| 🟢 | Success Green | `#20c997` | Paid status, occupied badges, positive indicators |
| 🟡 | Warning Amber | `#ffc107` | In-progress status, partial payment, caution alerts |
| 🔴 | Danger Red | `#ff4f4f` | Delete actions, urgent priority, error states |
| 🟣 | Stat Purple | `#6f42c1` | KPI stat icons, chart accents |
| ⬜ | Page Background | `#ffffff` | Light mode page background |
| ⬛ | Dark Background | `#0a0a0a` | Dark mode / login page base |

#### Login Page Specific

| Swatch | Name | HEX | Notes |
|--------|------|-----|-------|
| ⬛ | Background | `#0a0a0a` | Base body background |
| 🟦 | Card Blue | `rgba(10,40,60,0.85)` | Glassmorphism card overlay |
| ⬜ | Button White | `#ffffff` | Login button in white (inverted) |
| 🟦 | Button Text | `#0a1e32` | Dark navy text on white button |

---

### Typography

| Font | Weight | Usage |
|------|--------|-------|
| **Manrope** | 400–700 | Primary body font, headings |
| **Inter** | 300–800 | Login page, secondary text, UI labels |
| **Noto Sans** | 400–600 | Fallback, multilingual support |
| **Open Sans** | 300–700 | PDF documents, print layouts |

---

### Design Characteristics

- **Glassmorphism** login card with backdrop blur and shimmer border animation
- **Rounded corners** (`12px` border radius standard, `20px` for cards)
- **Subtle shadows** (`box-shadow: 0 4px 6px -1px rgba(0,0,0,.1)`)
- **Clean white topbar** with navy sidebar
- **Smooth transitions** (0.3s ease for all hover/state changes)
- **Badge system** with colour-coded status indicators
- **AJAX-first** interactions — no full page reloads
- **Responsive** at all breakpoints (desktop, tablet, mobile)

---

## 6. Competitive Advantages

| vs. Competitors | Kaad PMS Advantage |
|-----------------|-------------------|
| **vs. Spreadsheets** | Automated invoicing, FIFO payment allocation, real-time dashboards — no manual formulas, no human error |
| **vs. Buildium / AppFolio** | Self-hosted = no per-unit monthly fees; you own your data and infrastructure |
| **vs. Custom Development** | Ready to deploy today; proven architecture with 9 integrated modules — months of development time saved |
| **vs. Generic ERPs** | Purpose-built for property management; the UI, workflows, and terminology all speak the language of real estate |

---

## 7. Ideal Customer Profile

1. **Property Management Companies** managing 50–5,000+ units across multiple properties
2. **Real Estate Investors** with a growing portfolio who have outgrown spreadsheets
3. **Landlord Associations** needing a shared platform for multiple member organisations
4. **Housing Cooperatives** requiring transparent tenant and financial management
5. **Commercial Property Owners** managing office spaces, retail units, or mixed-use developments

---

## 8. Deployment Options

| Option | Details |
|--------|---------|
| **Self-Hosted** | XAMPP, LAMP, cPanel — any PHP 7.4+ server with MySQL |
| **Cloud VPS** | Deploy on DigitalOcean, AWS, Linode, or Hetzner |
| **Shared Hosting** | Works on standard cPanel shared hosting |
| **Demo Access** | Can be set up on a staging URL for client walkthroughs |

---

*© Kaad PMS — All Rights Reserved. Proprietary Software.*
