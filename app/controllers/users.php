<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

const USER_FLASH = [
    'add' => 'User added.',
    'update' => 'User updated.',
    'deactivate' => 'User deactivated.',
    'activate' => 'User reactivated.',
    'delete' => 'User deleted.',
];

function run_users_page(): void
{
    require_method(['GET', 'HEAD', 'POST']);
    require_login();
    $error = null;
    if (request_method() === 'POST') {
        verify_post_request();
        $error = handle_users_post();
    }
    render('users', [
        'title' => 'Users',
        'page' => 'users',
        'users' => all_users(),
        'error' => $error,
    ]);
}

/** Returns an error message, or redirects back to the list on success. */
function handle_users_post(): string
{
    $action = post_string('action', 12);
    $error = run_user_action(fn () => match ($action) {
        'add' => create_user(posted_user_email(), posted_user_password(true)),
        'update' => update_user(require_user_id(post_string('id', 10)), posted_user_email(), posted_user_password(false)),
        'deactivate' => set_user_active(require_user_id(post_string('id', 10)), false),
        'activate' => set_user_active(require_user_id(post_string('id', 10)), true),
        'delete' => delete_user(require_user_id(post_string('id', 10))),
        default => throw new UserError('Invalid request.'),
    });
    if ($error !== null) {
        return $error;
    }
    flash_set(USER_FLASH[$action]);
    redirect('users.php');
}
