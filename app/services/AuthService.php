<?php

declare(strict_types=1);

final class AuthService
{
    private SupabaseClient $supabaseClient;
    private AuthValidator $validator;
    private LoginRedirectResolver $redirectResolver;

    public function __construct(
        SupabaseClient $supabaseClient,
        ?AuthValidator $validator = null,
        ?LoginRedirectResolver $redirectResolver = null
    ) {
        $this->supabaseClient = $supabaseClient;
        $this->validator = $validator ?? new AuthValidator();
        $this->redirectResolver = $redirectResolver ?? new LoginRedirectResolver();
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok: bool, errors: array<string, string>, message: string|null}
     */
    public function register(array $input): array
    {
        $errors = $this->validator->validateRegistration($input);

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'message' => null,
            ];
        }

        $signupResponse = $this->supabaseClient->postAuth('signup', [
            'email' => trim((string) $input['email']),
            'password' => (string) $input['password'],
            'email_redirect_to' => app_public_url('auth-callback.php'),
            'data' => [
                'name' => trim((string) $input['name']),
                'role' => 'Buyer',
            ],
        ]);

        if (!$signupResponse['ok']) {
            return [
                'ok' => false,
                'errors' => ['form' => $this->resolveRegistrationErrorMessage($signupResponse['error'] ?? null)],
                'message' => null,
            ];
        }

        $userId = $this->extractSignupUserId($signupResponse['data'] ?? null);

        if (!is_string($userId) || $userId === '') {
            return [
                'ok' => false,
                'errors' => ['form' => 'Account created, but the user profile could not be initialized.'],
                'message' => null,
            ];
        }

        $profileResponse = $this->supabaseClient->postRest(supabase_users_table(), [
            'auth_user_id' => $userId,
            'name' => trim((string) $input['name']),
            'email' => trim((string) $input['email']),
            'role' => 'Buyer',
            'address' => trim((string) $input['complete_address']),
            'contact_no' => trim((string) $input['contact_number']),
        ]);

        if (!$profileResponse['ok']) {
            return [
                'ok' => false,
                'errors' => ['form' => $this->resolveProfileErrorMessage($profileResponse['error'] ?? null)],
                'message' => null,
            ];
        }

        return [
            'ok' => true,
            'errors' => [],
            'message' => 'Account created. Please check your email to confirm your registration.',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok: bool, errors: array<string, string>, message: string|null, user: array<string, mixed>|null, redirect: string}
     */
    public function signIn(array $input): array
    {
        $errors = $this->validator->validateSignIn($input);

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        $tokenResponse = $this->supabaseClient->postAuth('token?grant_type=password', [
            'email' => trim((string) $input['email']),
            'password' => (string) $input['password'],
        ]);

        if (!$tokenResponse['ok']) {
            return [
                'ok' => false,
                'errors' => ['form' => $this->resolveLoginErrorMessage($tokenResponse['error'])],
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        $authUser = $tokenResponse['data']['user'] ?? [];
        $authUserId = is_array($authUser) ? ($authUser['id'] ?? null) : null;

        if (!is_string($authUserId) || $authUserId === '') {
            return [
                'ok' => false,
                'errors' => ['form' => 'The authenticated account is missing an identifier.'],
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        $profileResponse = $this->supabaseClient->getRest(
            supabase_users_table(),
            'auth_user_id=eq.' . rawurlencode($authUserId) . '&select=*'
        );

        $profile = null;

        if ($profileResponse['ok'] && is_array($profileResponse['data']) && isset($profileResponse['data'][0]) && is_array($profileResponse['data'][0])) {
            $profile = $profileResponse['data'][0];
        }

        $role = $this->resolveRole($profile, $authUser);
        $user = [
            'id' => $profile['id'] ?? $authUserId,
            'auth_user_id' => $authUserId,
            'email' => $profile['email'] ?? $authUser['email'] ?? '',
            'name' => $profile['name'] ?? $authUser['user_metadata']['name'] ?? 'Synapse Member',
            'role' => $role,
        ];

        return [
            'ok' => true,
            'errors' => [],
            'message' => null,
            'user' => $user,
            'redirect' => $this->redirectResolver->forRole($role),
        ];
    }

    /**
     * @param array<string, mixed>|null $profile
     * @param array<string, mixed> $authUser
     */
    private function resolveRole(?array $profile, array $authUser): string
    {
        $profileRole = $profile['role'] ?? null;

        if (is_string($profileRole) && $profileRole !== '') {
            return $profileRole;
        }

        $appRole = $authUser['app_metadata']['role'] ?? null;

        if (is_string($appRole) && $appRole !== '') {
            return $appRole;
        }

        $userRole = $authUser['user_metadata']['role'] ?? null;

        if (is_string($userRole) && $userRole !== '') {
            return $userRole;
        }

        return 'Buyer';
    }

    private function resolveLoginErrorMessage(?string $error): string
    {
        $normalized = strtolower(trim((string) $error));

        if ($normalized === '') {
            return 'Invalid login or password. Remember that password is case-sensitive.';
        }

        $invalidCredentialErrors = [
            'invalid login credentials',
            'email not confirmed',
            'invalid email or password',
        ];

        if (in_array($normalized, $invalidCredentialErrors, true)) {
            return 'Invalid login or password. Remember that password is case-sensitive.';
        }

        return $error;
    }

    private function resolveRegistrationErrorMessage(?string $error): string
    {
        $normalized = strtolower(trim((string) $error));

        if (str_contains($normalized, 'email rate limit exceeded')) {
            return 'Too many signup attempts right now. Please wait a few minutes and try again.';
        }

        if ($normalized === '') {
            return 'Unable to create your account right now.';
        }

        return $error;
    }

    private function resolveProfileErrorMessage(?string $error): string
    {
        $normalized = strtolower(trim((string) $error));

        if (str_contains($normalized, 'duplicate key value violates unique constraint "users_email_key"')) {
            return 'That email is already registered. Please sign in or check your email confirmation link.';
        }

        if ($normalized === '') {
            return 'Unable to save the user profile.';
        }

        return $error;
    }

    /**
     * Supabase signup responses vary by surface:
     * raw Auth REST can return the user fields at the top level,
     * while SDK-style responses nest them under `user`.
     *
     * @param array<string, mixed>|array<mixed>|null $data
     */
    private function extractSignupUserId(array|null $data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        $nestedUserId = $data['user']['id'] ?? null;

        if (is_string($nestedUserId) && $nestedUserId !== '') {
            return $nestedUserId;
        }

        $topLevelUserId = $data['id'] ?? null;

        if (is_string($topLevelUserId) && $topLevelUserId !== '') {
            return $topLevelUserId;
        }

        return null;
    }
}
