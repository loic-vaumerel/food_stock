<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** @return list<string> */
function product_names(): array
{
    return db_run('SELECT name FROM food_stock_products ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
}

function find_product_name(int $id): ?string
{
    $row = db_fetch_one('SELECT name FROM food_stock_products WHERE id = ?', [$id]);
    return $row === null ? null : (string) $row['name'];
}

/** Case-insensitive, accent-sensitive lookup through the name_key column. */
function find_product_id(string $name): ?int
{
    $row = db_fetch_one('SELECT id FROM food_stock_products WHERE name_key = LOWER(?)', [$name]);
    return $row === null ? null : (int) $row['id'];
}

/**
 * Typing a new name creates the product. Runs outside any transaction (autocommit)
 * so that, if another user creates the same name at the same time, the duplicate
 * key error is caught and the other user's row is found.
 */
function find_or_create_product(string $name): int
{
    $id = find_product_id($name);
    if ($id !== null) {
        return $id;
    }
    try {
        db_run('INSERT INTO food_stock_products (name) VALUES (?)', [$name]);
        return (int) db()->lastInsertId();
    } catch (PDOException $e) {
        if (pdo_error_code($e) !== 1062) {
            throw $e;
        }
    }
    return find_product_id($name) ?? throw new RuntimeException('Product could not be created.');
}
