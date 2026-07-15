<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>
<!doctype html>
<html lang="en">

<?php
if (!is_logged_in()) {
    header('Location: cart.php');
    exit;
}

$lineItems = cart_line_items();

if ($lineItems === []) {
    header('Location: cart.php');
    exit;
}

$user = current_user() ?? [];
$cartTotalPhp = cart_total_php();
$checkoutErrors = flash_pull('checkout_errors', []);
$checkoutOld = flash_pull('checkout_old', []);
$latestReceipt = latest_payment_receipt();

if ($latestReceipt !== null) {
    clear_latest_payment_receipt();
}

$name = trim((string) ($checkoutOld['customer_name'] ?? ($user['name'] ?? '')));
$email = trim((string) ($checkoutOld['customer_email'] ?? ($user['email'] ?? '')));
$contactNumber = trim((string) ($checkoutOld['contact_number'] ?? ''));
$notes = trim((string) ($checkoutOld['notes'] ?? ''));
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Confirm your Synapse Motors contact details before payment simulation.">
    <title>Synapse Motors - Checkout</title>
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
        <p>Your selections are staged. Confirm the contact details for your reservation.</p><a href="cart.php">Return
            to cart</a>
    </div>

    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="checkout-page">
        <section class="checkout-shell" aria-labelledby="checkout-title">
            <div class="checkout-grid">
                <form action="checkout/submit.php" method="post" class="checkout-form-shell" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <section class="checkout-section-card" aria-labelledby="checkout-contact-title">
                        <div class="checkout-section-head">
                            <p class="section-kicker">Primary contact</p>
                            <h2 id="checkout-contact-title">Who should we brief first?</h2>
                        </div>
                        <?php if (($checkoutErrors['form'] ?? null) !== null): ?>
                        <p class="checkout-form-error"><?= e((string) $checkoutErrors['form']) ?></p>
                        <?php endif; ?>
                        <div class="checkout-field-grid">
                            <label class="checkout-field">
                                <span>Name</span>
                                <input type="text" name="customer_name" value="<?= e($name) ?>" required>
                                <?php if (($checkoutErrors['customer_name'] ?? null) !== null): ?>
                                <small
                                    class="checkout-field-error"><?= e((string) $checkoutErrors['customer_name']) ?></small>
                                <?php endif; ?>
                            </label>
                            <label class="checkout-field">
                                <span>Email</span>
                                <input type="email" name="customer_email" value="<?= e($email) ?>" required>
                                <?php if (($checkoutErrors['customer_email'] ?? null) !== null): ?>
                                <small
                                    class="checkout-field-error"><?= e((string) $checkoutErrors['customer_email']) ?></small>
                                <?php endif; ?>
                            </label>
                            <label class="checkout-field checkout-field-wide">
                                <span>Contact number</span>
                                <input type="text" name="contact_number" value="<?= e($contactNumber) ?>" required>
                                <?php if (($checkoutErrors['contact_number'] ?? null) !== null): ?>
                                <small
                                    class="checkout-field-error"><?= e((string) $checkoutErrors['contact_number']) ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                    </section>

                    <div class="checkout-actions">
                        <a class="text-link" href="cart.php">Back to cart <span aria-hidden="true">&rarr;</span></a>
                        <button class="button button-dark checkout-submit" type="submit">Continue to payment</button>
                    </div>
                </form>

                <aside class="checkout-summary-rail" aria-label="Checkout summary">
                    <div class="checkout-summary-card">
                        <p class="section-kicker">Today's review</p>
                        <h2>Every vehicle, one final review.</h2>
                        <ul class="checkout-summary-list">
                            <?php foreach ($lineItems as $lineItem): ?>
                            <?php $vehicle = $lineItem['vehicle']; ?>
                            <li>
                                <span>
                                    <strong><?= e((string) $vehicle['name']) ?></strong>
                                    <small><?= e((string) $vehicle['detail']) ?></small>
                                </span>
                                <strong>&#8369;<?= number_format((int) $lineItem['line_total_php']) ?></strong>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="checkout-summary-total">
                            <span>Estimated total</span>
                            <strong>&#8369;<?= number_format($cartTotalPhp) ?></strong>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/auth_modal.php'; ?>

    <script src="assets/js/landing.js" defer></script>
</body>

</html>