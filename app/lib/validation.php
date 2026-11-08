<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const QUANTITY_MAX = 99999;
const DATE_MIN = '2000-01-01';
const DATE_MAX = '2100-12-31';

/**
 * A problem the user can fix. The message is the English text (translation key);
 * run_user_action() translates it, filling the {name} placeholders from $params.
 */
final class UserError extends RuntimeException
{
    /** @param array<string, string|int> $params */
    public function __construct(string $message, public readonly array $params = [])
    {
        parent::__construct($message);
    }
}

/**
 * Runs a user action. Returns null on success, or a translated error message.
 * Foreign-key failures mean another user changed the data meanwhile.
 */
function run_user_action(callable $action): ?string
{
    try {
        $action();
        return null;
    } catch (UserError $e) {
        return t($e->getMessage(), $e->params);
    } catch (PDOException $e) {
        if (!in_array(pdo_error_code($e), [1451, 1452], true)) {
            throw $e;
        }
        return t('The data was changed by someone else in the meantime. Please try again.');
    }
}

/** Unicode-aware whitespace cleanup + NFC (so "é" typed two ways is one name). */
function normalize_text(string $raw): string
{
    $text = preg_replace('/\s+/u', ' ', $raw);
    if (!is_string($text)) {
        return ''; // invalid UTF-8
    }
    $text = trim($text);
    if (class_exists(Normalizer::class)) {
        $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
    }
    return $text;
}

/** Product / location / container name: 1..$maxLength characters, no control characters. */
function validate_name(string $raw, int $maxLength): string
{
    $name = normalize_text($raw);
    if ($name === '') {
        throw new UserError('Please enter a name.');
    }
    if (preg_match('/^[^\p{C}]{1,' . $maxLength . '}$/uD', $name) !== 1) {
        throw new UserError('The name is invalid (maximum {max} characters, no control characters).', ['max' => $maxLength]);
    }
    return $name;
}

/** Positive database id, or null. */
function parse_id(mixed $raw): ?int
{
    if (!is_string($raw) || preg_match('/^[1-9]\d{0,9}$/D', $raw) !== 1 || (int) $raw > 4294967295) {
        return null;
    }
    return (int) $raw;
}

function require_id(string $raw): int
{
    return parse_id($raw) ?? throw new UserError('Invalid request.');
}

/** Number of packages: whole number 1..QUANTITY_MAX. */
function parse_quantity(string $raw): int
{
    $raw = trim($raw);
    if (preg_match('/^[1-9]\d{0,4}$/D', $raw) !== 1 || (int) $raw > QUANTITY_MAX) {
        throw new UserError('Enter a whole number of packages between 1 and {max}.', ['max' => QUANTITY_MAX]);
    }
    return (int) $raw;
}

/** Strict Y-m-d date (as sent by <input type="date">), returned as Y-m-d or null. */
function parse_date(string $raw, bool $required): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return $required ? throw new UserError('Please enter the storage date.') : null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
    if ($date === false || $date->format('Y-m-d') !== $raw || $raw < DATE_MIN || $raw > DATE_MAX) {
        throw new UserError('Please enter a valid date.');
    }
    return $raw;
}

function today(): string
{
    return (new DateTimeImmutable('today'))->format('Y-m-d');
}
