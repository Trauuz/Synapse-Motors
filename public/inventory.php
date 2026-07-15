<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Browse every Synapse Motors vehicle in one place, from electric commuters to grand touring machines.">
    <title>Synapse Motors - Inventory</title>
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
        <p>Every current Synapse Motors listing, sorted for quick comparison.</p><a href="#inventory-grid">Jump to
            the inventory</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="inventory-page">
        <section class="inventory-directory" aria-labelledby="inventory-grid-title">
            <div class="section-heading inventory-directory-heading">
                <div>
                    <p class="section-kicker">Directory</p>
                    <h2 id="inventory-grid-title">Choose the drive that fits the day.</h2>
                </div>
            </div>

            <div class="filter-row inventory-filter-row" aria-label="Filter all vehicles">
                <div class="inventory-filter-actions" role="group" aria-label="Vehicle categories">
                    <button class="filter is-active" type="button" data-filter="all" aria-pressed="true">All</button>
                    <button class="filter" type="button" data-filter="performance"
                        aria-pressed="false">Performance</button>
                    <button class="filter" type="button" data-filter="electric" aria-pressed="false">Electric</button>
                    <button class="filter" type="button" data-filter="suv" aria-pressed="false">SUV</button>
                    <button class="filter" type="button" data-filter="touring" aria-pressed="false">Grand
                        touring</button>
                    <button class="filter" type="button" data-filter="city" aria-pressed="false">City</button>
                    <button class="filter" type="button" data-filter="collector" aria-pressed="false">Collector</button>
                </div>
                <label class="inventory-search" for="inventory-search">
                    <input id="inventory-search" type="search" name="inventory_search"
                        placeholder="Search by model, body, or powertrain" data-inventory-search>
                </label>
            </div>

            <div class="inventory-cards" id="inventory-grid" aria-live="polite">
                <?php foreach (synapse_vehicle_inventory() as $vehicle): ?>
                <article class="inventory-record" data-category="<?= htmlspecialchars($vehicle['category']) ?>"
                    data-search="<?= htmlspecialchars(strtolower(implode(' ', [
                        $vehicle['name'],
                        $vehicle['collection'],
                        strip_tags($vehicle['detail']),
                        $vehicle['body'],
                        $vehicle['powertrain'],
                        $vehicle['drive'],
                        $vehicle['availability'],
                    ]))) ?>">
                    <a class="inventory-record-media" href="#inventory-grid"
                        aria-label="Review <?= htmlspecialchars($vehicle['name']) ?>">
                        <img src="assets/images/<?= htmlspecialchars($vehicle['image']) ?>"
                            width="<?= $vehicle['width'] ?>" height="<?= $vehicle['height'] ?>" loading="lazy"
                            alt="<?= htmlspecialchars($vehicle['alt']) ?>" draggable="false">
                    </a>
                    <div class="inventory-record-body">
                        <p class="inventory-record-kicker"><?= htmlspecialchars($vehicle['collection']) ?></p>
                        <div class="inventory-record-head">
                            <div>
                                <h3><?= htmlspecialchars($vehicle['name']) ?></h3>
                                <p><?= $vehicle['detail'] ?></p>
                            </div>
                        </div>
                        <dl class="inventory-specs">
                            <div>
                                <dt>Body</dt>
                                <dd><?= htmlspecialchars($vehicle['body']) ?></dd>
                            </div>
                            <div>
                                <dt>Powertrain</dt>
                                <dd><?= htmlspecialchars($vehicle['powertrain']) ?></dd>
                            </div>
                            <div>
                                <dt>Drive</dt>
                                <dd><?= htmlspecialchars($vehicle['drive']) ?></dd>
                            </div>
                            <div>
                                <dt>Status</dt>
                                <dd><?= htmlspecialchars($vehicle['availability']) ?></dd>
                            </div>
                        </dl>
                        <div class="inventory-record-actions">
                            <p class="inventory-record-price">
                                <strong><?= htmlspecialchars(format_vehicle_price_php((int) $vehicle['price'])) ?></strong>
                            </p>
                            <?php if (is_logged_in()): ?>
                            <form action="cart/add.php" method="post" class="inventory-cart-form">
                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="vehicle_id" value="<?= e((string) $vehicle['id']) ?>">
                                <input type="hidden" name="redirect_to" value="<?= e(current_request_path()) ?>">
                                <button class="browse-cars-link" type="submit">Add to Cart</button>
                            </form>
                            <?php else: ?>
                            <a class="browse-cars-link" href="#" data-auth-trigger="save" data-auth-label="save">Add
                                to Cart</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>
