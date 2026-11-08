-- Food stock - least-privilege application account (run as a MariaDB administrator).
--
-- BEFORE RUNNING: replace CHANGE_ME_... with a long random password
-- (e.g. `openssl rand -base64 32`) and put the same value in app/.env (DB_PASS).
-- '%' is needed because PHP connects from another Docker container; narrow it to
-- that container's host/subnet if you can (e.g. 'lvlotoamdat'@'172.18.%').
-- Re-runnable: run it again after every schema change (grants are per table).
--
-- The application cannot: create/drop/alter tables, create users, read other
-- databases, insert or delete users, change anything on a user but its password hash
-- and interface language,
-- or update/delete products.

CREATE USER IF NOT EXISTS 'lvlotoamdat'@'%' IDENTIFIED BY 'CHANGE_ME_TO_A_LONG_RANDOM_PASSWORD';

-- food_stock_users: read + transparent password re-hash + saving the chosen language only.
GRANT SELECT ON lvlotoamdat.food_stock_users TO 'lvlotoamdat'@'%';
GRANT UPDATE (password_hash, language) ON lvlotoamdat.food_stock_users TO 'lvlotoamdat'@'%';

-- food_stock_auth_tokens: full row management (issue, rotate, revoke, purge).
GRANT SELECT, INSERT, UPDATE, DELETE ON lvlotoamdat.food_stock_auth_tokens TO 'lvlotoamdat'@'%';

-- food_stock_login_attempts: no UPDATE needed.
GRANT SELECT, INSERT, DELETE ON lvlotoamdat.food_stock_login_attempts TO 'lvlotoamdat'@'%';

-- food_stock_locations: add, rename, delete (deleting a non-empty one is blocked by a FK).
GRANT SELECT, INSERT, UPDATE, DELETE ON lvlotoamdat.food_stock_locations TO 'lvlotoamdat'@'%';

-- food_stock_products: the catalog only grows (created on the fly).
GRANT SELECT, INSERT ON lvlotoamdat.food_stock_products TO 'lvlotoamdat'@'%';

-- food_stock_containers: add, rename, delete (deleting a used one is blocked by a FK).
GRANT SELECT, INSERT, UPDATE, DELETE ON lvlotoamdat.food_stock_containers TO 'lvlotoamdat'@'%';

-- food_stock_entries: add (merge), edit, remove, move.
GRANT SELECT, INSERT, UPDATE, DELETE ON lvlotoamdat.food_stock_entries TO 'lvlotoamdat'@'%';

FLUSH PRIVILEGES;
