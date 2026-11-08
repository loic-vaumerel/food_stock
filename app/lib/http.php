<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Builds an absolute URL from the configured APP_URL only (never from Host headers). */
function app_url(string $path = ''): string
{
    return rtrim(env('APP_URL'), '/') . '/' . ltrim($path, '/');
}

function app_origin(): string
{
    $parts = parse_url(env('APP_URL'));
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    return $parts['scheme'] . '://' . $parts['host'] . $port;
}

/**
 * Redirects to an internal, hard-coded path. There is deliberately no
 * "return to" parameter, so open redirects are impossible.
 */
function redirect(string $path): never
{
    header('Location: ' . app_url($path), true, 303);
    exit;
}

function is_https(): bool
{
    $https = $_SERVER['HTTPS'] ?? '';
    return $https !== '' && strtolower((string) $https) !== 'off';
}

function require_https(): void
{
    if (!is_https()) {
        redirect('');
    }
}

function request_method(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}

/** @param list<string> $allowed */
function require_method(array $allowed): void
{
    if (!in_array(request_method(), $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        abort(405, 'Method not allowed.');
    }
}

/**
 * Returns a request field as a string, or '' if absent, not a string (array
 * injection like name[]=x) or longer than $maxLength bytes.
 *
 * @param array<mixed> $source $_GET or $_POST
 */
function request_string(array $source, string $key, int $maxLength): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value) || strlen($value) > $maxLength) {
        return '';
    }
    return $value;
}

function post_string(string $key, int $maxLength): string
{
    return request_string($_POST, $key, $maxLength);
}

function get_string(string $key, int $maxLength): string
{
    return request_string($_GET, $key, $maxLength);
}

/**
 * Client IP as a binary key for rate limiting. Only REMOTE_ADDR is trusted
 * (X-Forwarded-For is spoofable). IPv6 is grouped by /64 network.
 */
function client_ip_key(): string
{
    $binary = @inet_pton((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($binary === false) {
        return str_repeat("\0", 4);
    }
    return strlen($binary) === 16 ? substr($binary, 0, 8) . str_repeat("\0", 8) : $binary;
}

function abort(int $status, string $message): never
{
    http_response_code($status);
    render('error', ['title' => 'Error', 'message' => t($message)]);
    exit;
}
