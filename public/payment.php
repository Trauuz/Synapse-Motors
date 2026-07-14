<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__) . '/app/config/database.php'; ?>
<?php require_once dirname(__DIR__) . '/app/bootstrap.php'; ?>

<?php
if (!is_logged_in()) {
    header('Location: cart.php');
    exit;
}

$paymentErrors = flash_pull('payment_errors', []);
$paymentOld = flash_pull('payment_old', []);
$receipt = latest_payment_receipt();
$draft = checkout_draft();

if ($receipt === null && $draft === null) {
    header('Location: checkout.php');
    exit;
}

$selectedPaymentMethod = trim((string) ($paymentOld['payment_method'] ?? 'reservation'));
$draftCustomer = is_array($draft) && is_array($draft['customer'] ?? null) ? $draft['customer'] : [];
$customerName = trim((string) ($draftCustomer['name'] ?? ''));
$lineItems = is_array($draft) && is_array($draft['line_items'] ?? null) ? $draft['line_items'] : [];
$totalPhp = is_array($draft) ? (int) ($draft['total_php'] ?? 0) : 0;
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Simulate the Synapse Motors payment step and receive a reservation reference.">
    <title>Synapse Motors - Payment</title>
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
    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="main-content" class="payment-page">
        <section class="payment-shell" aria-labelledby="payment-title">
            <?php if ($receipt !== null): ?>
            <section class="payment-success-card" aria-labelledby="payment-success-title">
                <div class="payment-success-copy">
                    <p class="section-kicker">Simulation complete</p>
                    <h1 id="payment-success-title">Reserved. Routed. Ready for follow-up.</h1>
                    <p>Your guided payment step is complete. We've generated a reservation reference and staged the next
                        follow-up just like a live payment confirmation would.</p>
                </div>
                <dl class="payment-success-meta">
                    <div>
                        <dt>Reference</dt>
                        <dd><?= e((string) ($receipt['reference'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt>Payment path</dt>
                        <dd><?= e((string) ($receipt['payment_method_label'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt>Reserved total</dt>
                        <dd>&#8369;<?= number_format((int) ($receipt['amount_php'] ?? 0)) ?></dd>
                    </div>
                    <div>
                        <dt>Primary contact</dt>
                        <dd><?= e((string) ($receipt['customer_name'] ?? '')) ?></dd>
                    </div>
                </dl>
                <div class="payment-success-actions">
                    <a class="button button-dark" href="inventory.php">Return to inventory</a>
                    <a class="text-link" href="cart.php">View cart again <span aria-hidden="true">&rarr;</span></a>
                </div>
            </section>
            <?php else: ?>
            <div class="payment-grid">
                <form action="payment/submit.php" method="post" class="payment-form-shell" novalidate data-pending-form>
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <section class="payment-method-card" aria-labelledby="payment-method-title">
                        <div class="checkout-section-head">
                            <p class="section-kicker">Simulation mode</p>
                            <h2 id="payment-method-title">Payment Method</h2>
                            <p class="checkout-option-copy"><small>No actual payments will be made</small></p>
                        </div>
                        <?php if (($paymentErrors['form'] ?? null) !== null): ?>
                        <p class="checkout-form-error"><?= e((string) $paymentErrors['form']) ?></p>
                        <?php endif; ?>
                        <div class="payment-option-stack">
                            <label class="checkout-option-card">
                                <input type="radio" name="payment_method" value="reservation"
                                    <?= $selectedPaymentMethod === 'reservation' ? ' checked' : '' ?>>
                                <span class="checkout-option-copy">
                                    <strong>Outright Cash</strong>
                                </span>
                            </label>
                            <label class="checkout-option-card">
                                <input type="radio" name="payment_method" value="bank-transfer"
                                    <?= $selectedPaymentMethod === 'bank-transfer' ? ' checked' : '' ?>>
                                <span class="checkout-option-copy">
                                    <strong>Credit/Debit</strong>
                                </span>
                            </label>
                            <label class="checkout-option-card">
                                <input type="radio" name="payment_method" value="advisor-follow-up"
                                    <?= $selectedPaymentMethod === 'advisor-follow-up' ? ' checked' : '' ?>>
                                <span class="checkout-option-copy">
                                    <strong>Financing (Installments)</strong>
                                </span>
                            </label>
                        </div>
                        <?php if (($paymentErrors['payment_method'] ?? null) !== null): ?>
                        <p class="checkout-field-error"><?= e((string) $paymentErrors['payment_method']) ?></p>
                        <?php endif; ?>
                    </section>
                    <div class="checkout-actions">
                        <a class="text-link" href="checkout.php">Back to checkout details <span
                                aria-hidden="true">&rarr;</span></a>
                        <button class="button button-dark checkout-submit" type="submit" data-pending-button
                            data-pending-label="Processing payment...">Complete payment</button>
                    </div>
                </form>

                <aside class="payment-summary-rail" aria-label="Payment summary">
                    <div class="payment-summary-card">
                        <p class="section-kicker">Reserved set</p>
                        <h2><?= e($customerName) ?> is confirming a <?= count($lineItems) ?>-vehicle reservation.</h2>
                        <ul class="checkout-summary-list">
                            <?php foreach ($lineItems as $lineItem): ?>
                            <?php $vehicle = $lineItem['vehicle'] ?? []; ?>
                            <li>
                                <span>
                                    <strong><?= e((string) ($vehicle['name'] ?? '')) ?></strong>
                                    <small><?= e((string) ($vehicle['detail'] ?? '')) ?></small>
                                </span>
                                <strong>&#8369;<?= number_format((int) ($lineItem['line_total_php'] ?? 0)) ?></strong>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="checkout-summary-total">
                            <span>Simulated total</span>
                            <strong>&#8369;<?= number_format($totalPhp) ?></strong>
                        </div>
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
