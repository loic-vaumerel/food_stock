<?php

declare(strict_types=1);

/**
 * CLI helper to create the password hash of a new user.
 * The password is read from STDIN (never from argv, which leaks into
 * shell history and the process list):
 *
 *   read -rs P && printf '%s' "$P" | php app/bin/hash_password.php; unset P
 *
 * Then: INSERT INTO food_stock_users (email, password_hash) VALUES ('me@example.com', '<hash>');
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function read_password(): string
{
    $password = rtrim((string) stream_get_contents(STDIN), "\r\n");
    if ($password === '' || strlen($password) > 1024) {
        fwrite(STDERR, "Password must be between 1 and 1024 characters.\n");
        exit(1);
    }
    return $password;
}

$algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
echo password_hash(read_password(), $algo), PHP_EOL;
