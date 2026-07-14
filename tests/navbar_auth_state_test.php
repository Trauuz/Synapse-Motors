<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/view.php';

app_start_session();

$_SERVER['SCRIPT_NAME'] = '/index.php';

/**
 * @param array<string, mixed>|null $user
 */
function render_navbar_for(?array $user): string
{
    $_SESSION = [];

    if ($user !== null) {
        sign_in_user($user);
    }

    ob_start();
    require __DIR__ . '/../public/includes/navbar.php';

    return (string) ob_get_clean();
}

$guestNavbar = render_navbar_for(null);

if (!str_contains($guestNavbar, 'data-auth-trigger="signin"') || !str_contains($guestNavbar, '>Sign In</a>')) {
    fwrite(STDERR, "Expected guests to see the sign-in action in the navbar.\n");
    exit(1);
}

if (
    str_contains($guestNavbar, 'auth/logout.php')
    || str_contains($guestNavbar, '>Logout</button>')
    || str_contains($guestNavbar, '>Sign Out</button>')
) {
    fwrite(STDERR, "Expected guests not to see the logout action in the navbar.\n");
    exit(1);
}

$loggedInNavbar = render_navbar_for([
    'id' => 42,
    'auth_user_id' => 'user-42',
    'email' => 'driver@example.com',
    'name' => 'Test Driver',
    'role' => 'Buyer',
]);

if (!str_contains($loggedInNavbar, 'action="auth/logout.php"')) {
    fwrite(STDERR, "Expected signed-in users to get a logout form in the navbar.\n");
    exit(1);
}

if (!str_contains($loggedInNavbar, '>Logout</button>') && !str_contains($loggedInNavbar, '>Sign Out</button>')) {
    fwrite(STDERR, "Expected signed-in users to see a sign-out button in the navbar.\n");
    exit(1);
}

if (str_contains($loggedInNavbar, 'data-auth-trigger="signin"') || str_contains($loggedInNavbar, '>Sign In</a>')) {
    fwrite(STDERR, "Expected signed-in users not to see the sign-in action in the navbar.\n");
    exit(1);
}

echo "Navbar auth state contract passed.\n";
