<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Low-level stock entry storage. Every write of a packaging goes through
 * upsert_stock_entry(), which normalises the amount and merges with an
 * identical entry (same product, location, packaging, storage and expiry date)
 * thanks to the UNIQUE key uq_food_stock_entries_identity.
 */

/** Locks the row until the end of the current transaction. */
function lock_stock_entry(int $id): array
{
    $row = db_fetch_one(
        'SELECT id, product_id, location_id, container_id, amount, unit, quantity, storage_date, expiry_date
           FROM food_stock_entries WHERE id = ? FOR UPDATE',
        [$id]
    );
    if ($row === null) {
        throw new UserError('This stock entry no longer exists.');
    }
    return hydrate_stock_entry($row);
}

function hydrate_stock_entry(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'product_id' => (int) $row['product_id'],
        'location_id' => (int) $row['location_id'],
        'container_id' => (int) $row['container_id'],
        'milli' => db_amount_to_milli((string) $row['amount']),
        'unit' => (string) $row['unit'],
        'quantity' => (int) $row['quantity'],
        'storage_date' => (string) $row['storage_date'],
        'expiry_date' => $row['expiry_date'] === null ? null : (string) $row['expiry_date'],
    ];
}

/** Insert, or add the quantity to the identical existing entry. */
function upsert_stock_entry(array $entry): void
{
    [$milli, $unit] = normalize_amount($entry['milli'], $entry['unit']);
    db_run(
        'INSERT INTO food_stock_entries
                (product_id, location_id, container_id, amount, unit, quantity, storage_date, expiry_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)',
        [
            $entry['product_id'], $entry['location_id'], $entry['container_id'],
            milli_to_db_amount($milli), $unit, $entry['quantity'],
            $entry['storage_date'], $entry['expiry_date'],
        ]
    );
}

/** Quantity 0 deletes the entry. */
function set_stock_quantity(int $id, int $quantity): void
{
    if ($quantity <= 0) {
        delete_stock_entry($id);
        return;
    }
    db_run('UPDATE food_stock_entries SET quantity = ? WHERE id = ?', [$quantity, $id]);
}

function delete_stock_entry(int $id): void
{
    db_run('DELETE FROM food_stock_entries WHERE id = ?', [$id]);
}
