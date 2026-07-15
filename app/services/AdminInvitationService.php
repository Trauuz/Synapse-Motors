<?php

declare(strict_types=1);

final class AdminInvitationService
{
    public function __construct(private HtmlMailer $mailer)
    {
    }

    /**
     * @param array<string, mixed> $admin
     */
    public function sendForAdmin(array $admin, string $inviterName): void
    {
        $email = strtolower(trim((string) ($admin['email'] ?? '')));
        $name = trim((string) ($admin['name'] ?? 'Administrator'));

        if ($email === '') {
            throw new RuntimeException('Cannot send an admin invitation without an email address.');
        }

        $setupUrl = app_public_url('index.php');

        if ($setupUrl === '') {
            throw new RuntimeException('Unable to build the admin invitation URL.');
        }

        $subject = 'You were invited to the Synapse Motors admin portal';
        $htmlBody = $this->htmlBody($name, $email, $inviterName, $setupUrl);
        $textBody = $this->textBody($name, $email, $inviterName, $setupUrl);

        $this->mailer->sendHtmlMessage($email, $name, $subject, $htmlBody, $textBody);
    }

    private function htmlBody(string $name, string $email, string $inviterName, string $setupUrl): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeInviterName = htmlspecialchars($inviterName === '' ? 'A Synapse Motors administrator' : $inviterName, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($setupUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<body style="margin:0;padding:24px;font-family:Arial,sans-serif;background:#f6f3ee;color:#1c1917;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e7dfd2;padding:32px;">
        <p style="margin:0 0 16px;font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#8b5e34;">Synapse Motors</p>
        <h1 style="margin:0 0 16px;font-size:32px;line-height:1.05;">You have seller access waiting</h1>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hi {$safeName}, {$safeInviterName} invited you to the Synapse Motors admin portal.</p>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Open the site, create your account using this email address, and complete the verification step to activate your admin access.</p>
        <p style="margin:24px 0;">
            <a href="{$safeUrl}" style="display:inline-block;padding:14px 22px;background:#1c1917;color:#ffffff;text-decoration:none;font-weight:700;">Open Synapse Motors</a>
        </p>
        <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#57534e;">Use this email address when you sign up:</p>
        <p style="margin:0;font-size:14px;line-height:1.6;word-break:break-all;">{$safeEmail}</p>
    </div>
</body>
</html>
HTML;
    }

    private function textBody(string $name, string $email, string $inviterName, string $setupUrl): string
    {
        $sender = trim($inviterName) === '' ? 'A Synapse Motors administrator' : $inviterName;

        return "Hi {$name},\n\n{$sender} invited you to the Synapse Motors admin portal.\n\nSign up with this email address:\n{$email}\n\nOpen this link to get started:\n{$setupUrl}";
    }
}
