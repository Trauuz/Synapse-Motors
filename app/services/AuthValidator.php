<?php

declare(strict_types=1);

final class AuthValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public function validateRegistration(array $input): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $passwordConfirmation = (string) ($input['password_confirmation'] ?? '');
        $address = trim((string) ($input['complete_address'] ?? ''));
        $contactNumber = trim((string) ($input['contact_number'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Complete name is required.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }

        if ($address === '') {
            $errors['complete_address'] = 'Complete address is required.';
        }

        if ($contactNumber === '') {
            $errors['contact_number'] = 'Contact numbers are required.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public function validateSignIn(array $input): array
    {
        $errors = [];
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }
}
