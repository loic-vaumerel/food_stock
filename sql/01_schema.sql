-- Food stock - schema (run as a MariaDB administrator, MariaDB >= 10.3).
-- Re-runnable: never drops existing data.

CREATE DATABASE IF NOT EXISTS lvlotoamdat
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lvlotoamdat;

-- Naming rule for names (products, locations, containers):
-- `name` keeps what the user typed (utf8mb4_unicode_ci: case- AND accent-insensitive
-- search/sort), `name_key` = LOWER(name) in utf8mb4_bin carries the UNIQUE index, so
-- uniqueness is case-insensitive but accent-sensitive ("Pâte" = "pâte", "pâte" <> "pâté").

-- ---------------------------------------------------------------- authentication

-- Users are created directly in the database (no self-registration).
CREATE TABLE IF NOT EXISTS food_stock_users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email         VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,           -- Argon2id / bcrypt, never plain text
    is_active     TINYINT(1)   NOT NULL DEFAULT 1, -- set to 0 to revoke access immediately
    language      CHAR(2) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'en'
                  CHECK (language IN ('en', 'fr')), -- interface language, chosen by the user
    created_at    DATETIME     NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_users_email (email)
) ENGINE=InnoDB;

-- Persistent login tokens: one row per device ("selector:validator" pattern).
CREATE TABLE IF NOT EXISTS food_stock_auth_tokens (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    selector       CHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    validator_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, -- SHA-256 of the secret part
    user_id        INT UNSIGNED NOT NULL,
    expires_at     DATETIME NOT NULL,                                       -- UTC
    created_at     DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_auth_tokens_selector (selector),
    KEY idx_food_stock_auth_tokens_expires (expires_at),
    CONSTRAINT fk_food_stock_auth_tokens_user FOREIGN KEY (user_id)
        REFERENCES food_stock_users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Login attempts for rate limiting (e-mail stored hashed, IPv6 grouped by /64).
CREATE TABLE IF NOT EXISTS food_stock_login_attempts (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email_hash   CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    ip           VARBINARY(16) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    KEY idx_food_stock_login_attempts_email (email_hash, attempted_at),
    KEY idx_food_stock_login_attempts_ip (ip, attempted_at),
    KEY idx_food_stock_login_attempts_time (attempted_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------- stock

-- Location: a place where food is stored (fridge, freezer...).
CREATE TABLE IF NOT EXISTS food_stock_locations (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    name_key   VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
               GENERATED ALWAYS AS (LOWER(name)) STORED,
    created_at DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_locations_name_key (name_key)
) ENGINE=InnoDB;

-- Product: a food name in the shared catalog. Stays even without stock entries.
CREATE TABLE IF NOT EXISTS food_stock_products (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    name_key   VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
               GENERATED ALWAYS AS (LOWER(name)) STORED,
    created_at DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_products_name_key (name_key)
) ENGINE=InnoDB;

-- Container type (bag, box...). Users can add new ones.
CREATE TABLE IF NOT EXISTS food_stock_containers (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50) NOT NULL,
    name_key   VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
               GENERATED ALWAYS AS (LOWER(name)) STORED,
    created_at DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_containers_name_key (name_key)
) ENGINE=InnoDB;

-- Initial list (needed in production too, not only for tests).
INSERT IGNORE INTO food_stock_containers (name) VALUES ('bag'), ('box'), ('pack'), ('pot');

-- Stock entry: product + location + packaging (container + amount + unit) + quantity + dates.
-- Amounts are always stored normalised (below 1 kg / 1 l converted down to g / ml), so the
-- UNIQUE "identity" key is what makes identical entries merge (INSERT ... ON DUPLICATE KEY).
-- expiry_key replaces NULL by a sentinel because NULLs never collide in a UNIQUE index.
CREATE TABLE IF NOT EXISTS food_stock_entries (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id   INT UNSIGNED NOT NULL,
    location_id  INT UNSIGNED NOT NULL,
    container_id INT UNSIGNED NOT NULL,
    amount       DECIMAL(10,3) NOT NULL,
    unit         ENUM('g', 'kg', 'ml', 'l', 'pieces') NOT NULL,
    quantity     INT UNSIGNED NOT NULL,       -- number of identical packages
    storage_date DATE NOT NULL,
    expiry_date  DATE NULL,
    expiry_key   DATE GENERATED ALWAYS AS (IFNULL(expiry_date, DATE '9999-12-31')) STORED,
    created_at   DATETIME NOT NULL DEFAULT (UTC_TIMESTAMP()),
    PRIMARY KEY (id),
    UNIQUE KEY uq_food_stock_entries_identity
        (product_id, location_id, container_id, amount, unit, storage_date, expiry_key),
    KEY idx_food_stock_entries_location (location_id),
    KEY idx_food_stock_entries_container (container_id),
    CONSTRAINT fk_food_stock_entries_product FOREIGN KEY (product_id)
        REFERENCES food_stock_products (id) ON DELETE RESTRICT,
    CONSTRAINT fk_food_stock_entries_location FOREIGN KEY (location_id)
        REFERENCES food_stock_locations (id) ON DELETE RESTRICT, -- a location with stock cannot be deleted
    CONSTRAINT fk_food_stock_entries_container FOREIGN KEY (container_id)
        REFERENCES food_stock_containers (id) ON DELETE RESTRICT,  -- a used container cannot be deleted
    CONSTRAINT chk_food_stock_entries_quantity CHECK (quantity >= 1),
    CONSTRAINT chk_food_stock_entries_amount CHECK (amount > 0)
) ENGINE=InnoDB;
