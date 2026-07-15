<!doctype html>
<html lang="en">
<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; ?>

<?php
require_admin();
record_admin_activity('Viewed dashboard', 'Opened the seller dashboard.');

$inventory = synapse_vehicle_inventory();
$currentAdmin = current_admin_account();
$recentActivity = current_admin_recent_audit_log(5);
$activeAdminCount = active_admin_user_count();
$activeVehicleCount = count(array_filter($inventory, static fn(array $vehicle): bool => (int) ($vehicle['stock_quantity'] ?? 0) > 0));
$totalUnits = array_sum(array_map(static fn(array $vehicle): int => (int) ($vehicle['stock_quantity'] ?? 0), $inventory));
$auditEntryCount = current_admin_audit_log_count();
$inventoryStatus = array_values(array_filter($inventory, static fn(array $vehicle): bool => (int) ($vehicle['stock_quantity'] ?? 0) <= 2));
usort($inventoryStatus, static fn(array $left, array $right): int => ((int) ($left['stock_quantity'] ?? 0)) <=> ((int) ($right['stock_quantity'] ?? 0)));
$inventoryStatus = array_slice($inventoryStatus, 0, 5);
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&amp;family=Manrope:wght@400;600;700;800&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/media.css">
</head>

<body>
    <main class="admin-workbench">
        <aside class="admin-sidebar" aria-label="Seller navigation">
            <div class="admin-sidebar-top">
                <a class="admin-brand" href="dashboard.php" aria-label="Synapse Motors seller dashboard">
                    <img class="admin-brand-logo" src="../assets/images/company-logo.png" width="813" height="949"
                        alt="Synapse Motors logo">
                    <span class="admin-brand-copy">
                        <strong>Synapse Motors</strong>
                        <span>Seller control</span>
                    </span>
                </a>
                <p class="admin-sidebar-label">Seller control</p>
                <nav class="admin-side-nav">
                    <a class="admin-nav-link is-active" href="dashboard.php"><span>Dashboard</span></a>
                    <a class="admin-nav-link" href="inventory.php"><span>Inventory</span></a>
                    <a class="admin-nav-link" href="admins.php"><span>Admin users</span></a>
                    <a class="admin-nav-link" href="reports.php"><span>Reports</span></a>
                </nav>
            </div>
            <div class="admin-sidebar-bottom">
                <a class="admin-utility-link" href="../index.php">View website</a>
                <form action="../auth/logout.php" method="post" class="admin-logout-form">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <button class="admin-utility-link admin-utility-button" type="submit">Logout</button>
                </form>
            </div>
        </aside>

        <section class="admin-content">
            <div class="admin-shell">
                <header class="admin-topbar">
                    <div>
                        <p class="section-kicker">Overview</p>
                        <h1>Dashboard</h1>
                        <p class="admin-page-copy">Manage the live catalog, seller access, and activity reports from one place.</p>
                    </div>
                    <a class="admin-primary-action" href="inventory.php">Open inventory</a>
                </header>

                <section class="admin-welcome-card">
                    <p class="section-kicker">Synapse Motors seller portal</p>
                    <h2>Welcome back, <?= e((string) ($currentAdmin['name'] ?? 'System Admin')) ?></h2>
                    <p>Everything you need to control the online dealership is available from this workbench.</p>
                    <div class="admin-inline-actions">
                        <a class="button button-dark" href="inventory.php">Manage inventory</a>
                        <a class="button button-light" href="reports.php">View reports</a>
                    </div>
                </section>

                <section class="admin-stat-grid" aria-label="Seller summary">
                    <article class="admin-stat-card admin-stat-card-a">
                        <div>
                            <p class="admin-stat-label">Available stock</p>
                            <h2><?= $totalUnits ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-b">
                        <div>
                            <p class="admin-stat-label">Active listings</p>
                            <h2><?= $activeVehicleCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-c">
                        <div>
                            <p class="admin-stat-label">Admin users</p>
                            <h2><?= $activeAdminCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-d">
                        <div>
                            <p class="admin-stat-label">Logged actions</p>
                            <h2><?= $auditEntryCount ?></h2>
                        </div>
                    </article>
                </section>

                <section class="admin-dashboard-grid">
                    <article class="admin-panel admin-panel-table">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Inventory status</h2>
                                <p>Items with the lowest remaining stock.</p>
                            </div>
                            <a class="admin-panel-link" href="inventory.php">View all</a>
                        </div>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Price</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($inventoryStatus === []): ?>
                                    <tr>
                                        <td colspan="4">No low-stock inventory needs attention right now.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($inventoryStatus as $vehicle): ?>
                                    <tr>
                                        <td><?= e((string) ($vehicle['name'] ?? '')) ?></td>
                                        <td><?= e(format_vehicle_price_php((int) ($vehicle['price'] ?? 0))) ?></td>
                                        <td><?= (int) ($vehicle['stock_quantity'] ?? 0) ?></td>
                                        <td><span class="admin-status-badge admin-status-badge-alert">Low stock</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="admin-panel admin-panel-activity">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Recent activity</h2>
                                <p>Latest administrator actions from your account.</p>
                            </div>
                            <a class="admin-panel-link" href="reports.php">View report</a>
                        </div>
                        <div class="admin-activity-list">
                            <?php if ($recentActivity === []): ?>
                            <div class="admin-activity-item">
                                <div>
                                    <strong>No activity yet</strong>
                                    <p>New seller actions will appear here after the first inventory or admin update.</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach ($recentActivity as $entry): ?>
                            <div class="admin-activity-item">
                                <div>
                                    <strong><?= e((string) ($entry['action'] ?? 'Seller action')) ?></strong>
                                    <p><?= e((string) ($entry['summary'] ?? '')) ?></p>
                                    <small><?= e((string) ($entry['occurred_at'] ?? '')) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                </section>
            </div>
        </section>
    </main>
</body>

</html>
