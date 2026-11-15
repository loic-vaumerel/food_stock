<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const REMOVE_FLASH = [
    'remove_packages' => 'Packages removed.',
    'remove_amount' => 'Amount removed.',
];

/** Remove page: remove full packages or an amount of a stock entry. */
function run_remove_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $id = parse_id($_GET['id'] ?? null) ?? abort(404, 'This stock entry no longer exists.');
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_remove_post($id);
    }
    $entry = find_stock_entry_view($id) ?? abort(404, 'This stock entry no longer exists.');
    render('remove', [
        'title' => 'Remove',
        'page' => 'remove',
        'entry' => $entry,
        'error' => $error,
    ]);
}

/** Returns an error message, or redirects to the home page on success. */
function handle_remove_post(int $id): string
{
    $action = post_string('action', 20);
    $error = run_user_action(fn () => match ($action) {
        'remove_packages' => remove_packages($id, parse_quantity(post_string('count', 10))),
        'remove_amount' => remove_amount($id, post_string('amount', 20)),
        default => throw new UserError('Invalid request.'),
    });
    if ($error !== null) {
        return $error;
    }
    flash_set(REMOVE_FLASH[$action]);
    redirect('index.php');
}
