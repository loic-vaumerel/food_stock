<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** One-shot message shown on the page displayed after a redirect (Post/Redirect/Get). */
function flash_set(string $message): void
{
    $_SESSION['flash'] = $message;
}

function flash_take(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_string($message) ? $message : null;
}
