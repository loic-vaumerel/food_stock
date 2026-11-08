<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const ENTRY_FLASH = [
    'edit' => 'Stock entry updated.',
    'remove_packages' => 'Packages removed.',
    'remove_amount' => 'Amount removed.',
    'move' => 'Packages moved.',
];

/** Stock entry page: change packaging/quantity, remove packages or an amount, move. */
function run_entry_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $id = parse_id($_GET['id'] ?? null) ?? abort(404, 'This stock entry no longer exists.');
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_entry_post($id);
    }
    $entry = find_stock_entry_view($id) ?? abort(404, 'This stock entry no longer exists.');
    render('entry', [
        'title' => 'Stock entry',
        'entry' => $entry,
        'error' => $error,
        'containers' => all_containers(),
        'locations' => all_locations(),
    ]);
}

/** Returns an error message, or redirects to the home page on success. */
function handle_entry_post(int $id): string
{
    $action = post_string('action', 20);
    $error = run_user_action(fn () => match ($action) {
        'edit' => entry_action_edit($id),
        'remove_packages' => remove_packages($id, parse_quantity(post_string('count', 10))),
        'remove_amount' => remove_amount($id, post_string('amount', 20)),
        'move' => entry_action_move($id),
        default => throw new UserError('Invalid request.'),
    });
    if ($error !== null) {
        return $error;
    }
    flash_set(ENTRY_FLASH[$action]);
    redirect('index.php');
}

function entry_action_edit(int $id): void
{
    $unit = parse_unit(post_string('unit', 10));
    edit_stock_entry(
        $id,
        require_container_id(post_string('container', 10)),
        parse_amount_input(post_string('amount', 20), $unit),
        $unit,
        parse_quantity(post_string('quantity', 10))
    );
}

function entry_action_move(int $id): void
{
    move_packages(
        $id,
        require_location_id(post_string('location', 10)),
        parse_quantity(post_string('count', 10))
    );
}
