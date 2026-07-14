<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__) . '/app/config/database.php'; ?>
<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Learn how Synapse Motors curates performance, electric, and grand touring vehicles with a quiet editorial eye.">
    <title>About Synapse Motors</title>
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
        <p>Complimentary nationwide delivery on selected vehicles.</p><a href="inventory.php">See available cars</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="about-page">
        <section class="about-marquee" aria-labelledby="about-title">
            <div class="about-marquee-copy">
                <p class="section-kicker">About Synapse Motors</p>
                <h1 id="about-title">We source cars for people who still notice the long way home.</h1>
                <p class="about-intro">Synapse Motors is a small-format automotive house built around edited choice,
                    provenance, and the feeling that a good drive should slow your pulse before it raises it.</p>
                <p>From first sighting to final handover, we keep the process legible.</p>
                <div class="about-actions">
                    <a class="button button-dark" href="inventory.php">Browse the collection</a>
                    <a class="text-link" href="index.php#visit">Plan a visit <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
        </section>

    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>
