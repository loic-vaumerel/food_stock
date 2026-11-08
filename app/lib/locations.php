<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const NAME_MAX_LENGTH = 100;
const LOCATION_EXISTS = 'A location with this name already exists.';

/** All locations, sorted by name, with their number of stock entries. */
function all_locations(): array
{
    return db_run(
        'SELECT l.id, l.name, COUNT(s.id) AS entry_count
           FROM food_stock_locations l
           LEFT JOIN food_stock_entries s ON s.location_id = l.id
          GROUP BY l.id, l.name
          ORDER BY l.name, l.id'
    )->fetchAll();
}

function find_location(int $id): ?array
{
    return db_fetch_one('SELECT id, name FROM food_stock_locations WHERE id = ?', [$id]);
}

/** Id of an existing location chosen in a form. */
function require_location_id(string $raw): int
{
    $id = parse_id($raw);
    if ($id === null || find_location($id) === null) {
        throw new UserError('Please choose a location.');
    }
    return $id;
}

function create_location(string $name): void
{
    db_run_unique('INSERT INTO food_stock_locations (name) VALUES (?)', [$name], LOCATION_EXISTS);
}

function rename_location(int $id, string $name): void
{
    if (find_location($id) === null) {
        throw new UserError('This location no longer exists.');
    }
    db_run_unique('UPDATE food_stock_locations SET name = ? WHERE id = ?', [$name, $id], LOCATION_EXISTS);
}

/** The RESTRICT foreign key makes "only when empty" atomic, even with concurrent users. */
function delete_location(int $id): void
{
    try {
        $deleted = db_run('DELETE FROM food_stock_locations WHERE id = ?', [$id])->rowCount();
    } catch (PDOException $e) {
        if (pdo_error_code($e) === 1451) {
            throw new UserError('This location still contains stock entries and cannot be deleted.');
        }
        throw $e;
    }
    if ($deleted === 0) {
        throw new UserError('This location no longer exists.');
    }
}
