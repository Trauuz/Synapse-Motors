<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/services/AuthValidator.php';
require_once __DIR__ . '/../app/services/LoginRedirectResolver.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/config/env.php';
require_once __DIR__ . '/../app/services/SupabaseClient.php';
require_once __DIR__ . '/../app/services/AuthService.php';

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/ApplicationDevelopment/Synapse-Motors/public/auth/submit.php';

$validator = new AuthValidator();

$validRegistration = $validator->validateRegistration([
    'name' => 'Alyssa P. Hacker',
    'email' => 'alyssa@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'complete_address' => '123 Main Street, Sample City',
    'contact_number' => '+63 917 123 4567',
]);

if ($validRegistration !== []) {
    fwrite(STDERR, "Expected valid registration payload to pass validation.\n");
    exit(1);
}

$invalidRegistration = $validator->validateRegistration([
    'name' => '',
    'email' => 'not-an-email',
    'password' => 'short',
    'password_confirmation' => 'different',
    'complete_address' => '',
    'contact_number' => '',
]);

$requiredRegistrationErrors = [
    'name',
    'email',
    'password',
    'password_confirmation',
    'complete_address',
    'contact_number',
];

foreach ($requiredRegistrationErrors as $field) {
    if (array_key_exists($field, $invalidRegistration)) {
        continue;
    }

    fwrite(STDERR, "Expected registration error for {$field}.\n");
    exit(1);
}

$invalidSignIn = $validator->validateSignIn([
    'email' => 'wrong',
    'password' => '',
]);

if (!array_key_exists('email', $invalidSignIn) || !array_key_exists('password', $invalidSignIn)) {
    fwrite(STDERR, "Expected sign-in validation to require a valid email and password.\n");
    exit(1);
}

$resolver = new LoginRedirectResolver();

if ($resolver->forRole('Admin') !== 'admin/dashboard.php') {
    fwrite(STDERR, "Expected admin users to be redirected to the admin dashboard.\n");
    exit(1);
}

if ($resolver->forRole('Buyer') !== 'index.php') {
    fwrite(STDERR, "Expected buyer users to be redirected to the landing page.\n");
    exit(1);
}

$supabaseStub = new class extends SupabaseClient {
    public function postAuth(string $path, ?array $payload = null): array
    {
        return [
            'ok' => false,
            'status' => 400,
            'data' => null,
            'error' => 'Invalid login credentials',
        ];
    }
};

$authService = new AuthService($supabaseStub);
$failedLogin = $authService->signIn([
    'email' => 'missing@example.com',
    'password' => 'Password123',
]);

if (($failedLogin['errors']['form'] ?? null) !== 'Invalid login or password. Remember that password is case-sensitive.') {
    fwrite(STDERR, "Expected invalid login credentials to map to the requested user-facing message.\n");
    exit(1);
}

$registeringSupabaseStub = new class extends SupabaseClient {
    public function postAuth(string $path, ?array $payload = null): array
    {
        if ($path !== 'signup') {
            return [
                'ok' => false,
                'status' => 400,
                'data' => null,
                'error' => 'Unexpected auth path.',
            ];
        }

        if (($payload['email_redirect_to'] ?? null) !== 'http://localhost/ApplicationDevelopment/Synapse-Motors/public/auth-callback.php') {
            return [
                'ok' => false,
                'status' => 400,
                'data' => null,
                'error' => 'Signup did not use the dedicated auth callback page.',
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'email' => $payload['email'] ?? null,
            ],
            'error' => null,
        ];
    }

    public function postRest(string $table, array $payload): array
    {
        if (($payload['auth_user_id'] ?? null) !== '123e4567-e89b-12d3-a456-426614174000') {
            return [
                'ok' => false,
                'status' => 400,
                'data' => null,
                'error' => 'Registration used an unexpected auth user ID.',
            ];
        }

        return [
            'ok' => true,
            'status' => 201,
            'data' => [$payload],
            'error' => null,
        ];
    }
};

$registrationService = new AuthService($registeringSupabaseStub);
$registrationResult = $registrationService->register([
    'name' => 'Alyssa P. Hacker',
    'email' => 'alyssa@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'complete_address' => '123 Main Street, Sample City',
    'contact_number' => '+63 917 123 4567',
]);

if ($registrationResult['ok'] !== true) {
    fwrite(STDERR, "Expected signup responses with a top-level user id to initialize the profile successfully.\n");
    exit(1);
}

$rateLimitedSupabaseStub = new class extends SupabaseClient {
    public function postAuth(string $path, ?array $payload = null): array
    {
        return [
            'ok' => false,
            'status' => 429,
            'data' => null,
            'error' => 'Email rate limit exceeded',
        ];
    }
};

$rateLimitedRegistrationService = new AuthService($rateLimitedSupabaseStub);
$rateLimitedRegistration = $rateLimitedRegistrationService->register([
    'name' => 'Alyssa P. Hacker',
    'email' => 'alyssa@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'complete_address' => '123 Main Street, Sample City',
    'contact_number' => '+63 917 123 4567',
]);

if (($rateLimitedRegistration['errors']['form'] ?? null) !== 'Too many signup attempts right now. Please wait a few minutes and try again.') {
    fwrite(STDERR, "Expected signup rate limit errors to map to the friendlier message.\n");
    exit(1);
}

$duplicateEmailProfileStub = new class extends SupabaseClient {
    public function postAuth(string $path, ?array $payload = null): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => [
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'email' => $payload['email'] ?? null,
            ],
            'error' => null,
        ];
    }

    public function postRest(string $table, array $payload): array
    {
        return [
            'ok' => false,
            'status' => 409,
            'data' => null,
            'error' => 'duplicate key value violates unique constraint "users_email_key"',
        ];
    }
};

$duplicateEmailProfileService = new AuthService($duplicateEmailProfileStub);
$duplicateEmailRegistration = $duplicateEmailProfileService->register([
    'name' => 'Alyssa P. Hacker',
    'email' => 'alyssa@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'complete_address' => '123 Main Street, Sample City',
    'contact_number' => '+63 917 123 4567',
]);

if (($duplicateEmailRegistration['errors']['form'] ?? null) !== 'That email is already registered. Please sign in or check your email confirmation link.') {
    fwrite(STDERR, "Expected duplicate profile email errors to map to the friendlier message.\n");
    exit(1);
}

echo "Auth backend contract passed.\n";
