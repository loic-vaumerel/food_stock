# Food stock

A home food-stock manager. Stocks are shared by all users. The interface is in English or French, using the EN / FR switch in the header. A signed-in user's choice is saved in their profile and restored at the next login. On the login page the choice applies until sign-in only, and English is the default.

## Layout

```
food_stock/
├── index.php, stock_add.php, entry.php,      public entry points (3 lines each)
│   locations.php, containers.php,
│   login.php, logout.php, language.php
├── assets/style.css
├── app/          (HTTP-denied) bootstrap, lib/, controllers/, templates/, lang/ (French catalog), bin/, .env
└── sql/          (HTTP-denied) schema, least-privilege DB user, test data
```

## Requirements

PHP 8.2+ with `pdo_mysql` and `mbstring`. `intl` is optional: it adds Unicode NFC normalisation of names.
MariaDB 10.3 or later.

## Setup

1. **Apache**: `AllowOverride All` must be enabled for the docroot. The `.htaccess` files are
   what keep `app/` (including `.env`) and `sql/` from being served.
   Check: `https://localhost/food_stock/app/.env` and `https://localhost/food_stock/sql/01_schema.sql`
   must return **403/404**.
2. **Database** (as a MariaDB admin). All scripts can be re-run safely:
   ```bash
   mariadb -u root -p < sql/01_schema.sql
   # edit the password in sql/02_app_user.sql first!
   mariadb -u root -p < sql/02_app_user.sql
   mariadb -u root -p < sql/03_test_data.sql   # dev only
   ```
   **Upgrading** from the login-only step: re-run `01_schema.sql` (it adds the new tables) and
   `02_app_user.sql` (it grants access to them).
   **Upgrading** a database that still uses the old `food_` table prefix: run `05_rename_prefix.sql` **once** first
   (renames tables, indexes and the FK, keeps the data, revokes grants on the old names), then re-run `02_app_user.sql`.
   **Upgrading** to the French/English interface: run `04_user_language.sql` (it adds `food_stock_users.language`), then re-run `02_app_user.sql`.
3. **Config**: `cp app/.env.example app/.env`, fill it in, then `chmod 640 app/.env`
   (readable by the Apache user only). `APP_TIMEZONE` (default UTC) and `EXPIRY_WARNING_DAYS`
   (default 7) are optional.
4. Open https://localhost/food_stock and sign in as `a@example.com` / `password`.

## Creating a user

```bash
read -rs P && printf '%s' "$P" | php app/bin/hash_password.php; unset P
```
```sql
INSERT INTO lvlotoamdat.food_stock_users (email, password_hash) VALUES ('me@example.com', '<hash>');
```
To disable a user: `UPDATE lvlotoamdat.food_stock_users SET is_active = 0 WHERE email = '…';`. Access is cut on their next request.
To log a user out of every device: `DELETE FROM lvlotoamdat.food_stock_auth_tokens WHERE user_id = …;`.

## Stock rules

| Term | Meaning |
|---|---|
| Product | Food name in the shared catalog. Unique ignoring case but not accents (`Pâte` = `pâte` ≠ `pâté`). Created on the fly and never deleted |
| Location | Where food is stored. Unique name, can be renamed. Can only be deleted when it holds no stock entry |
| Packaging | Container + amount + unit. Initial containers: bag, box, pack, pot. Users can add and rename them, and delete unused ones. Units: g, kg, ml, l, pieces |
| Stock entry | Product + location + packaging + quantity (number of packages) + storage date + optional expiry date |

- **Merging**: an entry identical to an existing one (same product, location, packaging, storage and expiry date)
  adds to that entry's quantity. This applies to adding, editing, moving and partial removal.
- **Normalisation** on every write: amounts below 1 kg or 1 l are stored in g or ml (0.5 kg → 500 g).
  Amounts are never converted up, so 1000 g stays 1000 g.
- **Precision**: kg and l allow up to 3 decimals (comma or dot). g, ml and pieces take whole numbers.
- **Partial removal**, entered in the packaging's unit: whole packages are used first, then one package is opened.
  What is left of it becomes a separate entry. Removing more than is available is refused.
- **Quantity 0** deletes the entry.

## Authentication model

| Item | Behaviour |
|---|---|
| Password | Argon2id (bcrypt fallback). Hashes are upgraded automatically at login |
| Session | `__Host-food_sid`. Strict mode, regenerated at every login, 30 min idle / 8 h absolute timeout |
| Remember-me | `__Host-food_remember` = `selector:validator`. The DB stores only the SHA-256 of the validator. One row per device. The token is rotated and its expiry reset to **now + 7 days** at each login or cookie authentication |
| Logout | POST + CSRF token. Deletes this device's token only, then destroys the session |
| Rate limit | Max 5 attempts per e-mail and 20 per IP (IPv6 grouped by /64) per 15 minutes, counted race-free. HTTP 429 when the limit is hit |
| Cookies | `Secure`, `HttpOnly`, `SameSite=Lax`, `__Host-` prefix |
