<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Lazily opened, shared PDO connection with real (non-emulated) prepared statements. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = db_connect();
    }
    return $pdo;
}

function db_connect(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        env('DB_HOST'),
        (int) env('DB_PORT'),
        env('DB_NAME')
    );
    return new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
}

/**
 * The only way the application talks to the database: SQL text is always
 * a constant, every value goes through a bound parameter.
 *
 * @param list<mixed> $params
 */
function db_run(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

/** @param list<mixed> $params */
function db_fetch_one(string $sql, array $params = []): ?array
{
    $row = db_run($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Runs $work atomically; any exception rolls everything back and is rethrown. */
function db_transaction(callable $work): mixed
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $result = $work();
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/** MariaDB native error number (1062 duplicate key, 1451/1452 foreign key...). */
function pdo_error_code(PDOException $e): ?int
{
    $code = $e->errorInfo[1] ?? null;
    return is_numeric($code) ? (int) $code : null;
}

/**
 * Write that must respect a UNIQUE index: a duplicate becomes a user-facing error.
 *
 * @param list<mixed> $params
 */
function db_run_unique(string $sql, array $params, string $duplicateMessage): void
{
    try {
        db_run($sql, $params);
    } catch (PDOException $e) {
        if (pdo_error_code($e) === 1062) {
            throw new UserError($duplicateMessage);
        }
        throw $e;
    }
}
