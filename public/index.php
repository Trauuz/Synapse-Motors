<!doctype html>
<html lang="en">

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
        <p>Complimentary nationwide delivery on selected vehicles.</p><a href="#inventory">See available cars</a>
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
                <div class="hero-actions"><a class="button button-light" href="#inventory">Explore inventory</a><a
                        class="button button-ghost" href="#visit">Book a test drive</a></div>
            </div>
            <p class="hero-caption">Graphite coupe &middot; North coast, early light</p>
        </section>

        <section class="inventory" id="inventory" aria-labelledby="inventory-title">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">The current edit</p>
                    <h2 id="inventory-title">Cars worth leaving for.</h2>
                </div>
                <a class="text-link" href="#visit">View all inventory <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="filter-row" role="group" aria-label="Filter featured vehicles"><button class="filter is-active"
                    type="button" data-filter="all" aria-pressed="true">All</button><button class="filter" type="button"
                    data-filter="performance" aria-pressed="false">Performance</button><button class="filter"
                    type="button" data-filter="electric" aria-pressed="false">Electric</button><button class="filter"
                    type="button" data-filter="touring" aria-pressed="false">Grand touring</button></div>
            <div class="vehicle-grid" aria-live="polite">
                <?php
            $vehicles = [
                ['Apex GT', 'Performance &middot; Petrol', 'performance touring', 'hero-coast.png', 1962, 802, 'Graphite performance coupe on a coastal overlook'],
                ['Vela R', 'Grand touring &middot; Petrol', 'performance touring', 'tunnel-gt.png', 1796, 876, 'Oxblood grand touring coupe driving through a concrete tunnel'],
                ['Northline E', 'SUV &middot; Electric', 'electric', 'alpine-suv.png', 1536, 1024, 'Silver electric SUV parked beside a lakeside cabin'],
            ];
            foreach ($vehicles as [$name, $detail, $category, $image, $width, $height, $alt]): ?>
                <article class="vehicle-card" data-category="<?= htmlspecialchars($category) ?>">
                    <a class="vehicle-image" href="#visit" aria-label="View <?= htmlspecialchars($name) ?>"><img
                            src="assets/images/<?= htmlspecialchars($image) ?>" width="<?= $width ?>"
                            height="<?= $height ?>" loading="lazy" alt="<?= htmlspecialchars($alt) ?>"></a>
                    <div class="vehicle-meta">
                        <div>
                            <h3><?= htmlspecialchars($name) ?></h3>
                            <p><?= $detail ?></p>
                        </div>
                        <button class="save-button" type="button" aria-label="Save <?= htmlspecialchars($name) ?>"
                            aria-pressed="false" data-save><svg aria-hidden="true" viewBox="0 0 24 24">
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.7-7.5 1.1-1.1a5.5 5.5 0 0 0 0-7.8Z">
                                </path>
                            </svg></button>
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
                <a class="text-link light-link" href="#visit">Meet the grand tourers <span
                        aria-hidden="true">&rarr;</span></a>
            </div>
        </section>

        <section class="journal" id="journal" aria-labelledby="journal-title">
            <div class="journal-image"><img src="assets/images/alpine-suv.png" width="1536" height="1024" loading="lazy"
                    alt="Silver electric SUV at a quiet alpine lake"></div>
            <div class="journal-copy">
                <p class="section-kicker">From the journal</p>
                <h2 id="journal-title">Silence has a range.</h2>
                <p>An electric weekender, a cabin above the water, and the roads that made us stop watching the clock.
                </p>
                <a class="text-link" href="#visit">Read the field note <span aria-hidden="true">&rarr;</span></a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>