<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

// POST + CSRF token: another site cannot change a user's saved language.
require_method(['POST']);
verify_post_request();
switch_language(post_string('lang', 2));
redirect(language_return_path(post_string('page', 20), post_string('entry', 10)));
