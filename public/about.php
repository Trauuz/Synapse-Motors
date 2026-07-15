<!doctype html>
<html lang="en">
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
        <section class="journal" aria-labelledby="journal-title">
            <div class="journal-image"><img src="assets/images/company-building.png" width="1536" height="802"
                    loading="lazy" alt="Company building"></div>
            <div class="journal-copy">
                <p class="section-kicker">About Synapse Motors</p>
                <h2>We source cars for people</h2>
                <p>Synapse Motors is a made-up company that sells vehicles, offering a wide range of
                    quality cars at competitive prices while providing reliable and customer-oriented automotive
                    services.
                </p>
                <a class="button button-dark" href="inventory.php">Browse the collection</a>
            </div>
        </section>

        <section class="about-member" aria-labelledby="about-member-title">
            <figure class="about-member-portrait">
                <img src="assets/images/carabbacan.jpg" width="1024" height="1024" loading="lazy"
                    alt="Portrait of the Synapse Motors founder">
            </figure>

            <div class="about-member-copy">
                <p class="section-kicker">Member profile</p>
                <h2 id="about-member-title">Built and run by one focused operator.</h2>
                <p>
                    Synapse Motors is currently shaped by a single member handling the curation, storefront, and
                    customer experience end to end. That smaller scale keeps the collection selective and the
                    presentation deliberate.
                </p>
                <p>
                    Every page choice, listing detail, and support touchpoint is meant to feel considered rather than
                    mass-produced, which is why the brand reads more like a studio than a dealership floor.
                </p>
                <dl class="about-member-meta">
                    <div>
                        <dt>Role</dt>
                        <dd>Founder and sole member</dd>
                    </div>
                    <div>
                        <dt>Focus</dt>
                        <dd>Selection, design, and customer flow</dd>
                    </div>
                </dl>
            </div>
        </section>


    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>
