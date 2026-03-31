-- Phase 6: Invoice Items and Payment Allocation Overhaul

-- -------------------------------------------------------
-- invoice_items: line items per invoice
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    org_id      INT NOT NULL,
    invoice_id  INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    qty         DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit_price  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_rate    DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    tax_amount  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    line_total  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    balance     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sort_order  INT           NOT NULL DEFAULT 0,
    INDEX idx_inv_items_invoice (invoice_id),
    INDEX idx_inv_items_org     (org_id),
    CONSTRAINT fk_inv_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- payment_allocations: FIFO distribution records
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_allocations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    org_id          INT NOT NULL,
    payment_id      INT NOT NULL,
    invoice_item_id INT NOT NULL,
    amount          DECIMAL(15,2) NOT NULL,
    allocated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pa_payment (payment_id),
    INDEX idx_pa_item    (invoice_item_id),
    INDEX idx_pa_org     (org_id),
    CONSTRAINT fk_pa_payment FOREIGN KEY (payment_id)      REFERENCES payments_received(id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_item    FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Backfill: create one line item per existing invoice
-- (only for invoices that don't already have items)
-- -------------------------------------------------------
INSERT INTO invoice_items
    (org_id, invoice_id, description, qty, unit_price, tax_rate, tax_amount, line_total, amount_paid, balance, sort_order)
SELECT
    inv.org_id,
    inv.id,
    CASE
        WHEN inv.invoice_type = 'rent' THEN 'Rent Charge'
        ELSE COALESCE(ct.name, 'Charge')
    END AS description,
    1               AS qty,
    inv.amount      AS unit_price,
    0               AS tax_rate,
    0               AS tax_amount,
    inv.amount      AS line_total,
    COALESCE(paid.total_paid, 0) AS amount_paid,
    GREATEST(0, inv.amount - COALESCE(paid.total_paid, 0)) AS balance,
    1               AS sort_order
FROM invoices inv
LEFT JOIN charge_types ct ON inv.charge_type_id = ct.id
LEFT JOIN (
    SELECT invoice_id, SUM(amount_paid) AS total_paid
    FROM payments_received
    GROUP BY invoice_id
) paid ON paid.invoice_id = inv.id
WHERE inv.id NOT IN (SELECT DISTINCT invoice_id FROM invoice_items);
