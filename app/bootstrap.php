<?php

declare(strict_types=1);

/**
 * Single entry point included by every public page.
 * Order matters: errors -> config -> HTTPS -> headers -> session.
 * Page-specific controllers (app/controllers/*.php) are required by each page.
 */

const FOOD_APP = true;
const APP_ROOT = __DIR__;

require APP_ROOT . '/lib/errors.php';
require APP_ROOT . '/lib/env.php';
require APP_ROOT . '/lib/http.php';
require APP_ROOT . '/lib/security.php';
require APP_ROOT . '/lib/view.php';
require APP_ROOT . '/lib/flash.php';
require APP_ROOT . '/lib/i18n.php';
require APP_ROOT . '/lib/validation.php';
require APP_ROOT . '/lib/db.php';
require APP_ROOT . '/lib/session.php';
require APP_ROOT . '/lib/csrf.php';
require APP_ROOT . '/lib/rate_limit.php';
require APP_ROOT . '/lib/remember.php';
require APP_ROOT . '/lib/auth.php';
require APP_ROOT . '/lib/amount.php';
require APP_ROOT . '/lib/format.php';
require APP_ROOT . '/lib/locations.php';
require APP_ROOT . '/lib/products.php';
require APP_ROOT . '/lib/containers.php';
require APP_ROOT . '/lib/stock.php';
require APP_ROOT . '/lib/stock_actions.php';
require APP_ROOT . '/lib/stock_view.php';

configure_errors();
load_env(APP_ROOT . '/.env');
validate_config();
configure_timezone();
require_https();
send_security_headers();
start_secure_session();
