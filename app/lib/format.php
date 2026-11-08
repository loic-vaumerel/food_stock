<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Machine form, dot decimal: 1500 -> "1.5"; 700000 -> "700". */
function format_milli(int $milli): string
{
    return rtrim(rtrim(milli_to_db_amount($milli), '0'), '.');
}

/** Display form, decimal separator of the current language: "1.5" or "1,5". */
function format_number(int $milli): string
{
    return str_replace('.', decimal_separator(), format_milli($milli));
}

function unit_label(string $unit, int $milli = 0): string
{
    if ($unit !== 'pieces') {
        return $unit;
    }
    return $milli === 1000 ? t('piece') : t('pieces');
}

/** "1.5 kg", "3 pieces" */
function format_amount(int $milli, string $unit): string
{
    return format_number($milli) . ' ' . unit_label($unit, $milli);
}

/** "bag of 1 kg" / "bag de 1 kg" (container names are user data, never translated) */
function packaging_label(string $container, int $milli, string $unit): string
{
    return t('{container} of {amount}', ['container' => $container, 'amount' => format_amount($milli, $unit)]);
}

function format_date(?string $ymd): string
{
    if ($ymd === null || $ymd === '') {
        return '';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd);
    if ($date === false) {
        return $ymd;
    }
    return t('{month} {day}, {year}', [
        'month' => t($date->format('M')),
        'day' => $date->format('j'),
        'year' => $date->format('Y'),
    ]);
}

function status_label(string $status): string
{
    return $status === 'expired' ? t('Expired') : t('Expires soon');
}

/** 'expired', 'soon' (within EXPIRY_WARNING_DAYS) or '' */
function expiry_status(?string $expiryDate): string
{
    if ($expiryDate === null) {
        return '';
    }
    if ($expiryDate < today()) {
        return 'expired';
    }
    $limit = (new DateTimeImmutable('today'))->modify('+' . expiry_warning_days() . ' days');
    return $expiryDate <= $limit->format('Y-m-d') ? 'soon' : '';
}
