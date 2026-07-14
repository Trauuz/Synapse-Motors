<?php
$homeHref = 'index.php';
$inventoryHref = 'inventory.php';
$journalHref = 'index.php#journal';
$aboutHref = 'about.php';
$supportHref = 'index.php#visit';
?>

<footer class="site-footer">
    <div class="footer-main">
        <nav class="footer-column" aria-labelledby="footer-help-title">
            <a class="wordmark" href="<?= $homeHref ?>" aria-label="Synapse Motors home">
                <img class="brand-logo" src="assets/images/company-logo.png" width="813" height="949"
                    alt="Synapse Motors logo">
                <span class="brand-name">SYNAPSE</span>
            </a>
        </nav>
        <section class="footer-column" aria-labelledby="footer-contact-title">
            <h2 id="footer-contact-title" class="footer-heading">Contact us</h2>
            <ul class="footer-links footer-links-strong">
                <li><a href="https://wa.me/852800906220">WhatsApp</a></li>
                <li><a href="<?= $supportHref ?>">Live Chat</a></li>
            </ul>
            <div class="footer-contact-copy">
                <p><strong>Phone:</strong> +852 8009 06220 (Toll Free)</p>
                <p><strong>Email:</strong> <a href="mailto:gec@synapsemotors.example">gec@synapsemotors.example</a></p>
                <p>Monday to Sunday &amp; Hong Kong SAR Public Holidays (except Christmas Day, Boxing Day and the first
                    three days of LNY)</p>
                <p>9AM - 6PM PHT</p>
            </div>
        </section>
        <nav class="footer-column" aria-labelledby="footer-help-title">
            <h2 id="footer-help-title" class="footer-heading">Help</h2>
            <ul class="footer-links">
                <li><a href="<?= $aboutHref ?>">About us</a></li>
                <li><a href="<?= $inventoryHref ?>">Track Order &amp; Return</a></li>
                <li><a href="<?= $supportHref ?>">Ordering &amp; Payment</a></li>
                <li><a href="<?= $supportHref ?>">Delivery</a></li>
                <li><a href="<?= $supportHref ?>">Returns &amp; Refunds</a></li>
                <li><a href="<?= $journalHref ?>">Our Products</a></li>
            </ul>
        </nav>
    </div>
    <div class="footer-meta"><span>This website is for educational purposes only and is a requirement for a
            final project.
        </span><span>&copy; <?= date('Y') ?>
            Synapse Motors.</span></div>
</footer>