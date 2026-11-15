<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Move page: move some packages of a stock entry to another location. */
function run_move_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $id = parse_id($_GET['id'] ?? null) ?? abort(404, 'This stock entry no longer exists.');
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_move_post($id);
    }
    $entry = find_stock_entry_view($id) ?? abort(404, 'This stock entry no longer exists.');
    render('move', [
        'title' => 'Move',
        'page' => 'move',
        'entry' => $entry,
        'error' => $error,
        'locations' => all_locations(),
    ]);
}

/** Returns an error message, or redirects to the home page on success. */
function handle_move_post(int $id): string
{
    $error = run_user_action(fn () => move_packages(
        $id,
        require_location_id(post_string('location', 10)),
        parse_quantity(post_string('count', 10))
    ));
    if ($error !== null) {
        return $error;
    }
    flash_set('Packages moved.');
    redirect('index.php');
}
