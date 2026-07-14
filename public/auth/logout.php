<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    header('Location: ../index.php');
    exit;
}

sign_out_user();
header('Location: ../index.php');
exit;
