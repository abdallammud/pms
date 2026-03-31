-- Phase 4: Units Enhancements — Unit Types, Amenities, Unit Images

-- -------------------------------------------------------
-- unit_types: managed per organization like property_types
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS unit_types (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    org_id      INT NOT NULL,
    type_name   VARCHAR(100) NOT NULL,
    description TEXT NULL,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_unit_types_org (org_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- amenities: managed per organization
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS amenities (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    org_id      INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    icon        VARCHAR(50)  NULL COMMENT 'Bootstrap icon class e.g. bi-wifi',
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_amenities_org (org_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- unit_amenities: unit <-> amenity junction
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS unit_amenities (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    unit_id     INT NOT NULL,
    amenity_id  INT NOT NULL,
    UNIQUE KEY uq_unit_amenity (unit_id, amenity_id),
    INDEX idx_ua_unit (unit_id),
    INDEX idx_ua_amenity (amenity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- unit_images: multiple images per unit with one cover
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS unit_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    org_id      INT NOT NULL,
    unit_id     INT NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_cover    TINYINT(1)   NOT NULL DEFAULT 0,
    caption     VARCHAR(255) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_unit_images_unit  (unit_id),
    INDEX idx_unit_images_org   (org_id),
    INDEX idx_unit_images_cover (unit_id, is_cover),
    CONSTRAINT fk_unit_images_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- units: add new columns
-- -------------------------------------------------------
ALTER TABLE units ADD COLUMN IF NOT EXISTS unit_type_id  INT          NULL  AFTER unit_type;
ALTER TABLE units ADD COLUMN IF NOT EXISTS floor_number  SMALLINT     NULL  AFTER unit_type_id;
ALTER TABLE units ADD COLUMN IF NOT EXISTS room_count    SMALLINT     NULL  AFTER floor_number;
ALTER TABLE units ADD COLUMN IF NOT EXISTS is_listed     TINYINT(1)   NOT NULL DEFAULT 0 AFTER room_count;

CREATE INDEX IF NOT EXISTS idx_units_type_id ON units(unit_type_id);
CREATE INDEX IF NOT EXISTS idx_units_listed  ON units(is_listed);
