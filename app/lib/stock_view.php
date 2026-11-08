<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const STOCK_VIEW_SELECT = '
    SELECT s.id, s.product_id, s.location_id, s.container_id, s.amount, s.unit, s.quantity,
           s.storage_date, s.expiry_date,
           p.name AS product_name, l.name AS location_name,
           c.name AS container_name
      FROM food_stock_entries s
      JOIN food_stock_products p ON p.id = s.product_id
      JOIN food_stock_locations l ON l.id = s.location_id
      JOIN food_stock_containers c ON c.id = s.container_id';

/**
 * Home page list. Search is a substring match on the product name; the column's
 * utf8mb4_unicode_ci collation makes it case- and accent-insensitive.
 */
function stock_rows(string $search, ?int $locationId): array
{
    $pattern = $search === '' ? null : '%' . escape_like($search) . '%';
    return db_run(
        STOCK_VIEW_SELECT . "
         WHERE (? IS NULL OR p.name LIKE ? ESCAPE '!')
           AND (? IS NULL OR s.location_id = ?)
         ORDER BY l.name, l.id, p.name, s.expiry_key, s.storage_date, s.id",
        [$pattern, $pattern, $locationId, $locationId]
    )->fetchAll();
}

/** Makes %, _ and the escape character itself literal in a LIKE pattern. */
function escape_like(string $value): string
{
    return strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']);
}

/** @return list<array{id: int, name: string, entries: list<array>}> */
function stock_groups(string $search, ?int $locationId): array
{
    $groups = [];
    foreach (stock_rows($search, $locationId) as $row) {
        $id = (int) $row['location_id'];
        $groups[$id] ??= ['id' => $id, 'name' => (string) $row['location_name'], 'entries' => []];
        $groups[$id]['entries'][] = decorate_stock_row($row);
    }
    return array_values($groups);
}

function find_stock_entry_view(int $id): ?array
{
    $row = db_fetch_one(STOCK_VIEW_SELECT . ' WHERE s.id = ?', [$id]);
    return $row === null ? null : decorate_stock_row($row);
}

/** Adds display-ready (not yet escaped) fields. */
function decorate_stock_row(array $row): array
{
    $entry = hydrate_stock_entry($row);
    return $entry + [
        'product_name' => (string) $row['product_name'],
        'location_name' => (string) $row['location_name'],
        'packaging' => packaging_label((string) $row['container_name'], $entry['milli'], $entry['unit']),
        'storage_label' => format_date($entry['storage_date']),
        'expiry_label' => format_date($entry['expiry_date']),
        'status' => expiry_status($entry['expiry_date']),
    ];
}
