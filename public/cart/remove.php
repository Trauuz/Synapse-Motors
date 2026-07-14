<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

if (!verify_csrf_token($_POST['_csrf'] ?? null) || !is_logged_in()) {
    header('Location: ../cart.php');
    exit;
}

$vehicleId = trim((string) ($_POST['vehicle_id'] ?? ''));

if ($vehicleId !== '') {
    remove_vehicle_from_cart($vehicleId);
}

header('Location: ../cart.php');
exit;
