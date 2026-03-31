# Phase 1 – Multitenancy Testing Checklist

## Step 1: Run the Database Migration

Open phpMyAdmin (or your MySQL client) and run:

```
migrations/20260326_phase1_multitenant.sql
```

This will:
- Create the `organizations` table and seed a "Default Company" with `id = 1`
- Add `org_id` and `is_super_admin` columns to `users` and all domain tables
- Backfill all existing rows to `org_id = 1`
- Add performance indexes

---

## Step 2: Create a Super Admin User

In your database, promote one existing user to super admin:

```sql
UPDATE users SET is_super_admin = 1 WHERE id = <your_user_id>;
```

Make sure their `org_id = 1` (or any valid org).

---

## Step 3: Create a Second Organization + Second User

```sql
INSERT INTO organizations (name, code, status) VALUES ('Test Company B', 'TESTB', 'active');
-- Get the new org's id (e.g. 2)

INSERT INTO users (org_id, name, username, email, password, status, is_super_admin)
VALUES (2, 'Company B Admin', 'companyb', 'companyb@test.com', '<hashed_password>', 'active', 0);
```

Assign a role to this new user via `user_roles` so they can log in.

---

## Step 4: Login Tests

### 4.1 – Regular User (org_id = 1)
1. Log in as a regular user belonging to Org 1
2. Go to Properties → should see ONLY Org 1 properties
3. Go to Tenants → should see ONLY Org 1 tenants
4. Go to Finance/Accounting → ONLY Org 1 invoices and payments
5. Go to Maintenance → ONLY Org 1 requests
6. Go to Settings → System Settings should save against `org_id = 1`

### 4.2 – Regular User (org_id = 2)
1. Log in as a user belonging to Org 2
2. Verify they see **zero records** (since none exist for Org 2 yet)
3. Add a property — confirm it saves with `org_id = 2`
4. Log back in as Org 1 user — confirm Org 2's property is NOT visible

### 4.3 – Super Admin (no active org context)
1. Log in as the super admin
2. Topbar should show a building icon with "All Orgs" label
3. Go to Properties → should see ALL properties from all orgs (no filter)
4. Dashboard stats should reflect totals across all orgs
5. Settings → Organizations → should see the Organizations management page

---

## Step 5: Org Context Switcher (Super Admin only)

1. As super admin, click the building icon (🏢) in the topbar
2. A dropdown appears with "All Organizations" and individual orgs
3. Click "Org 1 – Default Company"
4. Page reloads; topbar label now shows "Default Company"
5. Properties list now shows ONLY Org 1 records
6. Click switcher again → select "All Organizations"
7. Page reloads; all records visible again

---

## Step 6: Data Isolation Tests (CRUD)

For each module, logged in as Org 1 user:

| Action | Expected Result |
|--------|----------------|
| Add Property | Saved with `org_id = 1` in DB |
| Edit Property | Only works on own Org's property |
| Delete Property | Cannot delete another Org's property |
| Add Unit | Saved with `org_id = 1` |
| Add Tenant | Saved with `org_id = 1` |
| Add Lease | Saved with `org_id = 1` |
| Add Invoice | Saved with `org_id = 1` |
| Record Payment | Saved with `org_id = 1` |
| Add Expense | Saved with `org_id = 1` |
| Add Maintenance Request | Saved with `org_id = 1` |
| Add Vendor | Saved with `org_id = 1` |
| Add Guarantor | Saved with `org_id = 1` |

Verify each with: `SELECT org_id FROM <table> ORDER BY id DESC LIMIT 1;`

---

## Step 7: Settings Isolation

1. As Org 1 user, go to Settings → System Settings
2. Change company name or transaction prefix → save
3. Log in as Org 2 user → settings should be different (or empty defaults)
4. Verify DB: `SELECT org_id, setting_key, setting_value FROM system_settings;`

---

## Step 8: Organizations Page (Super Admin)

1. Log in as super admin
2. Go to Settings → Organizations
3. Add a new organization: "Test Org C"
4. Edit it: change name to "Test Org C Updated"
5. Try deleting an org that has users → should show error
6. Delete an org with no users → should succeed

---

## Step 9: Dropdowns Are Scoped

Check that all select dropdowns only show data from the current user's org:
- Add Invoice → Lease dropdown should only show current org's leases
- Add Unit → Property dropdown should only show current org's properties
- Add Lease → Tenant/Guarantor/Property/Unit dropdowns → only current org's records
- Maintenance Request → Property/Unit dropdown → only current org's

---

## Step 10: Reference Number Generation

1. As Org 1 user, create an invoice — note the reference number (e.g. INV-2026-001)
2. As Org 2 user (via super admin context switch), create an invoice
3. Both orgs should maintain their own independent numbering sequence

Verify: `SELECT org_id, setting_value FROM system_settings WHERE setting_key = 'transaction_series';`

---

## Step 11: Role Isolation Tests

Roles are now per-company. Each organization creates and manages its own roles with its own permission assignments.

### 11.1 – Role Creation per Org

1. Log in as Org 1 user with settings access
2. Go to Settings → User Management → Roles tab
3. Create a new role: "Property Manager" with permissions `property_manage`, `unit_manage`
4. Log in as Org 2 user (via super admin context switch)
5. Go to Roles tab → "Property Manager" should **NOT** appear (it belongs to Org 1)
6. Create a role also named "Property Manager" for Org 2 → should succeed (no conflict)
7. Verify in DB: `SELECT id, org_id, role_name FROM roles;` — two rows with same name but different `org_id`

### 11.2 – User Role Assignment is Org-Scoped

1. As Org 1 user, go to Settings → Add/Edit a user
2. The Role dropdown should only show Org 1's roles
3. As super admin switched to Org 2, edit a user → Role dropdown shows only Org 2's roles

### 11.3 – Permission Enforcement

1. Org 1 has a "Viewer" role with only `dashboard_view`
2. Assign this role to a test user in Org 1
3. Log in as that user → should only see Dashboard, no other menu items
4. Org 2 has a "Full Access" role with all permissions → that user sees everything

### 11.4 – Super Admin Permissions

1. Super admin always bypasses permission checks
2. Super admin can see all menu items regardless of roles/permissions
3. Super admin's permission session is loaded from the global `permissions` table (all permissions)

### 11.5 – Role Deletion Protection

1. Try deleting a role named "Admin" → should be blocked
2. Try deleting a role that has users assigned to it → should show how many users are assigned and block deletion
3. Try deleting a role from another org (via direct POST manipulation) → should return "access denied"

---

## Common Issues to Watch For

- **Blank page or 500 error after migration**: Check `php error_log` — likely a column doesn't exist yet (re-run migration)
- **Dropdowns showing all records**: A controller's dropdown query may have been missed — check that query includes `tenant_where_clause()`
- **Super admin sees filtered data**: Check that `is_super_admin` is `1` in the DB and session; `tenant_where_clause()` returns `1=1` when `active_org_id = 0`
- **Existing users can't log in**: Ensure `org_id` was backfilled to 1 (migration step) and is not null
