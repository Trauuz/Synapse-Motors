<?php

declare(strict_types=1);

final class AuthService
{
    private UserRepository $users;
    private AuthValidator $validator;
    private LoginRedirectResolver $redirectResolver;
    private EmailVerificationService $emailVerificationService;

    public function __construct(
        UserRepository $users,
        ?AuthValidator $validator = null,
        ?LoginRedirectResolver $redirectResolver = null,
        ?EmailVerificationService $emailVerificationService = null
    ) {
        $this->users = $users;
        $this->validator = $validator ?? new AuthValidator();
        $this->redirectResolver = $redirectResolver ?? new LoginRedirectResolver();
        $this->emailVerificationService = $emailVerificationService ?? email_verification_service();
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

        $email = strtolower(trim((string) $input['email']));
        $existingUser = $this->users->findByEmail($email);

        if (is_array($existingUser) && ($existingUser['email_verified_at'] ?? null) !== null) {
            return [
                'ok' => false,
                'errors' => ['form' => 'That email is already registered. Please sign in instead.'],
                'message' => null,
            ];
        }

        try {
            $existingRole = is_array($existingUser) ? (string) ($existingUser['role'] ?? 'Buyer') : 'Buyer';
            $existingInvitedByUserId = is_array($existingUser) ? ($existingUser['invited_by_user_id'] ?? null) : null;
            $existingInvitedAt = is_array($existingUser) ? ($existingUser['invited_at'] ?? null) : null;
            $role = $existingRole === 'Admin' ? 'Admin' : 'Buyer';

            $attributes = [
                'name' => trim((string) $input['name']),
                'email' => $email,
                'password_hash' => password_hash((string) $input['password'], PASSWORD_DEFAULT),
                'role' => $role,
                'address' => trim((string) $input['complete_address']),
                'contact_no' => trim((string) $input['contact_number']),
                'email_verified_at' => null,
                'access_status' => 'pending_verification',
                'invited_by_user_id' => $existingInvitedByUserId,
                'invited_at' => $existingInvitedAt,
            ];

            if (is_array($existingUser)) {
                $user = $this->users->update((string) $existingUser['id'], $attributes);
            } else {
                $user = $this->users->create($attributes);
            }

            $this->emailVerificationService->sendForUser($user);
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'errors' => ['form' => 'Unable to create your account right now. Please verify the email settings and try again.'],
                'message' => null,
            ];
        }

        return [
            'ok' => true,
            'errors' => [],
            'message' => 'Account created. Check your inbox and confirm your email before signing in.',
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

        $userRecord = $this->users->findByEmail((string) $input['email']);

        if (!is_array($userRecord) || !$this->passwordMatches($userRecord, (string) $input['password'])) {
            return [
                'ok' => false,
                'errors' => ['form' => 'Invalid login or password. Remember that password is case-sensitive.'],
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        if (($userRecord['email_verified_at'] ?? null) === null) {
            return [
                'ok' => false,
                'errors' => ['form' => 'Please confirm your email address before signing in. If you need a fresh link, sign up again with the same email.'],
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        if (($userRecord['access_status'] ?? 'active') !== 'active') {
            return [
                'ok' => false,
                'errors' => ['form' => 'This account is currently disabled. Please contact an administrator.'],
                'message' => null,
                'user' => null,
                'redirect' => 'index.php',
            ];
        }

        $updatedUserRecord = $this->users->update((string) $userRecord['id'], [
            'last_seen_at' => gmdate('Y-m-d H:i:s'),
            'email_verified_at' => $userRecord['email_verified_at'] ?? gmdate('Y-m-d H:i:s'),
        ]);

        $role = $this->resolveRole($updatedUserRecord);
        $user = [
            'id' => $updatedUserRecord['id'],
            'auth_user_id' => $updatedUserRecord['id'],
            'email' => $updatedUserRecord['email'],
            'name' => $updatedUserRecord['name'] ?: 'Synapse Member',
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
     * @param array<string, mixed> $userRecord
     */
    private function passwordMatches(array $userRecord, string $password): bool
    {
        $passwordHash = $userRecord['password_hash'] ?? null;

        return is_string($passwordHash) && $passwordHash !== '' && password_verify($password, $passwordHash);
    }

    /**
     * @param array<string, mixed> $userRecord
     */
    private function resolveRole(array $userRecord): string
    {
        $role = $userRecord['role'] ?? null;

        if (is_string($role) && $role !== '') {
            return $role;
        }

        return 'Buyer';
    }
}
