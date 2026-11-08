<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** HTTP hardening headers (OWASP HTTP Headers Cheat Sheet). No JS is used, so scripts are fully blocked. */
function send_security_headers(): void
{
    header_remove('X-Powered-By');
    header("Content-Security-Policy: default-src 'none'; style-src 'self'; img-src 'self'; "
        . "form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('Cache-Control: no-store');
    if (env_bool('APP_HSTS')) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/** Context-aware output escaping for HTML body and quoted attributes. */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}
