<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const CONTAINER_FLASH = [
    'add' => 'Container type added.',
    'rename' => 'Container type renamed.',
    'delete' => 'Container type deleted.',
];

function run_containers_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_containers_post();
    }
    render('containers', [
        'title' => 'Containers',
        'page' => 'containers',
        'containers' => all_containers(),
        'error' => $error,
    ]);
}

/** Returns an error message, or redirects back to the list on success. */
function handle_containers_post(): string
{
    $action = post_string('action', 10);
    $error = run_user_action(fn () => match ($action) {
        'add' => create_container(posted_container_name()),
        'rename' => rename_container(require_id(post_string('id', 10)), posted_container_name()),
        'delete' => delete_container(require_id(post_string('id', 10))),
        default => throw new UserError('Invalid request.'),
    });
    if ($error !== null) {
        return $error;
    }
    flash_set(CONTAINER_FLASH[$action]);
    redirect('containers.php');
}

function posted_container_name(): string
{
    return validate_name(post_string('name', 1000), CONTAINER_NAME_MAX_LENGTH);
}
