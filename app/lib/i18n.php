<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Interface translations, gettext-style: the English text is the key and
 * app/lang/<code>.php maps it to the other language. A missing translation
 * falls back to English. t() does NOT escape: templates still wrap it in e().
 */
const LANGUAGES = ['en' => 'English', 'fr' => 'Français'];
const DEFAULT_LANGUAGE = 'en';
const DECIMAL_SEPARATORS = ['en' => '.', 'fr' => ','];

/** Page key => path the language switch returns to (fixed list: no open redirect). */
const LANGUAGE_RETURN_PAGES = [
    'home' => 'index.php',
    'add' => 'stock_add.php',
    'locations' => 'locations.php',
    'containers' => 'containers.php',
    'login' => 'login.php',
];

function is_language(mixed $code): bool
{
    return is_string($code) && isset(LANGUAGES[$code]);
}

/** Signed in: the profile language. Signed out: the unsaved choice made on the login page. */
function current_language(): string
{
    $user = current_user();
    $code = $user === null ? ($_SESSION['lang'] ?? null) : $user['language'];
    return is_language($code) ? $code : DEFAULT_LANGUAGE;
}

/** @param array<string, string|int> $params values for the {name} placeholders */
function t(string $text, array $params = []): string
{
    $translated = translations(current_language())[$text] ?? $text;
    $pairs = [];
    foreach ($params as $name => $value) {
        $pairs['{' . $name . '}'] = (string) $value;
    }
    return strtr($translated, $pairs);
}

/** The catalog path comes from the LANGUAGES whitelist only (no path traversal). */
function translations(string $code): array
{
    static $catalogs = [];
    if ($code === DEFAULT_LANGUAGE || !is_language($code)) {
        return [];
    }
    return $catalogs[$code] ??= require APP_ROOT . '/lang/' . $code . '.php';
}

function decimal_separator(): string
{
    return DECIMAL_SEPARATORS[current_language()];
}

/** Signed in: saved in the profile. Signed out (login page): kept in the session only. */
function switch_language(string $code): void
{
    if (!is_language($code)) {
        return;
    }
    $user = current_user();
    if ($user === null) {
        $_SESSION['lang'] = $code;
        return;
    }
    db_run('UPDATE food_stock_users SET language = ? WHERE id = ?', [$code, (int) $user['id']]);
}

/** Back to the page the switch was used on; a stock entry page is identified by its id. */
function language_return_path(string $page, string $rawEntryId): string
{
    $entryId = parse_id($rawEntryId);
    if ($entryId !== null) {
        return 'entry.php?id=' . $entryId;
    }
    return LANGUAGE_RETURN_PAGES[$page] ?? 'index.php';
}
