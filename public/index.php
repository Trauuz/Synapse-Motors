<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__) . '/app/config/database.php'; ?>
<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>
<?php require_once dirname(__DIR__) . '/app/helpers/vehicles.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Discover selected performance, electric, and grand touring vehicles at Synapse Motors.">
    <title>Synapse Motors - Find your next drive</title>
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

    <main id="main-content">
        <section class="hero" id="top" aria-labelledby="hero-title">
            <img src="assets/images/hero-coast.png" width="1962" height="802"
                alt="Graphite performance coupe overlooking a rugged coast">
            <div class="hero-shade"></div>
            <div class="hero-copy">
                <p>New collection &middot; Coast road</p>
                <h1 id="hero-title">Find your next drive.</h1>
                <div class="hero-actions"><a class="button button-light" href="inventory.php">Explore inventory</a><a
                        class="button button-ghost" href="#">Book a test drive</a></div>
            </div>
            <p class="hero-caption">Graphite coupe &middot; North coast, early light</p>
        </section>

        <!-- Featured Cars -->
        <section class="inventory" id="inventory" aria-labelledby="inventory-title">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">The current edit</p>
                    <h2 id="inventory-title">Cars worth leaving for.</h2>
                </div>
                <a class="text-link" href="inventory.php">View inventory <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="inventory-featured-grid" aria-label="Featured Synapse vehicles">
                <?php
            $vehicles = array_slice(synapse_vehicle_inventory(), 0, 3);
            foreach ($vehicles as $vehicle): ?>
                <article class="inventory-feature-card">
                    <a class="inventory-feature-media" href="inventory.php"
                        aria-label="View <?= htmlspecialchars($vehicle['name']) ?>"><img
                            src="assets/images/<?= htmlspecialchars($vehicle['image']) ?>"
                            width="<?= $vehicle['width'] ?>" height="<?= $vehicle['height'] ?>" loading="lazy"
                            alt="<?= htmlspecialchars($vehicle['alt']) ?>"></a>
                    <div class="inventory-feature-copy">
                        <div>
                            <p class="inventory-feature-kicker"><?= htmlspecialchars($vehicle['collection']) ?>
                            </p>
                            <h3><?= htmlspecialchars($vehicle['name']) ?></h3>
                            <p><?= htmlspecialchars($vehicle['detail']) ?></p>
                        </div>
                        <div class="inventory-feature-meta">
                            <span><?= htmlspecialchars($vehicle['availability']) ?></span>
                            <a href="inventory.php">Explore</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="story-fold" id="grand-touring" aria-labelledby="touring-title">
            <img src="assets/images/tunnel-gt.png" width="1796" height="876" loading="lazy"
                alt="Oxblood grand touring coupe in a concrete tunnel">
            <div class="story-card">
                <p>Road note &middot; 01</p>
                <h2 id="touring-title">Built for the long way home.</h2>
                <a class="text-link light-link" href="inventory.php?filter=touring">Meet the grand tourers <span
                        aria-hidden="true">&rarr;</span></a>
            </div>
        </section>

        <section class="journal" id="journal" aria-labelledby="journal-title">
            <div class="journal-image"><img src="assets/images/alpine-suv.png" width="1536" height="1024" loading="lazy"
                    alt="Silver electric SUV at a quiet alpine lake"></div>
            <div class="journal-copy">
                <p class="section-kicker">From the journal</p>
                <h2 id="journal-title">Silence has a range.</h2>
                <p>An electric weekender, a cabin above the water, and the roads that made us stop watching the
                    clock.
                </p>
                <a class="text-link" href="#visit">Read the field note <span aria-hidden="true">&rarr;</span></a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/order_complete_modal.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script>
    window.SUPABASE_CONFIG = <?= json_encode(supabase_public_config(), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) ?>;
    </script>
    <script src="assets/js/landing.js" defer></script>
</body>

</html>
