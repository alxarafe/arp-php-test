<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use ArpPhpTest\Application;

header('Content-Type: application/json; charset=utf-8');
if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) !== '/health') {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    return;
}
try {
    $manager = Application::modules();
    $manager->boot();
    echo json_encode(['status' => 'ok', 'modules' => array_column($manager->list(), 'state', 'id')], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(503);
    echo json_encode(['status' => 'unavailable']);
}
