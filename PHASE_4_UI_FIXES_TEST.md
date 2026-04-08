# Phase 4 Test Plan — UI & Document Styles

**Goal**: Verify the visual improvements on the Reports page and the branding consistency in Word documents.

## 1. Reports Page Filter Section
- **Navigation**: Reports > Any report (e.g., Rent Collection).
- **Previous Look**: Generic grey form fields.
- **New Look**: 
  - Modern card container with a left-accent border (System Primary color).
  - Subtle background tint.
  - Better spacing and typography for labels.
  - "Run Report" button styled with the system brand color.
- **Manual Verification**: Confirm the design feels premium and matches the rebranded aesthetic.

## 2. Lease Word Document Branding
- **Navigation**: Tenants > Leases List.
- **Action**: Click the **Download Word** icon (blue Word icon) for any lease.
- **Verification**: 
  1. Open the downloaded `.docx` file.
  2. Check the header/title colors.
  3. **Expected**: The blueish default colors are replaced by the Kaad PMS primary brand color (`#2E62A8`).
  4. Ensure all system references in the document use "Kaad PMS" instead of "Aayatiin".

---

### Troubleshooting:
- If Word document colors are unchanged, clear your browser cache and try downloading again.
- If the Reports page looks the same, ensure `views/reports/reports_page.php` has been updated with the latest styles.
