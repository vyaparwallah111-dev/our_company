<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['is_paid']) || $_SESSION['is_paid'] !== true || !isset($_SESSION['onboarding']) || !is_array($_SESSION['onboarding'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Payment is not verified.']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $_SESSION['onboarding'],
]);
