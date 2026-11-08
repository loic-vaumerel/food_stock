<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const LOCATION_FLASH = [
    'add' => 'Location added.',
    'rename' => 'Location renamed.',
    'delete' => 'Location deleted.',
];

function run_locations_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_locations_post();
    }
    render('locations', [
        'title' => 'Locations',
        'page' => 'locations',
        'locations' => all_locations(),
        'error' => $error,
    ]);
}

/** Returns an error message, or redirects back to the list on success. */
function handle_locations_post(): string
{
    $action = post_string('action', 10);
    $error = run_user_action(fn () => match ($action) {
        'add' => create_location(posted_location_name()),
        'rename' => rename_location(require_id(post_string('id', 10)), posted_location_name()),
        'delete' => delete_location(require_id(post_string('id', 10))),
        default => throw new UserError('Invalid request.'),
    });
    if ($error !== null) {
        return $error;
    }
    flash_set(LOCATION_FLASH[$action]);
    redirect('locations.php');
}

function posted_location_name(): string
{
    return validate_name(post_string('name', 1000), NAME_MAX_LENGTH);
}
