<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$controller = new AuthController(
    new AuthService(
        new SupabaseClient(),
        new AuthValidator(),
        new LoginRedirectResolver(),
    )
);

$controller->handle();
