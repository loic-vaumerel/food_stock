# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Home food-stock web app. Stocks are shared by all users: there is no per-user ownership of data, only authentication.
Plain PHP 8.2+, no framework, **zero Composer dependencies** (keep it that way unless unavoidable), MariaDB via PDO, vanilla HTML/CSS.
There is **no JavaScript**, and the CSP (`send_security_headers()`) blocks scripts. Native HTML (`<datalist>`, `type="date"`, GET filter forms) covers the UI. Adding JS requires a CSP change.
The user's spec, glossary and rules are in `README.md` ("Stock rules"). Use the glossary terms (product, location, packaging, stock entry, quantity) consistently in code, DB and UI.

## Environment & commands

- Runs in Docker: Apache 2.4 (HTTPS) + PHP 8.5, MariaDB in a separate container (reached as `host.docker.internal`). This folder is served at https://localhost/food_stock (docroot = parent folder).
  - The URL path is hardcoded in `APP_URL` (`app/.env` and `.env.example`) and in the root `.htaccess` `RedirectMatch 404 ^/food_stock/(app|sql)`. If the folder is renamed or moved, update both, or `app/.env` becomes publicly readable. The leftover `food` names (`FOOD_APP` constant, `__Host-food_*` cookies) are intentional: renaming the cookies would log everyone out.
- No build step, no test suite, no linter config. Syntax check inside the PHP container: `find . -name '*.php' -exec php -l {} \;`
- DB setup (as MariaDB admin), in order: `sql/01_schema.sql` → `sql/02_app_user.sql` (edit the password first) → `sql/03_test_data.sql` (dev only, password `password`, `c@example.com` is inactive). All are re-runnable. After any schema change, re-run `02_app_user.sql`. Upgrading an existing database: `sql/05_rename_prefix.sql` (old `food_` prefix → `food_stock_`, run once), then `sql/04_user_language.sql` if needed, then `02_app_user.sql`. `05` needs MariaDB ≥ 10.5.2 (`RENAME INDEX`); everything else needs ≥ 10.3.
- Config: `app/.env` (copy of `app/.env.example`), read via `env()` / `env_bool()` / `env_int()` (never `getenv`). Optional: `APP_TIMEZONE` (default UTC, defines "today" for storage dates and expiry highlights) and `EXPIRY_WARNING_DAYS` (default 7). Database and DB user are both named `lvlotoamdat`.
- Create a user: `read -rs P && printf '%s' "$P" | php app/bin/hash_password.php; unset P`, then `INSERT INTO food_stock_users (email, password_hash) …`. There is no registration or user management in the app, by design.

## Architecture

- **Request flow**: a public page (`index.php`, `entry.php`, …) requires `app/bootstrap.php`, then `app/controllers/<page>.php`, and calls `run_<page>_page()`.
  - Bootstrap defines `FOOD_APP`/`APP_ROOT`, requires every `app/lib/*.php` (the order matters), and then runs, in order: errors → env + `validate_config()` → timezone → HTTPS → headers → session.
  - Every lib, controller and template starts with `defined('FOOD_APP') || exit;`.
  - **Adding a page** = a public `<page>.php` copied from an existing one (3 lines) + `app/controllers/<page>.php` with `run_<page>_page()` + a template, which renders with a `page` key.
    - Add that key to `LANGUAGE_RETURN_PAGES` (`lib/i18n.php`), or the EN/FR switch returns to the home page.
    - Add it to `NAV_PAGES` (`lib/view.php`) if the page belongs in the header.
  - `login.php`, `logout.php` and `language.php` are exceptions: inline scripts with no controller.
  - **Adding a lib file** = add its `require` to `bootstrap.php` (nothing is autoloaded).
- **Non-public code** lives in `app/` and `sql/`, protected only by `.htaccess` (`Require all denied`) plus root `.htaccess` rules.
- **Controllers** follow one pattern for POST: `verify_post_request()` → `run_user_action(fn () => match ($action) {...})` → on success `flash_set()` + `redirect()` (PRG), on error re-render with the message.
  - Business errors are thrown as `UserError('<English message with {placeholders}>', [params])` (`lib/validation.php`). `run_user_action()` translates the message with `t()`, and it is shown escaped.
  - `run_user_action()` maps FK errors 1451/1452 to a generic "data changed meanwhile" message, and rethrows any other `PDOException` (logged, generic 500).
- **UI language** (`lib/i18n.php`): English or French. Translation is gettext-style: the English text is the key, `app/lang/fr.php` maps it to French, and a missing entry falls back to English.
  - In templates, write `<?= e(t('English text')) ?>`, using `{name}` placeholders plus a params array for dynamic parts. **Every new UI string needs a French entry in `app/lang/fr.php`.**
  - Translated at display time, so the code passes English: page `title`, `flash_set()` messages, `abort()` messages, `NAV_PAGES` labels and the `select_options()` placeholder.
  - Language source: when signed in, `food_stock_users.language`, saved by `language.php` (POST + CSRF, return page from a fixed list). When signed out, `$_SESSION['lang']`, never saved. `start_user_session()` wipes the session, so the profile language applies after login. The default is English.
  - Display numbers use `format_number()` (localized decimal separator). `format_milli()` keeps the dot. User data (product, location and container names) is never translated.
- **Views**: `render($template, $vars)` renders `app/templates/<name>.php` inside `layout.php`. It adds `user`, `page` and `flash` automatically. Templates read `$v[...]`. `select_options()` builds escaped `<option>` lists.
- **DB access**: only through `db_run()` / `db_fetch_one()` / `db_run_unique()` / `db_transaction()` (`lib/db.php`), with constant SQL, bound params and native prepares.
- **Stock model** (`lib/amount.php`, `lib/stock.php`, `lib/stock_actions.php`):
  - Amounts are integers in thousandths of the unit ("milli": 1.5 kg = 1500). There are no floats. The DB column is `DECIMAL(10,3)`.
  - Every packaging write goes through `upsert_stock_entry()`. It applies `normalize_amount()` (below 1 kg / 1 l → g / ml, never up), then `INSERT … ON DUPLICATE KEY UPDATE quantity = quantity + …`.
  - The merge rule *is* the UNIQUE key `uq_food_stock_entries_identity`. `expiry_key` is a generated column, needed because NULLs never collide.
  - Operations on an existing entry run in `db_transaction()` and start with `lock_stock_entry()` (`SELECT … FOR UPDATE`).
  - Edit = delete + upsert, which merges automatically. `set_stock_quantity()` deletes at 0.
  - Partial removal math is `plan_partial_removal()`.
- **Name uniqueness**: tables keep `name` (utf8mb4_unicode_ci: case- and accent-insensitive search and sort). The UNIQUE index is on the generated `name_key = LOWER(name)` in utf8mb4_bin, which is case-insensitive but accent-sensitive. Look names up with `WHERE name_key = LOWER(?)`. Names go through `validate_name()` (whitespace collapse, NFC, no control characters).
- **Products** are created outside transactions by `find_or_create_product()`, which handles a concurrent duplicate. Location and container deletion relies on the RESTRICT FKs, which makes the "only when empty / unused" check atomic.
- **Auth** (`lib/auth.php`, `lib/remember.php`, `lib/session.php`, `lib/rate_limit.php`):
  - `current_user()` checks the session first (re-checking `is_active` every request), then the `selector:validator` remember cookie. Using the cookie rotates its token and extends it by 7 days.
  - Sessions expire after 30 min idle or 8 h total.
  - Rate limiting inserts the attempt first, then counts it.
- **Redirects** go only through `redirect('<internal path>')`, built from `APP_URL`. Never add a user-controlled URL target.

## Conventions (project requirements)

- Functions short and reusable, ideally < 20 lines.
- All tables are prefixed `food_stock_` (`food_stock_entries` is the stock entries table itself, not double-prefixed). Index, FK and check names follow: `uq_food_stock_…`, `idx_food_stock_…`, `fk_food_stock_…`, `chk_food_stock_…`.
- Least privilege: the app DB user has explicit per-table grants in `sql/02_app_user.sql` (products are SELECT/INSERT only). **Any new table, or new kind of query on an existing table, needs a matching GRANT there**, or it fails at runtime.
- Security (OWASP Cheat Sheets) is mandatory on every change: SQLi, XSS, auth/session, access control, CSRF, data/config leaks, DB privileges, outdated deps, path traversal, command injection, open redirects, insecure deserialization (no file uploads exist). Apply protections unprompted, warn before implementing anything that weakens them, and briefly list the protections relied on when finishing a task.
- Every page except `login.php` must call `require_login()`. Every POST form needs `csrf_field()`, and every POST handler needs `verify_post_request()`.
- Never display errors to clients (logged via `handle_uncaught_exception`). Mark password parameters `#[\SensitiveParameter]`.
- The user wants every prompt and Claude's questions appended to `/workspace/.claude/prompts.md`.
