<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Never display errors to the client; log them server-side only.
 * Exception arguments are stripped from traces so passwords never reach the logs.
 */
function configure_errors(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('zend.exception_ignore_args', '1');
    set_exception_handler('handle_uncaught_exception');
}

function handle_uncaught_exception(Throwable $e): void
{
    error_log(sprintf(
        '[food_stock] %s: %s in %s:%d%s%s',
        $e::class,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        PHP_EOL,
        $e->getTraceAsString()
    ));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo 'An internal error occurred. Please try again later.';
}
