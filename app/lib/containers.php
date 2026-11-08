<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const CONTAINER_NAME_MAX_LENGTH = 50;
const CONTAINER_EXISTS = 'This container type already exists.';
const CONTAINER_NOT_FOUND = 'This container type no longer exists.';

/** All container types (initial list: bag, box, pack, pot), sorted by name, with usage count. */
function all_containers(): array
{
    return db_run(
        'SELECT c.id, c.name, COUNT(s.id) AS entry_count
           FROM food_stock_containers c
           LEFT JOIN food_stock_entries s ON s.container_id = c.id
          GROUP BY c.id, c.name
          ORDER BY c.name, c.id'
    )->fetchAll();
}

function find_container(int $id): ?array
{
    return db_fetch_one('SELECT id, name FROM food_stock_containers WHERE id = ?', [$id]);
}

function require_container_id(string $raw): int
{
    $id = parse_id($raw);
    if ($id === null || find_container($id) === null) {
        throw new UserError('Please choose a container.');
    }
    return $id;
}

function create_container(string $name): void
{
    db_run_unique('INSERT INTO food_stock_containers (name) VALUES (?)', [$name], CONTAINER_EXISTS);
}

/** Renaming changes the packaging label of every stock entry using this container. */
function rename_container(int $id, string $name): void
{
    if (find_container($id) === null) {
        throw new UserError(CONTAINER_NOT_FOUND);
    }
    db_run_unique('UPDATE food_stock_containers SET name = ? WHERE id = ?', [$name, $id], CONTAINER_EXISTS);
}

/** The RESTRICT foreign key makes "only when unused" atomic, even with concurrent users. */
function delete_container(int $id): void
{
    try {
        $deleted = db_run('DELETE FROM food_stock_containers WHERE id = ?', [$id])->rowCount();
    } catch (PDOException $e) {
        if (pdo_error_code($e) === 1451) {
            throw new UserError('This container type is used by stock entries and cannot be deleted.');
        }
        throw $e;
    }
    if ($deleted === 0) {
        throw new UserError(CONTAINER_NOT_FOUND);
    }
}
