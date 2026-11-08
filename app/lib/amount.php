<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Packaging amounts are handled as integers in thousandths of the unit
 * ("milli"): 1.5 kg = 1500, 700 g = 700000. No floating point anywhere,
 * DB column is DECIMAL(10,3).
 */
const UNITS = ['g', 'kg', 'ml', 'l', 'pieces'];
const DECIMAL_UNITS = ['kg', 'l'];           // up to 3 decimals, others whole numbers
const CONVERT_DOWN = ['kg' => 'g', 'l' => 'ml']; // applied below 1 kg / 1 l, never up

function parse_unit(string $raw): string
{
    if (!in_array($raw, UNITS, true)) {
        throw new UserError('Please choose a unit.');
    }
    return $raw;
}

/** User input ("1.5" or "1,5" for kg/l, "300" for g/ml/pieces) -> milli, > 0. */
function parse_amount_input(string $raw, string $unit): int
{
    $isDecimal = in_array($unit, DECIMAL_UNITS, true);
    $pattern = $isDecimal ? '/^(\d{1,7})(?:\.(\d{1,3}))?$/D' : '/^(\d{1,7})$/D';
    if (preg_match($pattern, str_replace(',', '.', trim($raw)), $m) !== 1) {
        throw new UserError($isDecimal
            ? 'Enter a positive amount with up to 3 decimals.'
            : 'Enter a positive whole number.');
    }
    $milli = digits_to_milli($m[1], $m[2] ?? '');
    if ($milli <= 0) {
        throw new UserError('The amount must be greater than zero.');
    }
    return $milli;
}

function digits_to_milli(string $integer, string $fraction): int
{
    return (int) $integer * 1000 + (int) str_pad($fraction, 3, '0');
}

/** DECIMAL(10,3) string from the database -> milli. */
function db_amount_to_milli(string $value): int
{
    if (preg_match('/^(\d{1,7})(?:\.(\d{1,3}))?$/D', $value, $m) !== 1) {
        throw new UnexpectedValueException('Invalid amount in database.');
    }
    return digits_to_milli($m[1], $m[2] ?? '');
}

function milli_to_db_amount(int $milli): string
{
    return sprintf('%d.%03d', intdiv($milli, 1000), $milli % 1000);
}

/**
 * Canonical form used for every write: below 1 kg / 1 l, convert down to g / ml
 * (0.7 kg -> 700 g). Never convert up (1000 g stays 1000 g).
 *
 * @return array{0: int, 1: string} [milli, unit]
 */
function normalize_amount(int $milli, string $unit): array
{
    if (isset(CONVERT_DOWN[$unit]) && $milli < 1000) {
        return [$milli * 1000, CONVERT_DOWN[$unit]];
    }
    return [$milli, $unit];
}

/**
 * Partial removal plan: whole packages first, then open one package.
 * Example: 2 x 1 kg, remove 1.5 kg -> consumed 2, remainder 0.5 kg (one opened package).
 *
 * @return array{consumed: int, remainder: int} remainder = milli left in the opened package
 */
function plan_partial_removal(int $packageMilli, int $removeMilli): array
{
    $rest = $removeMilli % $packageMilli;
    return [
        'consumed' => intdiv($removeMilli, $packageMilli) + ($rest > 0 ? 1 : 0),
        'remainder' => $rest > 0 ? $packageMilli - $rest : 0,
    ];
}
