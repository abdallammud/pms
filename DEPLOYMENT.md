# Deployment Guide & Migration Strategy

---

## Pre-Deployment Checklist

- [ ] Full database backup taken
- [ ] Application files backed up / tagged in git
- [ ] PHP 7.4+ and MySQLi extension confirmed on server
- [ ] Write permissions on `public/images/` folder
- [ ] `.htaccess` mod_rewrite enabled on server

---

## Migration Run Order

Run migrations in this exact order. Each is idempotent (`IF NOT EXISTS` / `IF NOT EXISTS` guards).

| Order | File | Description |
|-------|------|-------------|
| 1 | `migrations/20260326_phase1_multitenant.sql` | Adds `organizations`, `org_id` columns, backfills to Default Org |
| 2 | `migrations/20260326_phase3_properties.sql` | Adds `region`, `district` to properties; creates `property_images` |
| 3 | `migrations/20260326_phase4_units.sql` | Creates `unit_types`, `amenities`, `unit_amenities`, `unit_images`; adds unit columns |
| 4 | `migrations/20260326_phase6_invoice_items.sql` | Creates `invoice_items`, `payment_allocations`; backfills one item per invoice |

---

## Execution Commands

```bash
mysql -u <user> -p <database> < migrations/20260326_phase1_multitenant.sql
mysql -u <user> -p <database> < migrations/20260326_phase3_properties.sql
mysql -u <user> -p <database> < migrations/20260326_phase4_units.sql
mysql -u <user> -p <database> < migrations/20260326_phase6_invoice_items.sql
```

---

## Post-Migration Validation

```sql
-- 1. Default organization exists
SELECT * FROM organizations;

-- 2. All users have org_id
SELECT COUNT(*) FROM users WHERE org_id IS NULL;  -- should be 0

-- 3. All invoices have at least one item
SELECT i.id FROM invoices i
LEFT JOIN invoice_items ii ON ii.invoice_id = i.id
WHERE ii.id IS NULL;  -- should return 0 rows

-- 4. Invoice item balances are correct
SELECT ii.invoice_id, SUM(ii.line_total) AS items_total, i.amount AS header_total
FROM invoice_items ii
JOIN invoices i ON i.id = ii.invoice_id
GROUP BY ii.invoice_id
HAVING ABS(items_total - header_total) > 0.01;  -- should be empty

-- 5. unit_types and amenities tables exist
SHOW TABLES LIKE 'unit_types';
SHOW TABLES LIKE 'amenities';
SHOW TABLES LIKE 'invoice_items';
SHOW TABLES LIKE 'payment_allocations';
```

---

## Rollback Strategy

If issues arise, execute the following to restore the previous state:

```sql
-- Phase 6 rollback
DROP TABLE IF EXISTS payment_allocations;
DROP TABLE IF EXISTS invoice_items;

-- Phase 4 rollback
DROP TABLE IF EXISTS unit_images;
DROP TABLE IF EXISTS unit_amenities;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS unit_types;
ALTER TABLE units
  DROP COLUMN IF EXISTS unit_type_id,
  DROP COLUMN IF EXISTS floor_number,
  DROP COLUMN IF EXISTS room_count,
  DROP COLUMN IF EXISTS is_listed;

-- Phase 3 rollback
DROP TABLE IF EXISTS property_images;
ALTER TABLE properties
  DROP COLUMN IF EXISTS region,
  DROP COLUMN IF EXISTS district;

-- Phase 1 rollback (WARNING: destructive – data loss for org_id columns)
-- Only do this if no production data has been created under new structure
ALTER TABLE users DROP COLUMN IF EXISTS org_id, DROP COLUMN IF EXISTS is_super_admin;
DROP TABLE IF EXISTS organizations;
```

> **IMPORTANT**: The Phase 1 rollback removes org_id from all domain tables.
> Never run this on a production system that has been in use.

---

## Staged Deployment Steps

1. **Maintenance Mode** – Put the application in maintenance mode (show static page)
2. **Backup** – `mysqldump database > backup_YYYYMMDD.sql`
3. **Deploy Code** – Upload new application files
4. **Run Migrations** – Execute in order above
5. **Validate Migrations** – Run post-migration SQL checks
6. **Smoke Test** – Log in, check dashboard, create one property, one invoice, one payment
7. **Set Super Admin** – `UPDATE users SET is_super_admin=1 WHERE id=<your_id>;`
8. **Remove Maintenance Mode** – Go live

---

## Environment Configuration

After deployment, set these in `app/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_database_name');
define('BASE_URL', 'https://your-domain.com');
```

---

## File Upload Configuration

Ensure the following directories exist and are writable:

```
public/images/         – property images, unit images, tenant ID photos
```

Set permissions:
```bash
chmod -R 755 public/images/
chown -R www-data:www-data public/images/
```

---

## PHP Configuration Recommendations

```ini
upload_max_filesize = 10M
post_max_size = 20M
max_execution_time = 60
memory_limit = 256M
```

---

## Cron Jobs (Optional)

For auto-invoice generation, add a cron if the feature is enabled:

```bash
# Run daily at 6 AM
0 6 * * * php /var/www/pms/app/auto_invoice_cron.php
```
