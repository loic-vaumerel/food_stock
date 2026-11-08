<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Persistent login, "selector:validator" pattern (OWASP / Paragon IE):
 * - selector: public lookup key, validator: secret, stored only as SHA-256;
 * - one row per device, so logout revokes the current device only;
 * - validator rotated and expiry pushed to now + 1 week on every use.
 */
const REMEMBER_COOKIE = '__Host-food_remember';
const REMEMBER_LIFETIME = 604800; // 1 week

function issue_remember_token(int $userId): void
{
    [$selector, $validator] = new_remember_pair();
    db_run(
        'INSERT INTO food_stock_auth_tokens (selector, validator_hash, user_id, expires_at)
         VALUES (?, ?, ?, UTC_TIMESTAMP() + INTERVAL ? SECOND)',
        [$selector, hash('sha256', $validator), $userId, REMEMBER_LIFETIME]
    );
    set_remember_cookie($selector, $validator);
}

/** @return array{0: string, 1: string} */
function new_remember_pair(): array
{
    return [bin2hex(random_bytes(12)), bin2hex(random_bytes(32))];
}

function set_remember_cookie(string $selector, string $validator): void
{
    setcookie(REMEMBER_COOKIE, $selector . ':' . $validator, cookie_options(time() + REMEMBER_LIFETIME));
}

function clear_remember_cookie(): void
{
    setcookie(REMEMBER_COOKIE, '', cookie_options(1));
}

/** Strict format check before anything touches the DB. @return array{0: string, 1: string}|null */
function read_remember_cookie(): ?array
{
    $raw = $_COOKIE[REMEMBER_COOKIE] ?? null;
    if (!is_string($raw) || preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/D', $raw, $m) !== 1) {
        return null;
    }
    return [$m[1], $m[2]];
}

/** Returns the token row (id, user_id) if the cookie is valid, clears the cookie otherwise. */
function valid_remember_token(): ?array
{
    $cookie = read_remember_cookie();
    $token = $cookie === null ? null : find_remember_token($cookie[0]);
    if ($token === null || !hash_equals($token['validator_hash'], hash('sha256', $cookie[1]))) {
        if (isset($_COOKIE[REMEMBER_COOKIE])) {
            clear_remember_cookie();
        }
        return null;
    }
    return $token;
}

function find_remember_token(string $selector): ?array
{
    return db_fetch_one(
        'SELECT t.id, t.user_id, t.validator_hash
           FROM food_stock_auth_tokens t
           JOIN food_stock_users u ON u.id = t.user_id
          WHERE t.selector = ? AND t.expires_at > UTC_TIMESTAMP() AND u.is_active = 1',
        [$selector]
    );
}

function rotate_remember_token(int $tokenId): void
{
    [$selector, $validator] = new_remember_pair();
    db_run(
        'UPDATE food_stock_auth_tokens
            SET selector = ?, validator_hash = ?, expires_at = UTC_TIMESTAMP() + INTERVAL ? SECOND
          WHERE id = ?',
        [$selector, hash('sha256', $validator), REMEMBER_LIFETIME, $tokenId]
    );
    set_remember_cookie($selector, $validator);
}

/** Deletes this device's token only; other devices stay logged in. */
function revoke_current_remember_token(): void
{
    $cookie = read_remember_cookie();
    if ($cookie !== null) {
        db_run('DELETE FROM food_stock_auth_tokens WHERE selector = ?', [$cookie[0]]);
    }
    if (isset($_COOKIE[REMEMBER_COOKIE])) {
        clear_remember_cookie();
    }
}

function purge_expired_remember_tokens(): void
{
    db_run('DELETE FROM food_stock_auth_tokens WHERE expires_at <= UTC_TIMESTAMP()');
}
