<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$homeHref = 'index.php';
$inventoryHref = 'inventory.php';
$performanceHref = 'inventory.php?filter=performance';
$electricHref = 'inventory.php?filter=electric';
$touringHref = 'index.php#grand-touring';
$grandTouringHref = 'inventory.php?filter=touring';
$cityHref = 'inventory.php?filter=city';
$collectorHref = 'inventory.php?filter=collector';
$journalHref = 'index.php#journal';
$aboutHref = 'about.php';
$signupHref = 'index.php#visit';
$isLoggedIn = function_exists('is_logged_in') && is_logged_in();
$isAdminUser = function_exists('is_admin') && is_admin();
$cartAuthAttributes = $isLoggedIn ? '' : ' data-auth-trigger="cart" data-auth-label="cart"';
?>

<header class="site-header" data-header>
    <div class="nav-shell">
        <a class="wordmark" href="<?= $homeHref ?>" aria-label="Synapse Motors home">
            <img class="brand-logo" src="assets/images/company-logo.png" width="813" height="949"
                alt="Synapse Motors logo">
            <span class="brand-name">SYNAPSE</span>
        </a>
        <nav class="desktop-nav" aria-label="Primary navigation">
            <button class="nav-trigger" type="button" aria-label="Vehicles" aria-expanded="false"
                aria-controls="mega-vehicles" data-menu-trigger="vehicles">Vehicles</button>
            <a href="<?= $touringHref ?>">Grand touring</a><a href="<?= $journalHref ?>">Journal</a><a
                href="<?= $aboutHref ?>">About Us</a>
        </nav>
        <div class="nav-actions">
            <a class="nav-icon-link" href="<?= $inventoryHref ?>" aria-label="View cart"<?= $cartAuthAttributes ?>><svg aria-hidden="true"
                    viewBox="0 0 24 24">
                    <circle cx="9" cy="20" r="1.5"></circle>
                    <circle cx="18" cy="20" r="1.5"></circle>
                    <path d="M3 4h2.5l2.1 10.2a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.7L21 8H7.1"></path>
                </svg><span>Cart</span></a>
            <?php if ($isLoggedIn && $isAdminUser): ?>
            <a class="signup-link" href="admin/dashboard.php">Dashboard</a>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
            <form action="auth/logout.php" method="post" class="nav-auth-form">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button class="signup-link" type="submit">Sign Out</button>
            </form>
            <?php else: ?>
            <a class="signup-link" href="<?= $signupHref ?>" data-auth-trigger="signin"
                data-auth-label="account">Sign In</a>
            <?php endif; ?>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu"
                data-mobile-toggle><span>Menu</span><span class="menu-lines" aria-hidden="true"></span></button>
        </div>
    </div>

    <div class="mega-panel" id="mega-vehicles" data-menu-panel="vehicles" hidden>
        <div class="mega-panel-inner">
            <div>
                <p class="mega-heading">Shop by form</p><a
                    href="<?= $performanceHref ?>"><strong>Performance</strong><span>Coupe,
                        roadster, supercar</span></a><a
                    href="inventory.php?filter=suv"><strong>SUV</strong><span>Everyday space,
                        considered</span></a><a href="<?= $electricHref ?>"><strong>Electric</strong><span>Quiet pace,
                        immediate
                        torque</span></a>
            </div>
            <div>
                <p class="mega-heading">Shop by intent</p><a href="<?= $grandTouringHref ?>"><strong>Long
                        distance</strong><span>Grand tourers made to roam</span></a><a
                    href="<?= $cityHref ?>"><strong>City</strong><span>Compact, agile, effortless</span></a><a
                    href="<?= $collectorHref ?>"><strong>Collector</strong><span>Rare cars with provenance</span></a>
            </div>
            <div class="mega-panel-cta">
                <p class="mega-heading">Start here</p>
                <p>See the full collection in one pass, from quiet electric commuters to long-distance tourers.</p>
                <a class="browse-cars-link" href="<?= $inventoryHref ?>">All Vehicles</a>
            </div>
        </div>
    </div>
</header>

<div class="nav-scrim" data-scrim hidden></div>
<aside class="mobile-drawer" id="mobile-menu" data-mobile-menu hidden>
    <div class="mobile-drawer-head">
        <span class="wordmark wordmark-mobile">
            <img class="brand-logo" src="assets/images/company-logo.png" width="813" height="949"
                alt="Synapse Motors logo">
            <span class="brand-name">SYNAPSE</span>
        </span>
        <button type="button" data-mobile-close aria-label="Close menu">Close</button>
    </div>
    <nav aria-label="Mobile navigation"><a href="<?= $inventoryHref ?>">All vehicles</a><a
            href="<?= $electricHref ?>">Electric</a><a href="<?= $touringHref ?>">Grand touring</a><a
            href="<?= $journalHref ?>">Journal</a><a href="<?= $aboutHref ?>">About us</a><a
            href="<?= $signupHref ?>">Visit us</a></nav>
</aside>
