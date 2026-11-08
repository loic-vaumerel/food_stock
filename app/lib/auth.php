<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const EMAIL_MAX_LENGTH = 254;
const PASSWORD_MAX_LENGTH = 1024; // caps hashing cost (DoS) - well above any real password

/** Argon2id when PHP is built with it (OWASP first choice), bcrypt otherwise. */
function password_algo(): string
{
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
}

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

/* ---------- Current user resolution (session first, then remember cookie) ---------- */

function current_user(): ?array
{
    static $resolved = false;
    static $user = null;
    if (!$resolved) {
        $user = user_from_session() ?? user_from_remember_cookie();
        $resolved = true;
    }
    return $user;
}

/** Re-checks the account on every request so a deactivated user loses access immediately. */
function user_from_session(): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId)) {
        return null;
    }
    $user = find_active_user($userId);
    if ($user === null) {
        revoke_current_remember_token();
        reset_session();
    }
    return $user;
}

/** Cookie authentication: rotates the token and extends it to now + 1 week. */
function user_from_remember_cookie(): ?array
{
    $token = valid_remember_token();
    if ($token === null) {
        return null;
    }
    $user = find_active_user((int) $token['user_id']);
    if ($user === null) {
        return null;
    }
    rotate_remember_token((int) $token['id']);
    start_user_session((int) $user['id']);
    return $user;
}

function require_login(): array
{
    return current_user() ?? redirect('login.php');
}

function find_active_user(int $userId): ?array
{
    return db_fetch_one('SELECT id, email, language FROM food_stock_users WHERE id = ? AND is_active = 1', [$userId]);
}

/* ---------- Login ---------- */

/** @return array{email: string, error: ?string} */
function handle_login_post(): array
{
    $email = normalize_email(post_string('email', EMAIL_MAX_LENGTH));
    $password = post_string('password', PASSWORD_MAX_LENGTH);
    $result = ($email === '' || $password === '') ? 'invalid' : attempt_login($email, $password);
    if ($result === 'ok') {
        redirect('index.php');
    }
    if ($result === 'limited') {
        http_response_code(429);
    }
    return ['email' => $email, 'error' => login_error_message($result)];
}

/** @return 'ok'|'invalid'|'limited' */
function attempt_login(string $email, #[\SensitiveParameter] string $password): string
{
    $emailKey = login_email_key($email);
    if (!reserve_login_attempt($emailKey, client_ip_key())) {
        return 'limited';
    }
    $user = verify_credentials($email, $password);
    if ($user === null) {
        return 'invalid';
    }
    clear_login_attempts($emailKey);
    complete_login((int) $user['id']);
    return 'ok';
}

/** Same work (one Argon2/bcrypt computation) whether or not the e-mail exists: no user enumeration by timing. */
function verify_credentials(string $email, #[\SensitiveParameter] string $password): ?array
{
    $user = db_fetch_one(
        'SELECT id, password_hash FROM food_stock_users WHERE email = ? AND is_active = 1',
        [$email]
    );
    if ($user === null) {
        password_hash($password, password_algo());
        return null;
    }
    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }
    rehash_password_if_needed((int) $user['id'], $password, $user['password_hash']);
    return $user;
}

function rehash_password_if_needed(int $userId, #[\SensitiveParameter] string $password, string $hash): void
{
    if (password_needs_rehash($hash, password_algo())) {
        db_run('UPDATE food_stock_users SET password_hash = ? WHERE id = ?', [password_hash($password, password_algo()), $userId]);
    }
}

/** Replaces any token this device already had, then issues a fresh 1-week one. */
function complete_login(int $userId): void
{
    revoke_current_remember_token();
    purge_expired_remember_tokens();
    start_user_session($userId);
    issue_remember_token($userId);
}

function login_error_message(string $result): string
{
    return $result === 'limited'
        ? t('Too many login attempts. Please wait {minutes} minutes and try again.', ['minutes' => LOGIN_WINDOW_MINUTES])
        : t('Invalid e-mail or password.');
}

/* ---------- Logout ---------- */

function logout(): void
{
    revoke_current_remember_token();
    destroy_session();
}
