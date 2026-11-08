-- Food stock - upgrade: table prefix food_ -> food_stock_ (run ONCE as a MariaDB administrator,
-- MariaDB >= 10.5.2 for RENAME INDEX). Keeps all data. Not re-runnable.
--
-- Order for an existing database:
--   1. this script
--   2. 04_user_language.sql   (only if food_stock_users has no `language` column yet)
--   3. 02_app_user.sql        (grants on the new table names)
-- food_stock_entries already had the right name and keeps it.

USE lvlotoamdat;

-- One atomic statement: either every table is renamed or none is. FKs follow automatically.
RENAME TABLE
    food_users          TO food_stock_users,
    food_auth_tokens    TO food_stock_auth_tokens,
    food_login_attempts TO food_stock_login_attempts,
    food_locations      TO food_stock_locations,
    food_products       TO food_stock_products,
    food_containers     TO food_stock_containers;

-- Index names follow the table prefix.
ALTER TABLE food_stock_users RENAME INDEX uq_food_users_email TO uq_food_stock_users_email;

ALTER TABLE food_stock_auth_tokens
    RENAME INDEX uq_food_auth_tokens_selector TO uq_food_stock_auth_tokens_selector,
    RENAME INDEX idx_food_auth_tokens_expires TO idx_food_stock_auth_tokens_expires;

ALTER TABLE food_stock_login_attempts
    RENAME INDEX idx_food_login_attempts_email TO idx_food_stock_login_attempts_email,
    RENAME INDEX idx_food_login_attempts_ip    TO idx_food_stock_login_attempts_ip,
    RENAME INDEX idx_food_login_attempts_time  TO idx_food_stock_login_attempts_time;

ALTER TABLE food_stock_locations  RENAME INDEX uq_food_locations_name_key  TO uq_food_stock_locations_name_key;
ALTER TABLE food_stock_products   RENAME INDEX uq_food_products_name_key   TO uq_food_stock_products_name_key;
ALTER TABLE food_stock_containers RENAME INDEX uq_food_containers_name_key TO uq_food_stock_containers_name_key;

-- A foreign key cannot be renamed: drop it, rename its backing index, re-create it.
ALTER TABLE food_stock_auth_tokens DROP FOREIGN KEY fk_food_auth_tokens_user;
ALTER TABLE food_stock_auth_tokens RENAME INDEX fk_food_auth_tokens_user TO fk_food_stock_auth_tokens_user;
ALTER TABLE food_stock_auth_tokens
    ADD CONSTRAINT fk_food_stock_auth_tokens_user FOREIGN KEY (user_id)
        REFERENCES food_stock_users (id) ON DELETE CASCADE;

-- Least privilege: grants are stored per table NAME and do not follow a rename.
-- Remove the ones left on the old names (also drops column grants), then run 02_app_user.sql.
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_users          FROM 'lvlotoamdat'@'%';
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_auth_tokens    FROM 'lvlotoamdat'@'%';
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_login_attempts FROM 'lvlotoamdat'@'%';
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_locations      FROM 'lvlotoamdat'@'%';
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_products       FROM 'lvlotoamdat'@'%';
REVOKE ALL PRIVILEGES ON lvlotoamdat.food_containers     FROM 'lvlotoamdat'@'%';
FLUSH PRIVILEGES;
