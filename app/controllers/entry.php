<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const ENTRY_FLASH = [
    'edit' => 'Stock entry updated.',
];

/** Stock entry page: change packaging/quantity. Removal lives on the remove page. */
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
    ]);
}

/** Returns an error message, or redirects to the home page on success. */
function handle_entry_post(int $id): string
{
    $action = post_string('action', 20);
    $error = run_user_action(fn () => match ($action) {
        'edit' => entry_action_edit($id),
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
    edit_stock_entry($id, [
        'product_name' => validate_name(post_string('name', 1000), NAME_MAX_LENGTH),
        'container_id' => require_container_id(post_string('container', 10)),
        'milli' => parse_amount_input(post_string('amount', 20), $unit),
        'unit' => $unit,
        'quantity' => parse_quantity(post_string('quantity', 10)),
        'storage_date' => parse_date(post_string('storage_date', 10), true),
        'expiry_date' => parse_date(post_string('expiry_date', 10), false),
    ]);
}
