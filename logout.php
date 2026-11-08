<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

// POST + CSRF token only: a link or image on another site cannot log the user out.
require_method(['POST']);
verify_post_request();
logout();
redirect('login.php');
