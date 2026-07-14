<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../inventory.php');
    exit;
}

if (!verify_csrf_token($_POST['_csrf'] ?? null) || !is_logged_in()) {
    header('Location: ../inventory.php');
    exit;
}

$vehicleId = trim((string) ($_POST['vehicle_id'] ?? ''));
$redirectTarget = basename((string) parse_url((string) ($_POST['redirect_to'] ?? 'inventory.php'), PHP_URL_PATH));
$allowedTargets = ['inventory.php', 'cart.php'];

if (!in_array($redirectTarget, $allowedTargets, true)) {
    $redirectTarget = 'inventory.php';
}

if ($vehicleId !== '') {
    add_vehicle_to_cart($vehicleId);
}

header('Location: ../' . $redirectTarget);
exit;
