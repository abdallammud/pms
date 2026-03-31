-- Phase 3: Properties Module - region, district, property_images

ALTER TABLE properties ADD COLUMN IF NOT EXISTS region   VARCHAR(100) NULL AFTER city;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS district VARCHAR(100) NULL AFTER region;

CREATE TABLE IF NOT EXISTS property_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    org_id      INT NOT NULL,
    property_id INT NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_cover    TINYINT(1) NOT NULL DEFAULT 0,
    caption     VARCHAR(255) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_prop_images_prop  (property_id),
    INDEX idx_prop_images_org   (org_id),
    INDEX idx_prop_images_cover (property_id, is_cover),
    CONSTRAINT fk_prop_images_prop FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
