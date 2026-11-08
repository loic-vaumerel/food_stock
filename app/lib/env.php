<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Minimal, strict .env loader (no dependency).
 * Values are kept in a private registry: not copied to $_ENV / getenv(),
 * so they don't show up in phpinfo() or reach child processes.
 */
function load_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Configuration file is missing or unreadable.');
    }
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) as $number => $line) {
        $pair = parse_env_line($line, $number + 1);
        if ($pair !== null) {
            $values[$pair[0]] = $pair[1];
        }
    }
    env_registry($values);
}

/** @return array{0: string, 1: string}|null */
function parse_env_line(string $line, int $number): ?array
{
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        return null;
    }
    if (preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $m) !== 1) {
        throw new RuntimeException("Invalid syntax in .env at line {$number}.");
    }
    return [$m[1], unquote_env_value($m[2])];
}

function unquote_env_value(string $value): string
{
    $first = $value[0] ?? '';
    if (strlen($value) >= 2 && ($first === '"' || $first === "'") && $value[-1] === $first) {
        return substr($value, 1, -1);
    }
    return $value;
}

/** @param array<string, string>|null $set */
function env_registry(?array $set = null): array
{
    static $values = [];
    if ($set !== null) {
        $values = $set;
    }
    return $values;
}

function env(string $key): string
{
    $value = env_registry()[$key] ?? null;
    if ($value === null || $value === '') {
        throw new RuntimeException("Missing configuration value: {$key}");
    }
    return $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env_registry()[$key] ?? null;
    if ($value === null) {
        return $default;
    }
    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}

function env_string(string $key, string $default): string
{
    $value = env_registry()[$key] ?? '';
    return $value === '' ? $default : $value;
}

function env_int(string $key, int $default, int $min, int $max): int
{
    $value = env_registry()[$key] ?? '';
    if ($value === '') {
        return $default;
    }
    if (preg_match('/^\d{1,9}$/D', $value) !== 1 || (int) $value < $min || (int) $value > $max) {
        throw new RuntimeException("Invalid configuration value: {$key}");
    }
    return (int) $value;
}

function validate_config(): void
{
    $url = env('APP_URL');
    if (!str_starts_with($url, 'https://') || !is_string(parse_url($url, PHP_URL_HOST))) {
        throw new RuntimeException('APP_URL must be an absolute https:// URL.');
    }
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
        env($key);
    }
    expiry_warning_days();
}

/** "Today" (default storage date, expiry highlights) depends on the household's time zone. */
function configure_timezone(): void
{
    $timezone = env_string('APP_TIMEZONE', 'UTC');
    if (!in_array($timezone, timezone_identifiers_list(), true)) {
        throw new RuntimeException('APP_TIMEZONE is not a valid time zone identifier.');
    }
    date_default_timezone_set($timezone);
}

/** N in "expiring within N days". */
function expiry_warning_days(): int
{
    return env_int('EXPIRY_WARNING_DAYS', 7, 0, 365);
}
