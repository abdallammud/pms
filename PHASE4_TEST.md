# Phase 4 Test Checklist — Units Enhancements and Settings Extensions

## Pre-Test Setup

1. Run the migration:
   ```
   migrations/20260326_phase4_units.sql
   ```
2. Verify schema:
   ```sql
   DESCRIBE units;
   -- Expect: unit_type_id, floor_number, room_count, is_listed columns present

   SHOW TABLES LIKE 'unit_types';
   SHOW TABLES LIKE 'amenities';
   SHOW TABLES LIKE 'unit_amenities';
   SHOW TABLES LIKE 'unit_images';
   -- All four tables must exist
   ```

---

## Step 1: Unit Types (Settings)

1. Navigate to **Settings → Unit Types** (or `/unit_types`).
2. **Expected:** DataTable page loads with no rows initially.
3. Click **Add Unit Type**.
   - Fill in "Type Name" (e.g., "Studio") and optional description.
   - Set status to Active.
   - Click **Save**.
   - **Expected:** Success toast, row appears in table.
4. Add 2–3 more types (e.g., "1-Bedroom", "2-Bedroom", "Penthouse").
5. Click **Edit** on any row.
   - Change the name and save.
   - **Expected:** Row updates correctly.
6. Attempt to add a duplicate name.
   - **Expected:** Error message "Unit type name already exists."
7. Click **Delete** on an unused type.
   - **Expected:** Confirmation dialog, then row removed.
8. Assign a unit type to a unit (see Step 3), then try to delete that type.
   - **Expected:** Error "Cannot delete. This unit type is in use."

---

## Step 2: Amenities (Settings)

1. Navigate to **Settings → Amenities** (or `/amenities`).
2. Click **Add Amenity**.
   - Enter name: "WiFi"
   - Enter icon: `bi-wifi` — confirm the preview icon updates in the input group.
   - Click **Save**.
   - **Expected:** Row appears in table with icon preview.
3. Add more amenities: "Parking" (`bi-car-front`), "Swimming Pool" (`bi-droplet`), "Generator" (`bi-lightning`).
4. Edit an amenity → change name or icon → save → table updates.
5. Delete an amenity → confirmation → row removed.
6. Duplicate name check: add another "WiFi" → error shown.

---

## Step 3: Add Unit (New Fields)

1. Navigate to **Properties → Units** (or `/units`).
2. Click **Add Unit**.
3. **Expected form fields:**
   - Property *(required, dropdown)*
   - Unit Number *(required)*
   - Unit Type *(required, now dynamic dropdown from Unit Types)*
   - Size (sq ft)
   - **Floor Number** *(new)*
   - **Number of Rooms** *(new)*
   - Rent Amount
   - Status
   - **List on website** toggle *(new)*
   - **Amenities checklist** *(new, loaded from Settings → Amenities)*
4. Select Unit Type from the dropdown (must show types created in Step 1).
5. Fill floor number = 3, rooms = 2.
6. Check 2 amenities from the checklist.
7. Set status to "Vacant" and check "List on website".
   - **Expected:** No warning shown.
8. Save the unit.
   - **Expected:** Success toast, unit appears in units table.

---

## Step 4: is_listed / Occupied Rule

1. Click **Add Unit** → set status to "Occupied" → check "List on website".
   - **Expected:** Warning banner appears: "An occupied unit cannot be listed on the website."
2. Try to click **Save Unit** with occupied + listed.
   - **Expected:** Error alert prevents saving.
3. Uncheck "List on website" → save.
   - **Expected:** Saves successfully.
4. Repeat the same test in the **Edit Unit** modal (Info Edit tab).

---

## Step 5: Edit Unit — Info Edit Tab

1. Click **Edit** on any unit in the units table.
2. **Expected:** New tabbed edit modal opens (not the old add modal).
3. All fields should be pre-filled including new fields (floor, rooms, is_listed).
4. Amenities that were selected should be pre-checked in the checklist.
5. Change the floor number and room count → click **Save Changes**.
   - **Expected:** Success toast, modal closes.
6. Re-open the same unit → verify the new values are shown.
7. Change amenities (add/remove checks) → save → re-open → confirm changes persisted.

---

## Step 6: Edit Unit — Images Upload Tab

1. Open the edit modal for any unit.
2. Click the **Images Upload** tab.
   - **Expected:** Upload zone and empty gallery appear.
3. Click the upload zone → select a JPG/PNG.
   - **Expected:** Upload progress bar shows, then image thumbnail appears in gallery.
4. Upload the first image.
   - **Expected:** It is automatically marked as the Cover (gold border + "Cover" badge).
5. Upload a second image.
   - **Expected:** Second image appears without cover badge.
6. Click the ⭐ star button on the second image.
   - **Expected:** Second image becomes cover; first image loses its badge.
7. Click the 🗑️ delete button on the cover image.
   - **Expected:** Image is removed; next image auto-promoted to cover.
8. Try uploading a file > 8MB.
   - **Expected:** Error "File too large (max 8 MB)."
9. Try uploading a non-image file (e.g., .pdf or .txt).
   - **Expected:** Error "Invalid file type."
10. Try drag-and-drop onto the upload zone.
    - **Expected:** Image uploads correctly.

---

## Step 7: Multitenancy Verification

1. Log in as **User A (Org 1)**.
   - Create unit types: "Studio", "Office".
   - Create amenities: "WiFi", "Parking".
   - Add a unit with those types and amenities.
2. Log in as **User B (Org 2)**.
   - Navigate to Settings → Unit Types.
   - **Expected:** User B sees no unit types (Org 1's types are NOT visible).
   - Navigate to Settings → Amenities.
   - **Expected:** Empty — Org 1's amenities not visible.
   - Navigate to Units → Add Unit.
   - **Expected:** Unit type dropdown is empty (no Org 1 types).
   - **Expected:** Amenities checklist shows "No amenities defined."
3. Log in as **super admin**.
   - Switch to Org 1 context.
   - **Expected:** Sees Org 1's unit types and amenities.

---

## Step 8: Regression Check

1. **Properties listing** (`/properties`) still shows card grid. ✓
2. **Add Property** modal still works (region, district present). ✓
3. **Edit Property** tabbed modal (Basic Info + Images) still works. ✓
4. **Property show page** (`/property/{id}`) loads correctly. ✓
5. **Leases, Invoices, Tenants** menus still accessible. ✓
6. **Settings → Property Types** still works. ✓
7. **Settings → Organizations** (super admin only) still works. ✓

---

## File Reference

| Path | Role |
|------|------|
| `migrations/20260326_phase4_units.sql` | DB schema changes |
| `app/unit_type_controller.php` | Unit Types CRUD API |
| `app/amenity_controller.php` | Amenities CRUD API |
| `app/property_controller.php` | Updated `save_unit`, `get_unit`, unit_images endpoints |
| `views/settings/unit_types.php` | Unit Types settings page |
| `views/settings/amenities.php` | Amenities settings page |
| `views/properties/modals/add_unit.php` | Redesigned add unit modal |
| `views/properties/modals/edit_unit.php` | New tabbed edit unit modal |
| `public/js/modules/unit_types.js` | Unit Types DataTable + CRUD JS |
| `public/js/modules/amenities.js` | Amenities DataTable + CRUD JS |
| `public/js/modules/properties.js` | Updated for units, images, amenities |
| `public/uploads/units/` | Unit image upload directory (must be writable) |
