# Phase 3 Test Plan — Summary Cards on DataTable Pages

**Goal**: Verify that all 7 key DataTable pages now feature functional, real-time summary cards at the top.

## 1. Units Page (`/units`)
- **Navigation**: Properties > Units List.
- **Expected Cards**:
  1. **Total Units**: Total count of units in the organization.
  2. **Occupied**: Count of units with status 'occupied'.
  3. **Vacant**: Count of units with status 'vacant'.
  4. **Maintenance**: Count of units with status 'maintenance'.
- **Verification**: Confirm "..." changes to actual numbers after ~200ms.

## 2. Tenants Page (`/tenants`)
- **Navigation**: Tenants > Tenants List.
- **Expected Cards**:
  1. **Total Tenants**: Total count of tenants.
  2. **Active**: Count of tenants with status 'active'.
  3. **Inactive**: Count of tenants with status 'inactive'.
- **Verification**: Confirm numbers match your tenant list.

## 3. Leases Page (`/leases`)
- **Navigation**: Tenants > Leases List.
- **Expected Cards**:
  1. **Total Leases**: Total count of leases.
  2. **Active Leases**: Count of leases with status 'active'.
  3. **Expiring Soon**: Count of leases expiring within the next 30 days.
- **Verification**: Check if the "Active" count matches the "Active" badge in the table.

## 4. Invoices List (`/invoices`)
- **Navigation**: Accounting > Invoices List.
- **Expected Cards**:
  1. **Total Invoices**: Total count of invoices.
  2. **Unpaid**: Count of invoices with status 'unpaid' or 'partially_paid'.
  3. **Overdue**: Count of invoices where due_date < today and status is not 'paid'.
- **Verification**: Data should reflect the invoice statuses accurately.

## 5. Payments Received (`/payments_received`)
- **Navigation**: Accounting > Payments Received.
- **Expected Cards**:
  1. **Total Received**: Total sum of all payments (formatted as currency).
  2. **Receipt Count**: Total count of receipt records.
  3. **Received Today**: Sum of payments received on the current date.
- **Verification**: Verify "Total Received" matches the logical sum of your receipts.

## 6. Expenses Page (`/expenses`)
- **Navigation**: Accounting > Expenses.
- **Expected Cards**:
  1. **Total Expenses**: Total sum of all expense records.
  2. **This Month**: Sum of expenses recorded in the current month.
  3. **Expense Count**: Total count of expense records.
- **Verification**: Check if "This Month" count captures recent entries.

## 7. Maintenance Requests (`/maintenance_requests`)
- **Navigation**: Maintenance > Requests.
- **Expected Cards**:
  1. **Total Requests**: Total count of requests.
  2. **Pending/Work**: Count of requests with status 'pending' or 'in_progress'.
  3. **High Priority**: Count of requests with priority 'high' or 'urgent'.
- **Verification**: Ensure priority counts match the table badges.

---

### Technical Note for Developer:
- All cards use the new `public/js/summary_cards.js` helper.
- Ensure the browser console has no "Stats Error" or "404" logs.
- If stats are still "...", check if the `base_url` is correctly initialized in `views/partials/app_footer.php`.
