<?php

declare(strict_types=1);

final class EmailVerificationService
{
    public function __construct(
        private UserRepository $users,
        private EmailVerificationTokenRepository $tokens,
        private HtmlMailer $mailer
    ) {
    }

    /**
     * @param array<string, mixed> $user
     */
    public function sendForUser(array $user): void
    {
        $userId = (string) ($user['id'] ?? '');
        $email = strtolower(trim((string) ($user['email'] ?? '')));

        if ($userId === '' || $email === '') {
            throw new RuntimeException('Cannot send a verification email without a user ID and email address.');
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + (24 * 60 * 60));

        $this->tokens->deleteUnconsumedForUser($userId);
        $this->tokens->create($userId, $tokenHash, $expiresAt);

        $verificationUrl = app_public_url('auth-callback.php?token=' . rawurlencode($token));

        if ($verificationUrl === '') {
            throw new RuntimeException('Unable to build the verification URL.');
        }

        $name = trim((string) ($user['name'] ?? 'Synapse Member'));
        $subject = 'Confirm your Synapse Motors account';
        $htmlBody = $this->htmlBody($name, $verificationUrl);
        $textBody = $this->textBody($name, $verificationUrl);

        $this->mailer->sendHtmlMessage($email, $name, $subject, $htmlBody, $textBody);
    }

    /**
     * @return array{ok: bool, status: string, message: string}
     */
    public function verifyToken(?string $token): array
    {
        if (!is_string($token) || trim($token) === '') {
            return [
                'ok' => false,
                'status' => 'error',
                'message' => 'That verification link is incomplete. Please use the full email link.',
            ];
        }

        $tokenRecord = $this->tokens->findActiveByTokenHash(hash('sha256', trim($token)));

        if (!is_array($tokenRecord)) {
            return [
                'ok' => false,
                'status' => 'error',
                'message' => 'That verification link is invalid or has expired. Sign up again to receive a fresh email.',
            ];
        }

        $user = $this->users->findById((string) $tokenRecord['user_id']);

        if (!is_array($user)) {
            return [
                'ok' => false,
                'status' => 'error',
                'message' => 'We could not find the account for this verification link.',
            ];
        }

        if (($user['email_verified_at'] ?? null) !== null) {
            $this->tokens->consumeForUser((string) $user['id']);

            return [
                'ok' => true,
                'status' => 'success',
                'message' => 'Your email was already confirmed. You can sign in now.',
            ];
        }

        $this->users->update((string) $user['id'], [
            'email_verified_at' => gmdate('Y-m-d H:i:s'),
            'access_status' => 'active',
        ]);
        $this->tokens->consumeForUser((string) $user['id']);

        return [
            'ok' => true,
            'status' => 'success',
            'message' => 'Your email is confirmed. You can sign in now.',
        ];
    }

    private function htmlBody(string $name, string $verificationUrl): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;font-family:Arial,sans-serif;background:#f6f3ee;color:#1c1917;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e7dfd2;padding:32px;">
        <p style="margin:0 0 16px;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#8b5e34;">Synapse Motors</p>
        <h1 style="margin:0 0 16px;font-size:32px;line-height:1.05;">Confirm your email address</h1>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hi {$safeName}, thanks for creating your account. Confirm your email to activate sign-in access.</p>
        <p style="margin:24px 0;">
            <a href="{$safeUrl}" style="display:inline-block;padding:14px 22px;background:#1c1917;color:#ffffff;text-decoration:none;font-weight:700;">Confirm email</a>
        </p>
        <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#57534e;">If the button does not work, open this link:</p>
        <p style="margin:0;font-size:14px;line-height:1.6;word-break:break-all;"><a href="{$safeUrl}">{$safeUrl}</a></p>
        <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#78716c;">This link expires in 24 hours.</p>
    </div>
</body>
</html>
HTML;
    }

    private function textBody(string $name, string $verificationUrl): string
    {
        return "Hi {$name},\n\nConfirm your Synapse Motors account by opening this link:\n{$verificationUrl}\n\nThis link expires in 24 hours.";
    }
}
