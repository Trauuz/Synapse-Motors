<?php require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; ?>
<!doctype html>
<html lang="en">

<?php
require_admin();
record_admin_activity('Viewed admin users', 'Opened the administrator management page.');

$admins = admin_user_accounts();
$feedback = flash_pull('admin_feedback', null);
$currentAdmin = current_admin_account();
$currentAdminId = is_array($currentAdmin) ? (string) ($currentAdmin['id'] ?? '') : '';
$currentAdminDisplayName = trim((string) ($currentAdmin['name'] ?? 'Admin'));
$currentAdminFirstName = $currentAdminDisplayName === '' ? 'Admin' : explode(' ', $currentAdminDisplayName)[0];
$activeAdminCount = active_admin_user_count();
$pendingAdminCount = pending_admin_user_count();
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin users</title>
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
                    <a class="admin-nav-link is-active" href="admins.php"><span>Admin users</span></a>
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
                        <p class="section-kicker">Team</p>
                        <h1>Admin users</h1>
                        <p class="admin-page-copy">Invite administrators, confirm verification state, and manage active access.</p>
                    </div>
                    <a class="admin-primary-action" href="dashboard.php">Back to dashboard</a>
                </header>

                <?php if (is_array($feedback)): ?>
                <p class="admin-feedback admin-feedback-<?= e((string) ($feedback['tone'] ?? 'info')) ?>"><?= e((string) ($feedback['message'] ?? '')) ?></p>
                <?php endif; ?>

                <section class="admin-stat-grid" aria-label="Admin summary">
                    <article class="admin-stat-card admin-stat-card-a">
                        <div>
                            <p class="admin-stat-label">Admin users</p>
                            <h2><?= admin_user_count() ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-b">
                        <div>
                            <p class="admin-stat-label">Active access</p>
                            <h2><?= $activeAdminCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-c">
                        <div>
                            <p class="admin-stat-label">Pending verification</p>
                            <h2><?= $pendingAdminCount ?></h2>
                        </div>
                    </article>
                    <article class="admin-stat-card admin-stat-card-d">
                        <div>
                            <p class="admin-stat-label">Signed in as</p>
                            <h2><?= e($currentAdminFirstName) ?></h2>
                        </div>
                    </article>
                </section>

                <section class="admin-dashboard-grid">
                    <article class="admin-panel">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Invite administrators</h2>
                                <p>Grant seller access to another administrator account.</p>
                            </div>
                        </div>
                        <form action="admins/save.php" method="post" class="admin-inline-form">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="admin_action" value="invite">
                            <label class="checkout-field">
                                <span>Name</span>
                                <input type="text" name="name" required>
                            </label>
                            <label class="checkout-field">
                                <span>Email</span>
                                <input type="email" name="email" required>
                            </label>
                            <button class="button button-dark" type="submit">Send invite</button>
                        </form>
                    </article>

                    <article class="admin-panel admin-panel-activity">
                        <div class="admin-panel-head">
                            <div>
                                <h2>Access guidance</h2>
                                <p>Keep the seller bench tight and auditable.</p>
                            </div>
                        </div>
                        <div class="admin-activity-list">
                            <div class="admin-activity-item">
                                <div>
                                    <strong>Verify ownership</strong>
                                    <p>Use email verification to confirm that every admin owns the mailbox attached to seller access.</p>
                                </div>
                            </div>
                            <div class="admin-activity-item">
                                <div>
                                    <strong>Disable stale access</strong>
                                    <p>Switch access to disabled when a seller no longer needs control of inventory or reports.</p>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="admin-panel admin-panel-table">
                    <div class="admin-panel-head">
                        <div>
                            <h2>Status management</h2>
                            <p>Review verification, update names, and manage access state.</p>
                        </div>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Email</th>
                                    <th>Email verified</th>
                                    <th>Status</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= e((string) ($admin['name'] ?? '')) ?><?= ($currentAdminId !== '' && $currentAdminId === (string) ($admin['id'] ?? '')) ? ' (You)' : '' ?></td>
                                    <td><?= e((string) ($admin['email'] ?? '')) ?></td>
                                    <td>
                                        <span class="admin-status-badge <?= (($admin['email_verified'] ?? false) === true) ? 'admin-status-badge-calm' : 'admin-status-badge-alert' ?>">
                                            <?= (($admin['email_verified'] ?? false) === true) ? 'Verified' : 'Pending' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-status-badge <?= (($admin['access_status'] ?? 'active') === 'active') ? 'admin-status-badge-calm' : 'admin-status-badge-muted' ?>">
                                            <?= e((string) ($admin['access_status'] ?? 'active')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="admins/save.php" method="post" class="admin-table-form">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="admin_action" value="update">
                                            <input type="hidden" name="admin_id" value="<?= e((string) ($admin['id'] ?? '')) ?>">
                                            <input type="text" name="name" value="<?= e((string) ($admin['name'] ?? '')) ?>">
                                            <select name="access_status">
                                                <option value="active"<?= (($admin['access_status'] ?? 'active') === 'active') ? ' selected' : '' ?>>active</option>
                                                <option value="disabled"<?= (($admin['access_status'] ?? '') === 'disabled') ? ' selected' : '' ?>>disabled</option>
                                            </select>
                                            <button class="button button-dark" type="submit">Save</button>
                                        </form>
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
</body>

</html>
