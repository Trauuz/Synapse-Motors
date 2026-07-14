<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Confirm your Synapse Motors email and continue to your account with a dedicated secure callback page.">
    <title>Confirm your email | Synapse Motors</title>
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
        <p>Email confirmation is required before account access.</p><a href="index.php">Return to the home page</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="auth-callback-page">
        <section class="auth-callback-shell" aria-labelledby="auth-callback-title">
            <div class="auth-callback-card" data-auth-callback-card>
                <p class="section-kicker">Auth callback</p>
                <h1 id="auth-callback-title">Confirming your Synapse Motors email.</h1>
                <p class="auth-callback-copy" data-auth-callback-message>
                    We are checking your confirmation link now. This usually takes a moment.
                </p>

                <div class="auth-callback-status" data-auth-callback-status="pending">
                    <span class="auth-callback-badge" data-auth-callback-badge>Checking link</span>
                    <ol class="auth-callback-steps">
                        <li>Validate the confirmation token.</li>
                        <li>Unlock your account for sign in.</li>
                        <li>Send you back to continue browsing.</li>
                    </ol>
                </div>

                <div class="auth-callback-actions">
                    <a class="button button-dark" href="index.php" data-auth-callback-primary>Return home</a>
                    <a class="text-link" href="inventory.php">Browse inventory <span aria-hidden="true">&rarr;</span></a>
                </div>

                <p class="auth-callback-meta" data-auth-callback-meta>
                    If the link has expired, request another confirmation email from the sign-up form.
                </p>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>

    <script>
    window.SUPABASE_CONFIG = <?= json_encode(supabase_public_config(), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) ?>;
    </script>
    <script src="assets/js/landing.js" defer></script>
    <script src="assets/js/auth-callback.js" defer></script>
</body>

</html>
