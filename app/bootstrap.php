<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/vehicles.php';
require_once __DIR__ . '/helpers/cart.php';
require_once __DIR__ . '/helpers/checkout.php';
require_once __DIR__ . '/helpers/admin_portal.php';
require_once __DIR__ . '/helpers/view.php';
require_once __DIR__ . '/services/AuthValidator.php';
require_once __DIR__ . '/services/LoginRedirectResolver.php';
require_once __DIR__ . '/services/SupabaseClient.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/controllers/AuthController.php';

app_start_session();
