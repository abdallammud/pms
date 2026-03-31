# Phase 3 Test Checklist — Properties Module Redesign

## Pre-Test Setup

1. Run the migration:
   ```
   migrations/20260326_phase3_properties.sql
   ```
2. Verify columns added:
   ```sql
   DESCRIBE properties;
   -- Expect: region, district columns present
   SELECT * FROM property_images LIMIT 1;
   -- Expect: table exists
   ```

---

## Step 1: Properties Listing Page (Card View)

1. Navigate to `/properties`.
2. **Expected:** Card-based grid is shown (NOT a DataTable).
3. Each card must display:
   - Cover image (or placeholder building icon if no image)
   - Property name (bold)
   - Property type badge
   - City / District / Region
   - Total Units / Occupied / Vacant pills
   - Occupancy progress bar
   - Manager name (if set)
   - View, Edit, Delete action buttons
4. Confirm page loads with no JS errors in browser console.

---

## Step 2: Search and Filter Bar

1. Type a property name in the search box.
   - **Expected:** Cards filter live (debounced ~300ms) without page reload.
2. Select a type from the "All Types" dropdown.
   - **Expected:** Only cards matching that type are shown.
3. Select "Has Occupied" from the status filter.
   - **Expected:** Only properties with ≥1 occupied unit shown.
4. Select "Has Vacant".
   - **Expected:** Only properties with ≥1 vacant unit shown.
5. Clear all filters → all properties return.

---

## Step 3: Add Property Modal

1. Click **Add Property** button.
2. **Expected:** Modal opens with these fields:
   - Property Name *(required)*
   - Property Type (dropdown)
   - Street Address
   - City *(required)*
   - **Region** *(new)*
   - **District** *(new)*
   - Owner Name
   - Manager (dropdown)
   - Description
   - **No "Property Logo" field** *(removed)*
3. Fill all required fields and submit.
   - **Expected:** Success toast, modal closes, new card appears in grid.
4. Submit with empty Name or City → validation error shown.

---

## Step 4: Edit Property — Basic Info Tab

1. Click **Edit** on any property card.
2. **Expected:** Tabbed edit modal opens on "Basic Info" tab.
3. All fields should be pre-filled including Region and District.
4. Change the property name and click **Save Changes**.
   - **Expected:** Success toast, modal closes, card updates.

---

## Step 5: Edit Property — Images Tab

1. Open the edit modal for any property.
2. Click the **Images** tab.
   - **Expected:** Gallery area and upload zone appear.
3. Click the upload zone (or drag a JPG/PNG onto it).
   - **Expected:** Upload progress bar appears, then image thumbnail shows in gallery.
4. Upload multiple images at once.
   - **Expected:** All images appear in gallery.
5. First image uploaded should be automatically marked as **Cover** (gold border + "Cover" badge).
6. Click the ⭐ (star) button on a non-cover image.
   - **Expected:** That image becomes cover, previous cover loses badge.
7. Click the 🗑️ (trash) button on an image.
   - **Expected:** Image is removed from gallery and deleted from server.
8. Delete the cover image.
   - **Expected:** The next image in the gallery is automatically promoted to cover.
9. After setting a cover image, close modal and reload `/properties`.
   - **Expected:** The cover image appears as the card's hero photo.

---

## Step 6: Property Show Page

1. Click **View** on any property card (or navigate to `/property/{id}`).
2. **Expected:** Full property detail page loads with:
   - Hero image (cover image, or gradient placeholder)
   - Property name, type badge, full location string
   - Manager and Owner name in top-right
   - Description (if set)
   - Four stat cards: Total Units, Occupied, Vacant, Active Leases
   - Gallery section with all uploaded images
   - Units table (unit number, type, tenant, status)
   - "View All Units" link
   - Back to Properties, Edit, Delete buttons
3. Click a gallery image → lightbox opens with full-size preview.
4. Click outside the lightbox → it closes.
5. Click **Edit** button on show page → edit modal opens correctly.
6. Click **Delete** button → confirmation dialog, then redirects to `/properties`.

---

## Step 7: Delete Property (from card)

1. Click **Delete** on a property card.
2. **Expected:** SweetAlert confirmation appears.
3. Confirm deletion.
   - **Expected:** Property card disappears from grid.
4. Navigate to `/property/{deleted_id}` directly.
   - **Expected:** "Property not found" error message shown.

---

## Step 8: Multitenancy Verification

1. Log in as **User A** (Org 1).
   - Add a property with Region = "North".
2. Log in as **User B** (Org 2).
   - Navigate to `/properties`.
   - **Expected:** User B cannot see Org 1's property.
3. Log in as **super admin**.
   - Switch org context to Org 1 → sees Org 1's property.
   - Switch to "All Orgs" (org_id = 0) → sees all properties.

---

## Step 9: Regression Check

1. Navigate to `/units` → units DataTable still loads correctly.
2. Add a unit (no changes to unit form yet) → still works.
3. Edit a unit → still populates and saves correctly.
4. Leases, Invoices, Tenants menus still accessible and functional.
5. Settings → Property Types still works (unaffected).

---

## Known Constraints / Notes

- `properties.logo` column remains in DB for backward compatibility but is no longer used in the UI.
- Images are stored in `public/uploads/properties/`. Ensure this directory is writable (755).
- Max image upload size is 8 MB per file. Larger files will be rejected with an error message.
- The card grid fetches up to 500 properties in one request. For very large datasets, pagination may be added in Phase 9.
