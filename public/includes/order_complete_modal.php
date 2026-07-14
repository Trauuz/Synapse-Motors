<?php
$orderReceipt = function_exists('latest_payment_receipt') ? latest_payment_receipt() : null;
$orderModalOpen = is_array($orderReceipt) ? 'true' : 'false';
$orderModalHidden = is_array($orderReceipt) ? '' : 'hidden';
$orderReference = is_array($orderReceipt) ? (string) ($orderReceipt['reference'] ?? '') : '';
$orderTotalPhp = is_array($orderReceipt) ? (int) ($orderReceipt['amount_php'] ?? 0) : 0;
$orderCustomerName = is_array($orderReceipt) ? (string) ($orderReceipt['customer_name'] ?? 'Synapse Member') : 'Synapse Member';
$orderPaymentLabel = is_array($orderReceipt) ? (string) ($orderReceipt['payment_method_label'] ?? 'Payment simulation') : 'Payment simulation';

if (is_array($orderReceipt)) {
    clear_latest_payment_receipt();
}
?>

<div class="order-complete-shell" data-order-complete-modal data-order-complete-open-on-load="<?= e($orderModalOpen) ?>"
    <?= $orderModalHidden ?>>
    <div class="order-complete-backdrop" data-order-complete-close></div>
    <section class="order-complete-modal" role="dialog" aria-modal="true" aria-labelledby="order-complete-title">
        <button class="order-complete-close" type="button" aria-label="Close order complete modal"
            data-order-complete-close>
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="order-complete-copy">
            <p class="section-kicker">Order complete</p>
            <h2 id="order-complete-title">Order complete. We'll take it from here.</h2>
            <p><?= e($orderCustomerName) ?>, your reservation has been staged and the Synapse Motors team has the
                details they need for the next follow-up.</p>
        </div>
        <dl class="order-complete-meta">
            <div>
                <dt>Reference</dt>
                <dd><?= e($orderReference) ?></dd>
            </div>
            <div>
                <dt>Route</dt>
                <dd><?= e($orderPaymentLabel) ?></dd>
            </div>
            <div>
                <dt>Reserved total</dt>
                <dd>&#8369;<?= number_format($orderTotalPhp) ?></dd>
            </div>
        </dl>
        <div class="order-complete-actions">
            <a class="button button-dark" href="inventory.php">Keep browsing</a>
            <button class="text-link order-complete-dismiss" type="button" data-order-complete-close>Close</button>
        </div>
    </section>
</div>
