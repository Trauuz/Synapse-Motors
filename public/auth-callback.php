<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$verificationResult = email_verification_service()->verifyToken($_GET['token'] ?? null);
$status = (string) ($verificationResult['status'] ?? 'error');
$message = (string) ($verificationResult['message'] ?? 'We could not verify that link.');
$title = $status === 'success' ? 'Your account is ready.' : 'Verification needed.';
$badge = $status === 'success' ? 'Ready to sign in' : 'Verification issue';
$description = $status === 'success'
    ? 'Your email is confirmed. Return to the sign-in form and continue browsing inventory.'
    : 'That confirmation link could not be completed. You may need to sign up again to receive a fresh email.';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Confirm your Synapse Motors account email so sign-in can be activated.">
    <title>Email confirmation | Synapse Motors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&amp;family=Manrope:wght@400;600;700;800&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/media.css">
</head>

<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="announcement">
        <p>Accounts are ready to use as soon as registration finishes.</p><a href="index.php">Return to the home page</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="auth-callback-page">
        <section class="auth-callback-shell" aria-labelledby="auth-callback-title">
            <div class="auth-callback-card">
                <p class="section-kicker">Email confirmation</p>
                <h1 id="auth-callback-title"><?= e($title) ?></h1>
                <p class="auth-callback-copy">
                    <?= e($description) ?>
                </p>

                <div class="auth-callback-status" data-auth-callback-status="<?= e($status) ?>">
                    <span class="auth-callback-badge"><?= e($badge) ?></span>
                    <p class="auth-callback-copy"><?= e($message) ?></p>
                    <ol class="auth-callback-steps">
                        <li>Create your account from the sign-up form.</li>
                        <li>Open the confirmation email and use the verification link.</li>
                        <li>Sign in with your email and password.</li>
                    </ol>
                </div>

                <div class="auth-callback-actions">
                    <a class="button button-dark" href="index.php">Return home</a>
                    <a class="text-link" href="inventory.php">Browse inventory <span aria-hidden="true">&rarr;</span></a>
                </div>

                <p class="auth-callback-meta">
                    If the link expired, sign up again with the same email address to request a new confirmation email.
                </p>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>
