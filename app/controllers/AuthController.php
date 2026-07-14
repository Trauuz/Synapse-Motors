<?php

declare(strict_types=1);

final class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('../index.php');
        }

        if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
            if ($this->expectsJson()) {
                $this->json([
                    'ok' => false,
                    'errors' => ['form' => 'Your session expired. Please try again.'],
                ], 422);
            }

            $this->redirectWithState(
                $this->sanitizeMode((string) ($_POST['auth_action'] ?? 'signin')),
                $this->sanitizeRedirectTarget((string) ($_POST['redirect_to'] ?? 'index.php')),
                ['form' => 'Your session expired. Please try again.'],
                $this->oldInput(),
                null
            );
        }

        $mode = $this->sanitizeMode((string) ($_POST['auth_action'] ?? 'signin'));

        if ($mode === 'signup') {
            $this->handleSignUp();
            return;
        }

        $this->handleSignIn();
    }

    private function handleSignUp(): void
    {
        $redirectTarget = $this->sanitizeRedirectTarget((string) ($_POST['redirect_to'] ?? 'index.php'));
        $result = $this->authService->register($_POST);

        if ($result['ok']) {
            if ($this->expectsJson()) {
                $this->json([
                    'ok' => true,
                    'message' => $result['message'],
                    'redirect' => null,
                ]);
            }

            flash_set('auth_form', [
                'mode' => 'signin',
                'errors' => [],
                'old' => ['email' => trim((string) ($_POST['email'] ?? ''))],
                'message' => $result['message'],
                'open' => true,
            ]);
            $this->redirect($redirectTarget);
        }

        if ($this->expectsJson()) {
            $this->json([
                'ok' => false,
                'errors' => $result['errors'],
            ], 422);
        }

        $this->redirectWithState('signup', $redirectTarget, $result['errors'], $this->oldInput(), null);
    }

    private function handleSignIn(): void
    {
        $redirectTarget = $this->sanitizeRedirectTarget((string) ($_POST['redirect_to'] ?? 'index.php'));
        $result = $this->authService->signIn($_POST);

        if ($result['ok']) {
            sign_in_user($result['user'] ?? []);

            if ($this->expectsJson()) {
                $this->json([
                    'ok' => true,
                    'redirect' => $result['redirect'],
                    'message' => null,
                ]);
            }

            $this->redirect($this->serverRedirectTarget($result['redirect']));
        }

        if ($this->expectsJson()) {
            $this->json([
                'ok' => false,
                'errors' => $result['errors'],
            ], 422);
        }

        $this->redirectWithState('signin', $redirectTarget, $result['errors'], ['email' => trim((string) ($_POST['email'] ?? ''))], null);
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, string> $old
     */
    private function redirectWithState(string $mode, string $redirectTarget, array $errors, array $old, ?string $message): void
    {
        flash_set('auth_form', [
            'mode' => $mode,
            'errors' => $errors,
            'old' => $old,
            'message' => $message,
            'open' => true,
        ]);

        $this->redirect($redirectTarget);
    }

    /**
     * @return array<string, string>
     */
    private function oldInput(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'contact_number' => trim((string) ($_POST['contact_number'] ?? '')),
            'complete_address' => trim((string) ($_POST['complete_address'] ?? '')),
        ];
    }

    private function sanitizeMode(string $mode): string
    {
        return $mode === 'signup' ? 'signup' : 'signin';
    }

    private function sanitizeRedirectTarget(string $redirectTarget): string
    {
        $path = parse_url($redirectTarget, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return 'index.php';
        }

        $basename = basename($path);
        $allowed = ['index.php', 'inventory.php', 'about.php', 'cart.php', 'checkout.php', 'payment.php'];

        if (!in_array($basename, $allowed, true)) {
            return 'index.php';
        }

        $query = parse_url($redirectTarget, PHP_URL_QUERY);

        if (!is_string($query) || $query === '') {
            return '../' . $basename;
        }

        return '../' . $basename . '?' . $query;
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    private function serverRedirectTarget(string $location): string
    {
        if (str_starts_with($location, '../')) {
            return $location;
        }

        return '../' . ltrim($location, '/');
    }

    private function expectsJson(): bool
    {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        if (strcasecmp((string) $requestedWith, 'XMLHttpRequest') === 0) {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return is_string($accept) && str_contains($accept, 'application/json');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }
}
