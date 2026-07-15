<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>

<?php
$lineItems = cart_line_items();
$cartTotalPhp = cart_total_php();
$isLoggedIn = is_logged_in();
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Review your saved Synapse Motors selections before checkout.">
    <title>Synapse Motors - Cart</title>
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
        <p>Reserved vehicles remain visible here while your session is active.</p><a href="inventory.php">Keep
            browsing</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="cart-page">
        <section class="cart-shell" aria-labelledby="cart-title">
            <header class="section-heading">
                <div>
                    <p class="section-kicker">Cart</p>
                    <h1 id="cart-title">Your garage, staged for checkout.</h1>
                </div>
            </header>
            <?php if (!$isLoggedIn): ?>
            <section class="cart-empty-state" aria-labelledby="cart-signin-title">
                <p class="section-kicker">Member access</p>
                <h2 id="cart-signin-title">Sign in to build a cart that travels with you.</h2>
                <p>Browse the inventory, then sign in when you're ready to keep your shortlist in sync.</p>
                <div class="cart-empty-actions">
                    <a class="button button-dark" href="#" data-auth-trigger="signin" data-auth-label="account">Sign
                        in</a>
                    <a class="text-link" href="inventory.php">Browse inventory <span
                            aria-hidden="true">&rarr;</span></a>
                </div>
            </section>
            <?php elseif ($lineItems === []): ?>
            <section class="cart-empty-state" aria-labelledby="cart-empty-title">
                <p class="section-kicker">No vehicles yet</p>
                <h2 id="cart-empty-title">Your cart is open and ready.</h2>
                <p>Add a vehicle from the inventory to see it staged here with its price, quantity, and quick removal
                    controls.</p>
                <div class="cart-empty-actions">
                    <a class="button button-dark" href="inventory.php">Explore inventory</a>
                    <a class="text-link" href="about.php">Learn how we curate <span aria-hidden="true">&rarr;</span></a>
                </div>
            </section>
            <?php else: ?>
            <div class="cart-layout">
                <section class="cart-list" aria-label="Vehicles in your cart">
                    <?php foreach ($lineItems as $lineItem): ?>
                    <?php $vehicle = $lineItem['vehicle']; ?>
                    <article class="cart-card">
                        <a class="cart-card-media" href="inventory.php#inventory-grid"
                            aria-label="Review <?= htmlspecialchars((string) $vehicle['name']) ?>">
                            <img src="assets/images/<?= htmlspecialchars((string) $vehicle['image']) ?>"
                                width="<?= (int) $vehicle['width'] ?>" height="<?= (int) $vehicle['height'] ?>"
                                loading="lazy" alt="<?= htmlspecialchars((string) $vehicle['alt']) ?>">
                        </a>
                        <div class="cart-card-body">
                            <div class="cart-card-head">
                                <div>
                                    <p class="cart-card-kicker"><?= htmlspecialchars((string) $vehicle['collection']) ?>
                                    </p>
                                    <h2><?= htmlspecialchars((string) $vehicle['name']) ?></h2>
                                    <p class="cart-card-detail"><?= $vehicle['detail'] ?></p>
                                </div>
                                <form action="cart/remove.php" method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="vehicle_id" value="<?= e((string) $vehicle['id']) ?>">
                                    <button class="cart-remove-button" type="submit">Remove</button>
                                </form>
                            </div>
                            <dl class="cart-card-specs">
                                <div>
                                    <dt>Body</dt>
                                    <dd><?= htmlspecialchars((string) $vehicle['body']) ?></dd>
                                </div>
                                <div>
                                    <dt>Powertrain</dt>
                                    <dd><?= htmlspecialchars((string) $vehicle['powertrain']) ?></dd>
                                </div>
                                <div>
                                    <dt>Quantity</dt>
                                    <dd><?= (int) $lineItem['quantity'] ?></dd>
                                </div>
                                <div>
                                    <dt>Line total</dt>
                                    <dd><?= htmlspecialchars('₱' . number_format((int) $lineItem['line_total_php'])) ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>

                <aside class="cart-summary" aria-label="Cart summary">
                    <p class="section-kicker">Summary</p>
                    <h2>Ready for the next step.</h2>
                    <div class="cart-summary-total">
                        <span>Total</span>
                        <strong><?= htmlspecialchars('₱' . number_format($cartTotalPhp)) ?></strong>
                    </div>
                    <p class="cart-summary-note">Taxes, registration, and delivery arrangements are confirmed after
                        inquiry.</p>
                    <div class="cart-summary-actions">
                        <a class="button button-dark" href="checkout.php">Proceed to Checkout</a>
                        <a class="text-link" href="inventory.php">Add another vehicle <span
                                aria-hidden="true">&rarr;</span></a>
                    </div>
                </aside>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>
