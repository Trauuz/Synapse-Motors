<?php

declare(strict_types=1);

$pages = [
    'public/admin/dashboard.php' => file_get_contents(__DIR__ . '/../public/admin/dashboard.php'),
    'public/admin/reports.php' => file_get_contents(__DIR__ . '/../public/admin/reports.php'),
    'public/admin/inventory.php' => file_get_contents(__DIR__ . '/../public/admin/inventory.php'),
    'public/admin/admins.php' => file_get_contents(__DIR__ . '/../public/admin/admins.php'),
];

foreach ($pages as $label => $content) {
    if ($content !== false) {
        continue;
    }

    fwrite(STDERR, "Unable to read {$label}\n");
    exit(1);
}

$expectations = [
    'dashboard page title' => [$pages['public/admin/dashboard.php'], 'Seller dashboard'],
    'dashboard quick links' => [$pages['public/admin/dashboard.php'], 'Manage inventory'],
    'reports page title' => [$pages['public/admin/reports.php'], 'Reports'],
    'reports audit section' => [$pages['public/admin/reports.php'], 'Audit log'],
    'inventory page title' => [$pages['public/admin/inventory.php'], 'Inventory management'],
    'inventory save action' => [$pages['public/admin/inventory.php'], 'action="inventory/save.php"'],
    'inventory add button' => [$pages['public/admin/inventory.php'], 'Add car'],
    'inventory edit button' => [$pages['public/admin/inventory.php'], 'Edit'],
    'inventory delete button' => [$pages['public/admin/inventory.php'], 'Delete'],
    'admins page title' => [$pages['public/admin/admins.php'], 'Admin users'],
    'admins invite action' => [$pages['public/admin/admins.php'], 'action="admins/save.php"'],
];

$failures = [];

foreach ($expectations as $label => [$content, $needle]) {
    if (str_contains($content, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing admin-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Admin page contracts passed.\n";
