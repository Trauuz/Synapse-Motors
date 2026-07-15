<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; ?>
<!doctype html>
<html lang="en">

<?php
require_admin();
record_admin_activity('Viewed inventory management', 'Opened the seller inventory management page.');

$inventory = synapse_vehicle_inventory();
$feedback = flash_pull('admin_feedback', null);
$createFormData = flash_pull('admin_inventory_create_form', []);
$showCreateForm = (($_GET['create'] ?? '') === '1');
$editFormData = flash_pull('admin_inventory_edit_form', []);
$editVehicleId = trim((string) ($_GET['edit'] ?? ($editFormData['vehicle_id'] ?? '')));
$selectedVehicle = $editVehicleId !== '' ? find_vehicle_by_id($editVehicleId) : null;
$editVehicle = is_array($selectedVehicle) ? array_merge($selectedVehicle, is_array($editFormData) ? $editFormData : []) : null;
$showEditModal = is_array($editVehicle);
$totalUnits = array_sum(array_map(static fn(array $vehicle): int => (int) ($vehicle['stock_quantity'] ?? 0), $inventory));
$activeVehicleCount = count(array_filter($inventory, static fn(array $vehicle): bool => (int) ($vehicle['stock_quantity'] ?? 0) > 0));
$lowStockCount = count(array_filter($inventory, static fn(array $vehicle): bool => (int) ($vehicle['stock_quantity'] ?? 0) <= 2));
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory management</title>
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
                    <a class="admin-nav-link is-active" href="inventory.php"><span>Inventory</span></a>
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
                        <p class="section-kicker">Catalog</p>
                        <h1>Inventory</h1>
                        <p class="admin-page-copy">Adjust pricing, stock counts, and selling status for every live
                            vehicle.</p>
                    </div>
                    <a class="admin-primary-action" href="dashboard.php">Back to dashboard</a>
                </header>

                <?php if (is_array($feedback)): ?>
                <p class="admin-feedback admin-feedback-<?= e((string) ($feedback['tone'] ?? 'info')) ?>">
                    <?= e((string) ($feedback['message'] ?? '')) ?></p>
                <?php endif; ?>

                <section class="admin-stat-grid" aria-label="Inventory summary">
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
                            <p class="admin-stat-label">Low stock items</p>
                            <h2><?= $lowStockCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-d">
                        <div>
                            <p class="admin-stat-label">Updates ready</p>
                            <h2><?= count($inventory) ?></h2>
                        </div>
                    </article>
                </section>

                <section class="admin-panel admin-panel-table">
                    <div class="admin-panel-head">
                        <div>
                            <h2>Inventory editor</h2>
                            <p>Review each listing, adjust pricing and stock, then save updates inline.</p>
                        </div>
                        <a class="admin-primary-action" href="inventory.php?create=1">Add car</a>
                    </div>
                    <?php if ($showCreateForm): ?>
                    <form action="inventory/save.php" method="post"
                        class="admin-inline-form admin-inline-form-inventory">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="inventory_action" value="create">
                        <label class="checkout-field">
                            <span>Vehicle name</span>
                            <input type="text" name="name" value="<?= e((string) ($createFormData['name'] ?? '')) ?>"
                                placeholder="Apex GT-R" required>
                        </label>
                        <label class="checkout-field">
                            <span>Collection</span>
                            <input type="text" name="collection"
                                value="<?= e((string) ($createFormData['collection'] ?? '')) ?>"
                                placeholder="Coast road edit">
                        </label>
                        <label class="checkout-field">
                            <span>Detail</span>
                            <input type="text" name="detail"
                                value="<?= e((string) ($createFormData['detail'] ?? '')) ?>"
                                placeholder="Performance coupe - Petrol" required>
                        </label>
                        <label class="checkout-field">
                            <span>Price (PHP)</span>
                            <input type="number" min="0" name="price"
                                value="<?= e((string) ($createFormData['price'] ?? '')) ?>" placeholder="128000">
                        </label>
                        <label class="checkout-field">
                            <span>Stock</span>
                            <input type="number" min="0" name="stock_quantity"
                                value="<?= e((string) ($createFormData['stock_quantity'] ?? '')) ?>" placeholder="4">
                        </label>
                        <label class="checkout-field">
                            <span>Status</span>
                            <input type="text" name="availability"
                                value="<?= e((string) ($createFormData['availability'] ?? 'Available now')) ?>"
                                placeholder="Available now">
                        </label>
                        <button class="button button-dark" type="submit">Save new car</button>
                    </form>
                    <?php endif; ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table admin-table-editor">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Collection</th>
                                    <th>Current state</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory as $vehicle): ?>
                                <tr>
                                    <td>
                                        <div class="admin-table-meta">
                                            <strong><?= e((string) ($vehicle['name'] ?? '')) ?></strong>
                                            <p><?= e((string) ($vehicle['detail'] ?? '')) ?></p>
                                        </div>
                                    </td>
                                    <td><?= e((string) ($vehicle['collection'] ?? '')) ?></td>
                                    <td>
                                        <div class="admin-table-meta">
                                            <span
                                                class="admin-status-badge <?= ((int) ($vehicle['stock_quantity'] ?? 0) <= 2) ? 'admin-status-badge-alert' : 'admin-status-badge-calm' ?>">
                                                <?= ((int) ($vehicle['stock_quantity'] ?? 0) <= 2) ? 'Low stock' : e((string) ($vehicle['availability'] ?? 'Available')) ?>
                                            </span>
                                            <p>Price: ₱<?= number_format((float) ($vehicle['price'] ?? 0), 0) ?></p>
                                            <p>Remaining: <?= (int) ($vehicle['stock_quantity'] ?? 0) ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="button button-light admin-table-action-button" type="button"
                                                data-admin-edit-trigger
                                                data-vehicle-id="<?= e((string) ($vehicle['id'] ?? '')) ?>"
                                                data-vehicle-name="<?= e((string) ($vehicle['name'] ?? '')) ?>"
                                                data-vehicle-collection="<?= e((string) ($vehicle['collection'] ?? '')) ?>"
                                                data-vehicle-detail="<?= e((string) ($vehicle['detail'] ?? '')) ?>"
                                                data-vehicle-price="<?= e((string) ((int) ($vehicle['price'] ?? 0))) ?>"
                                                data-vehicle-stock="<?= e((string) ((int) ($vehicle['stock_quantity'] ?? 0))) ?>"
                                                data-vehicle-availability="<?= e((string) ($vehicle['availability'] ?? '')) ?>">
                                                Edit
                                            </button>
                                            <form action="inventory/save.php" method="post" class="admin-delete-form">
                                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="inventory_action" value="delete">
                                                <input type="hidden" name="vehicle_id" value="<?= e((string) ($vehicle['id'] ?? '')) ?>">
                                                <button class="button button-dark admin-table-action-button" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>
    </main>
    <div class="admin-editor-modal-shell" data-admin-editor-modal data-admin-editor-open-on-load="<?= $showEditModal ? 'true' : 'false' ?>"<?= $showEditModal ? '' : ' hidden' ?>>
        <div class="admin-editor-modal-backdrop" data-admin-editor-close></div>
        <section class="admin-editor-modal" role="dialog" aria-modal="true" aria-labelledby="admin-editor-title">
            <button class="admin-editor-modal-close" type="button" aria-label="Close edit vehicle modal" data-admin-editor-close>&times;</button>
            <div class="admin-editor-modal-copy">
                <p class="section-kicker">Inventory editor</p>
                <h2 id="admin-editor-title">Edit vehicle</h2>
                <p>Update the car details here, then save the listing back into inventory.</p>
            </div>
            <form action="inventory/save.php" method="post" class="admin-editor-modal-form">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="inventory_action" value="update">
                <input type="hidden" name="vehicle_id" value="<?= e((string) ($editVehicle['id'] ?? '')) ?>" data-admin-editor-field="vehicle_id">
                <label class="checkout-field">
                    <span>Vehicle name</span>
                    <input type="text" name="name" value="<?= e((string) ($editVehicle['name'] ?? '')) ?>" data-admin-editor-field="name" required>
                </label>
                <label class="checkout-field">
                    <span>Collection</span>
                    <input type="text" name="collection" value="<?= e((string) ($editVehicle['collection'] ?? '')) ?>" data-admin-editor-field="collection">
                </label>
                <label class="checkout-field checkout-field-wide">
                    <span>Detail</span>
                    <input type="text" name="detail" value="<?= e((string) ($editVehicle['detail'] ?? '')) ?>" data-admin-editor-field="detail" required>
                </label>
                <label class="checkout-field">
                    <span>Price (PHP)</span>
                    <input type="number" min="0" name="price" value="<?= e((string) ($editVehicle['price'] ?? '')) ?>" data-admin-editor-field="price">
                </label>
                <label class="checkout-field">
                    <span>Stock</span>
                    <input type="number" min="0" name="stock_quantity" value="<?= e((string) ($editVehicle['stock_quantity'] ?? '')) ?>" data-admin-editor-field="stock_quantity">
                </label>
                <label class="checkout-field">
                    <span>Status</span>
                    <input type="text" name="availability" value="<?= e((string) ($editVehicle['availability'] ?? 'Available now')) ?>" data-admin-editor-field="availability">
                </label>
                <div class="admin-editor-modal-actions">
                    <button class="button button-light" type="button" data-admin-editor-close>Cancel</button>
                    <button class="button button-dark" type="submit">Save changes</button>
                </div>
            </form>
        </section>
    </div>
    <script src="../assets/js/admin-inventory.js" defer></script>
</body>

</html>
