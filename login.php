<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

require_method(['GET', 'HEAD', 'POST']);
if (current_user() !== null) {
    redirect('index.php');
}

$state = ['email' => '', 'error' => null];
if (request_method() === 'POST') {
    verify_post_request();
    $state = handle_login_post();
}
render('login', ['title' => 'Sign in', 'page' => 'login'] + $state);
