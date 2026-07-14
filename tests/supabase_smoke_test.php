<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$client = new SupabaseClient();
$health = supabase_health_check();
$usersProbe = $client->getRest(supabase_users_table(), 'select=id&limit=1');

echo json_encode([
    'health' => [
        'ok' => $health['ok'],
        'status' => $health['status'],
        'error' => $health['error'],
    ],
    'users_probe' => [
        'ok' => $usersProbe['ok'],
        'status' => $usersProbe['status'],
        'error' => $usersProbe['error'],
        'has_array_data' => is_array($usersProbe['data']),
    ],
], JSON_PRETTY_PRINT), PHP_EOL;
