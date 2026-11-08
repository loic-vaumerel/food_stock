<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const LOGIN_WINDOW_MINUTES = 15;
const LOGIN_MAX_PER_EMAIL = 5;
const LOGIN_MAX_PER_IP = 20;
const LOGIN_ATTEMPTS_RETENTION_HOURS = 24;

/** E-mails are stored hashed in the attempts table (no plain PII in logs). */
function login_email_key(string $email): string
{
    return hash('sha256', $email);
}

/**
 * Inserts the attempt FIRST, then counts (including concurrent ones), so a burst
 * of parallel requests cannot slip past the limit. A rejected attempt is removed
 * again so it doesn't extend the lockout. Returns false when rate-limited.
 */
function reserve_login_attempt(string $emailKey, string $ipKey): bool
{
    purge_old_login_attempts();
    db_run('INSERT INTO food_stock_login_attempts (email_hash, ip) VALUES (?, ?)', [$emailKey, $ipKey]);
    $attemptId = (int) db()->lastInsertId();
    if (count_email_attempts($emailKey) > LOGIN_MAX_PER_EMAIL || count_ip_attempts($ipKey) > LOGIN_MAX_PER_IP) {
        db_run('DELETE FROM food_stock_login_attempts WHERE id = ?', [$attemptId]);
        return false;
    }
    return true;
}

function count_email_attempts(string $emailKey): int
{
    return (int) db_run(
        'SELECT COUNT(*) FROM food_stock_login_attempts
          WHERE email_hash = ? AND attempted_at > UTC_TIMESTAMP() - INTERVAL ? MINUTE',
        [$emailKey, LOGIN_WINDOW_MINUTES]
    )->fetchColumn();
}

function count_ip_attempts(string $ipKey): int
{
    return (int) db_run(
        'SELECT COUNT(*) FROM food_stock_login_attempts
          WHERE ip = ? AND attempted_at > UTC_TIMESTAMP() - INTERVAL ? MINUTE',
        [$ipKey, LOGIN_WINDOW_MINUTES]
    )->fetchColumn();
}

/** After a successful login, the e-mail's failure counter is reset. */
function clear_login_attempts(string $emailKey): void
{
    db_run('DELETE FROM food_stock_login_attempts WHERE email_hash = ?', [$emailKey]);
}

function purge_old_login_attempts(): void
{
    db_run(
        'DELETE FROM food_stock_login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL ? HOUR',
        [LOGIN_ATTEMPTS_RETENTION_HOURS]
    );
}
