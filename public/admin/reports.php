<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; ?>
<!doctype html>
<html lang="en">

<?php
require_admin();
record_admin_activity('Viewed reports', 'Reviewed inventory counts and personal audit log.');

$inventory = synapse_vehicle_inventory();
$requestedAuditPage = filter_input(INPUT_GET, 'audit_page', FILTER_VALIDATE_INT);

if (!is_int($requestedAuditPage) || $requestedAuditPage < 1) {
    $requestedAuditPage = isset($_GET['audit_page']) ? (int) $_GET['audit_page'] : 1;
}

$auditLogPage = current_admin_audit_log_page($requestedAuditPage, 10);
$auditEntries = $auditLogPage['entries'];
$sortedInventory = $inventory;
usort($sortedInventory, static fn(array $left, array $right): int => ((int) ($left['stock_quantity'] ?? 0)) <=> ((int) ($right['stock_quantity'] ?? 0)));
$currentAdmin = current_admin_account();
$totalUnits = array_sum(array_map(static fn(array $vehicle): int => (int) ($vehicle['stock_quantity'] ?? 0), $inventory));
$lowStockCount = count(array_filter($inventory, static fn(array $vehicle): bool => (int) ($vehicle['stock_quantity'] ?? 0) <= 2));
$auditEntryCount = (int) ($auditLogPage['total_entries'] ?? 0);
$auditPageCount = (int) ($auditLogPage['total_pages'] ?? 1);
$currentAuditPage = (int) ($auditLogPage['current_page'] ?? 1);
$previousAuditPage = max(1, $currentAuditPage - 1);
$nextAuditPage = min($auditPageCount, $currentAuditPage + 1);
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports</title>
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
                    <a class="admin-nav-link" href="dashboard.php"><span>Dashboard</span></a>
                    <a class="admin-nav-link" href="inventory.php"><span>Inventory</span></a>
                    <a class="admin-nav-link" href="admins.php"><span>Admin users</span></a>
                    <a class="admin-nav-link is-active" href="reports.php"><span>Reports</span></a>
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
                        <h1>Reports</h1>
                        <p class="admin-page-copy">Track inventory pressure and review the audit trail for <?= e((string) ($currentAdmin['name'] ?? 'this admin')) ?>.</p>
                    </div>
                    <a class="admin-primary-action" href="dashboard.php">Back to dashboard</a>
                </header>

                <section class="admin-stat-grid" aria-label="Report summary">
                    <article class="admin-stat-card admin-stat-card-a">
                        <div>
                            <p class="admin-stat-label">Available stock</p>
                            <h2><?= $totalUnits ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-b">
                        <div>
                            <p class="admin-stat-label">Low stock items</p>
                            <h2><?= $lowStockCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-c">
                        <div>
                            <p class="admin-stat-label">Audit entries</p>
                            <h2><?= $auditEntryCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-d">
                        <div>
                            <p class="admin-stat-label">Account</p>
                            <h2><?= e((string) ($currentAdmin['access_status'] ?? 'active')) ?></h2>
                        </div>
                    </article>
                </section>

                <section class="admin-dashboard-grid admin-dashboard-grid-stack">
                    <article class="admin-panel admin-panel-table">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Inventory status</h2>
                                <p>Remaining units across the current catalog.</p>
                            </div>
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
                                    <?php foreach ($sortedInventory as $vehicle): ?>
                                    <tr>
                                        <td><?= e((string) ($vehicle['name'] ?? '')) ?></td>
                                        <td><?= e(format_vehicle_price_php((int) ($vehicle['price'] ?? 0))) ?></td>
                                        <td><?= (int) ($vehicle['stock_quantity'] ?? 0) ?></td>
                                        <td>
                                            <?php if ((int) ($vehicle['stock_quantity'] ?? 0) <= 2): ?>
                                            <span class="admin-status-badge admin-status-badge-alert">Low stock</span>
                                            <?php else: ?>
                                            <span class="admin-status-badge admin-status-badge-calm"><?= e((string) ($vehicle['availability'] ?? 'Available')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="admin-panel admin-panel-activity">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Audit log</h2>
                                <p>Every recorded seller action for the current account, shown 10 activity items at a time.</p>
                            </div>
                            <span class="admin-panel-meta">Page <?= $currentAuditPage ?> of <?= $auditPageCount ?></span>
                        </div>
                        <div class="admin-activity-list">
                            <?php if ($auditEntries === []): ?>
                            <div class="admin-activity-item">
                                <div>
                                    <strong>No seller activity yet</strong>
                                    <p>Open inventory or admin tools to begin generating an audit trail.</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach ($auditEntries as $entry): ?>
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
                        <?php if ($auditPageCount > 1): ?>
                        <nav class="admin-pagination" aria-label="Audit log pagination">
                            <?php if ((bool) ($auditLogPage['has_previous_page'] ?? false)): ?>
                            <a class="admin-pagination-link" href="?audit_page=<?= $previousAuditPage ?>">Previous</a>
                            <?php else: ?>
                            <span class="admin-pagination-link is-disabled" aria-disabled="true">Previous</span>
                            <?php endif; ?>
                            <div class="admin-pagination-pages" aria-label="Audit log pages">
                                <?php for ($pageNumber = 1; $pageNumber <= $auditPageCount; $pageNumber++): ?>
                                <?php if ($pageNumber === $currentAuditPage): ?>
                                <span class="admin-pagination-link is-current" aria-current="page"><?= $pageNumber ?></span>
                                <?php else: ?>
                                <a class="admin-pagination-link" href="?audit_page=<?= $pageNumber ?>"><?= $pageNumber ?></a>
                                <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <?php if ((bool) ($auditLogPage['has_next_page'] ?? false)): ?>
                            <a class="admin-pagination-link" href="?audit_page=<?= $nextAuditPage ?>">Next</a>
                            <?php else: ?>
                            <span class="admin-pagination-link is-disabled" aria-disabled="true">Next</span>
                            <?php endif; ?>
                        </nav>
                        <?php endif; ?>
                    </article>
                </section>
            </div>
        </section>
    </main>
</body>

</html>
