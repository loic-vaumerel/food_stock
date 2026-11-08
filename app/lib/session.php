<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** "__Host-" prefix: browser enforces Secure, Path=/ and no Domain attribute. */
const SESSION_COOKIE = '__Host-food_sid';
const SESSION_IDLE_TIMEOUT = 1800;      // 30 min of inactivity
const SESSION_ABSOLUTE_TIMEOUT = 28800; // 8 h max, then re-auth via remember cookie

function start_secure_session(): void
{
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_TIMEOUT);
    session_name(SESSION_COOKIE);
    session_set_cookie_params(cookie_options(0));
    session_start();
    enforce_session_timeouts();
}

/** @return array<string, mixed> */
function cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function enforce_session_timeouts(): void
{
    $now = time();
    $lastSeen = $_SESSION['last_seen'] ?? $now;
    $startedAt = $_SESSION['started_at'] ?? $now;
    if ($now - $lastSeen > SESSION_IDLE_TIMEOUT || $now - $startedAt > SESSION_ABSOLUTE_TIMEOUT) {
        reset_session();
        return;
    }
    $_SESSION['started_at'] = $startedAt;
    $_SESSION['last_seen'] = $now;
}

/** Drops all session data (including authentication) under a brand-new session id. */
function reset_session(): void
{
    session_regenerate_id(true);
    $now = time();
    $_SESSION = ['started_at' => $now, 'last_seen' => $now];
}

/** New session id + fresh data on privilege change (prevents session fixation). */
function start_user_session(int $userId): void
{
    session_regenerate_id(true);
    $now = time();
    $_SESSION = ['user_id' => $userId, 'started_at' => $now, 'last_seen' => $now];
}

function destroy_session(): void
{
    $_SESSION = [];
    setcookie(SESSION_COOKIE, '', cookie_options(1));
    session_destroy();
}
