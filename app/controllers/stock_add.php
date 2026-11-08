<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** Form fields and their maximum raw length in bytes. */
const ADD_FORM_FIELDS = [
    'product' => 1000,
    'location' => 10,
    'container' => 10,
    'amount' => 20,
    'unit' => 10,
    'quantity' => 10,
    'storage_date' => 10,
    'expiry_date' => 10,
];

function run_stock_add_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $form = add_form_defaults();
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $form = add_form_from_post();
        $error = handle_add_stock($form);
    }
    render('stock_add', add_page_vars($form, $error));
}

/** Pre-filled from ?product=<id>&location=<id> ("add another packaging" links). */
function add_form_defaults(): array
{
    $productId = parse_id($_GET['product'] ?? null);
    $locationId = parse_id($_GET['location'] ?? null);
    return array_replace(array_fill_keys(array_keys(ADD_FORM_FIELDS), ''), [
        'product' => $productId === null ? '' : (find_product_name($productId) ?? ''),
        'location' => $locationId === null ? '' : (string) $locationId,
        'unit' => 'g',
        'quantity' => '1',
        'storage_date' => today(),
    ]);
}

function add_form_from_post(): array
{
    $form = [];
    foreach (ADD_FORM_FIELDS as $field => $maxLength) {
        $form[$field] = post_string($field, $maxLength);
    }
    return $form;
}

/** Returns an error message, or redirects to the home page on success. */
function handle_add_stock(array $form): string
{
    $error = run_user_action(fn () => add_stock(validate_add_form($form)));
    if ($error !== null) {
        return $error;
    }
    flash_set('Food added to stock.');
    redirect('index.php');
}

function validate_add_form(array $form): array
{
    $unit = parse_unit($form['unit']);
    return [
        'product_name' => validate_name($form['product'], NAME_MAX_LENGTH),
        'location_id' => require_location_id($form['location']),
        'container_id' => require_container_id($form['container']),
        'milli' => parse_amount_input($form['amount'], $unit),
        'unit' => $unit,
        'quantity' => parse_quantity($form['quantity']),
        'storage_date' => parse_date($form['storage_date'], true),
        'expiry_date' => parse_date($form['expiry_date'], false),
    ];
}

function add_page_vars(array $form, ?string $error): array
{
    return [
        'title' => 'Add food',
        'page' => 'add',
        'form' => $form,
        'error' => $error,
        'locations' => all_locations(),
        'containers' => all_containers(),
        'product_names' => product_names(),
    ];
}
