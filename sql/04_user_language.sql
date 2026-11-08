-- Food stock - upgrade: adds the per-user interface language (run as a MariaDB administrator).
-- Needed only for a database created before this column existed; re-runnable.
-- If the database still uses the old food_ prefix, run 05_rename_prefix.sql first.
-- Then re-run 02_app_user.sql so the application may update this column.

USE lvlotoamdat;

ALTER TABLE food_stock_users
    ADD COLUMN IF NOT EXISTS language CHAR(2) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'en'
        CHECK (language IN ('en', 'fr'))
        AFTER is_active;
