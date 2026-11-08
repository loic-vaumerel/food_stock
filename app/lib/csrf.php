<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Synchronizer token pattern, one random token per session (OWASP CSRF Cheat Sheet). */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Every state-changing request must pass both checks. */
function verify_post_request(): void
{
    if (!is_same_origin_request() || !is_valid_csrf_token()) {
        abort(403, 'Your session has expired or the request is invalid. Please reload the page and try again.');
    }
}

function is_valid_csrf_token(): bool
{
    $sent = $_POST['csrf_token'] ?? null;
    $expected = $_SESSION['csrf'] ?? null;
    return is_string($sent) && is_string($expected) && $expected !== '' && hash_equals($expected, $sent);
}

/** Defence in depth: reject requests whose Origin header is foreign (or "null"). */
function is_same_origin_request(): bool
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
    return $origin === null || $origin === app_origin();
}
