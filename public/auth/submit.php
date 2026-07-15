<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$controller = new AuthController(
    new AuthService(
        user_repository(),
        new AuthValidator(),
        new LoginRedirectResolver(),
    )
);

$controller->handle();
