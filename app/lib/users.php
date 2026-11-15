<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const USER_EXISTS = 'This e-mail address is already used by another user.';
const USER_NOT_FOUND = 'This user no longer exists.';

/** All accounts, sorted by e-mail. The password hash is never selected. */
function all_users(): array
{
    return db_run('SELECT id, email, is_active FROM food_stock_users ORDER BY email, id')->fetchAll();
}

function find_user(int $id): ?array
{
    return db_fetch_one('SELECT id, email, is_active FROM food_stock_users WHERE id = ?', [$id]);
}

function require_user_id(string $raw): int
{
    $id = parse_id($raw);
    if ($id === null || find_user($id) === null) {
        throw new UserError(USER_NOT_FOUND);
    }
    return $id;
}

/** Lower-cased and checked. The UNIQUE key on e-mail does the duplicate check. */
function posted_user_email(): string
{
    $email = normalize_email(post_string('email', EMAIL_MAX_LENGTH));
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new UserError('Please enter a valid e-mail address.');
    }
    return $email;
}

/** Null means "leave the password unchanged" (edit form). */
function posted_user_password(bool $required): ?string
{
    $password = post_string('password', PASSWORD_MAX_LENGTH);
    if ($password === '') {
        if ($required) {
            throw new UserError('Please enter a password.');
        }
        return null;
    }
    return $password;
}

/** Checked before writing, so an existing e-mail gives a message, not a database error. */
function ensure_email_free(string $email, ?int $exceptId = null): void
{
    $owner = db_fetch_one('SELECT id FROM food_stock_users WHERE email = ?', [$email]);
    if ($owner !== null && (int) $owner['id'] !== $exceptId) {
        throw new UserError(USER_EXISTS);
    }
}

function create_user(string $email, #[\SensitiveParameter] string $password): void
{
    ensure_email_free($email);
    $hash = password_hash($password, password_algo());
    // The UNIQUE key still catches two requests racing for the same e-mail.
    db_run_unique(
        'INSERT INTO food_stock_users (email, password_hash) VALUES (?, ?)',
        [$email, $hash],
        USER_EXISTS
    );
}

/**
 * A new password revokes the account's remember-me tokens. Browser sessions already open
 * stay valid until they expire (30 min idle, 8 h total): the app has no session store to purge.
 */
function update_user(int $id, string $email, #[\SensitiveParameter] ?string $password): void
{
    require_user_id((string) $id);
    ensure_email_free($email, $id);
    db_run_unique('UPDATE food_stock_users SET email = ? WHERE id = ?', [$email, $id], USER_EXISTS);
    if ($password !== null) {
        $hash = password_hash($password, password_algo());
        db_run('UPDATE food_stock_users SET password_hash = ? WHERE id = ?', [$hash, $id]);
        revoke_user_tokens($id);
    }
}

/**
 * Deactivating blocks login at once (find_active_user() re-checks on every request)
 * and revokes the remember-me tokens. Reactivating does not restore them.
 */
function set_user_active(int $id, bool $active): void
{
    $current = current_user();
    if (!$active && $current !== null && (int) $current['id'] === $id) {
        throw new UserError('You cannot deactivate your own account.');
    }
    require_user_id((string) $id);
    db_run('UPDATE food_stock_users SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    if (!$active) {
        revoke_user_tokens($id);
    }
}

/**
 * Hard delete. Login tokens go with the row (ON DELETE CASCADE). No stock entry refers to a
 * user, so nothing else is lost. Deleting your own account is refused.
 */
function delete_user(int $id): void
{
    $current = current_user();
    if ($current !== null && (int) $current['id'] === $id) {
        throw new UserError('You cannot delete your own account.');
    }
    require_user_id((string) $id);
    db_run('DELETE FROM food_stock_users WHERE id = ?', [$id]);
}

function revoke_user_tokens(int $id): void
{
    db_run('DELETE FROM food_stock_auth_tokens WHERE user_id = ?', [$id]);
}
