<?php

declare(strict_types=1);

final class LoginRedirectResolver
{
    public function forRole(string $role): string
    {
        if (strcasecmp($role, 'Admin') === 0) {
            return 'admin/dashboard.php';
        }

        return 'index.php';
    }
}
