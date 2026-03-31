-- =============================================================
-- A3: Communication Module
-- Creates sms_log table and adds communication_manage permission.
-- Run once per environment.
-- =============================================================

CREATE TABLE IF NOT EXISTS `sms_log` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `org_id`            INT UNSIGNED     NOT NULL DEFAULT 0,
    `tenant_id`         INT UNSIGNED     NULL,
    `recipient_phone`   VARCHAR(30)      NOT NULL,
    `message`           TEXT             NOT NULL,
    `status`            ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
    `provider_response` TEXT             NULL,
    `sent_by_user_id`   INT UNSIGNED     NULL,
    `created_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sms_org`    (`org_id`),
    KEY `idx_sms_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add communication_manage permission (safe to run multiple times)
INSERT IGNORE INTO `permissions` (`permission_name`, `description`)
VALUES ('communication_manage', 'Access and send messages via the Communication module');

-- Assign communication_manage to all existing admin roles
-- (roles whose name contains 'admin', case-insensitive)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE LOWER(r.role_name) LIKE '%admin%'
  AND p.permission_name = 'communication_manage';
