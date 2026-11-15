<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Business operations on stock. Each one is a single transaction that locks
 * the stock entry first, so two users acting on the same entry cannot
 * remove or move the same packages twice.
 */

/** @param array{product_name: string, location_id: int, container_id: int, milli: int, unit: string, quantity: int, storage_date: string, expiry_date: ?string} $input */
function add_stock(array $input): void
{
    $productId = find_or_create_product($input['product_name']);
    upsert_stock_entry(['product_id' => $productId] + $input);
}

/**
 * Change the product name (shared by all its entries), packaging, quantity and dates.
 * Merges into an identical entry if there is one. A duplicate product name rolls everything back.
 *
 * @param array{product_name: string, container_id: int, milli: int, unit: string, quantity: int, storage_date: string, expiry_date: ?string} $changes
 */
function edit_stock_entry(int $id, array $changes): void
{
    db_transaction(function () use ($id, $changes): void {
        $entry = lock_stock_entry($id);
        rename_product($entry['product_id'], $changes['product_name']);
        delete_stock_entry($id);
        upsert_stock_entry(array_replace($entry, [
            'container_id' => $changes['container_id'],
            'milli' => $changes['milli'],
            'unit' => $changes['unit'],
            'quantity' => $changes['quantity'],
            'storage_date' => $changes['storage_date'],
            'expiry_date' => $changes['expiry_date'],
        ]));
    });
}

function remove_packages(int $id, int $count): void
{
    db_transaction(function () use ($id, $count): void {
        $entry = lock_stock_entry($id);
        assert_enough_packages($entry, $count);
        set_stock_quantity($id, $entry['quantity'] - $count);
    });
}

/** Remove an amount (in the packaging's unit): whole packages first, then open one. */
function remove_amount(int $id, string $rawAmount): void
{
    db_transaction(function () use ($id, $rawAmount): void {
        $entry = lock_stock_entry($id);
        $remove = parse_amount_input($rawAmount, $entry['unit']);
        assert_enough_amount($entry, $remove);
        $plan = plan_partial_removal($entry['milli'], $remove);
        set_stock_quantity($id, $entry['quantity'] - $plan['consumed']);
        if ($plan['remainder'] > 0) {
            upsert_stock_entry(array_replace($entry, ['milli' => $plan['remainder'], 'quantity' => 1]));
        }
    });
}

function move_packages(int $id, int $locationId, int $count): void
{
    db_transaction(function () use ($id, $locationId, $count): void {
        $entry = lock_stock_entry($id);
        if ($entry['location_id'] === $locationId) {
            throw new UserError('Please choose a different location.');
        }
        assert_enough_packages($entry, $count);
        set_stock_quantity($id, $entry['quantity'] - $count);
        upsert_stock_entry(array_replace($entry, ['location_id' => $locationId, 'quantity' => $count]));
    });
}

function assert_enough_packages(array $entry, int $count): void
{
    if ($count > $entry['quantity']) {
        throw new UserError('Only {count} package(s) available.', ['count' => $entry['quantity']]);
    }
}

function assert_enough_amount(array $entry, int $removeMilli): void
{
    $total = $entry['milli'] * $entry['quantity'];
    if ($removeMilli > $total) {
        throw new UserError('You cannot remove more than the {amount} available.', [
            'amount' => format_amount($total, $entry['unit']),
        ]);
    }
}
