# Phase 1 Test Plan — Quick Fixes & Branding

## 1. Unique Email & Username Validation
**Goal**: Ensure no two users can have the same username OR email within the same organization.

### Steps:
1. Go to **Settings > Users**.
2. Click **Add User**.
3. Create a user with a unique username and unique email. Confirm it works.
4. Try creating another user with the **same username** but different email. It should fail with "Username or Email already exists."
5. Try creating another user with a different username but the **same email**. It should fail with "Username or Email already exists."
6. Try **editing** an existing user and changing their email to one that already belongs to another user. It should fail.

---

## 2. Rebranding (Aayatiin → Kaad PMS)
**Goal**: Verify all visual references to the old name are gone.

### Steps:
1. Check the **Browser Tab Title**: It should now say "Kaad PMS - Dashboard".
2. Check the **Sidebar**: The brand name at the bottom/top should be "KAAD PMS".
3. Go to **Accounting > Expenses**.
4. Click **Add Expense**.
5. Check the "Property" or "Entity" dropdown/list: "Kaad PMS / Property Manager" should appear instead of the old name.
6. Generate a **Lease Word document**: The organization name in the header should be "Kaad PMS".
