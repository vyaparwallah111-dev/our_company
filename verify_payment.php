<?php
declare(strict_types=1);

use Razorpay\Api\Api;

session_start();

header('Content-Type: application/json');

require_once 'razorpay-php/Razorpay.php';

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['status' => 'error', 'message' => 'Only POST requests are allowed.']);
}

$config = require __DIR__ . '/config.php';
$input = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($input)) {
    respond(400, ['status' => 'error', 'message' => 'Invalid JSON payload.']);
}

$paymentId = trim((string) ($input['razorpay_payment_id'] ?? ''));
$orderId = trim((string) ($input['razorpay_order_id'] ?? ''));
$signature = trim((string) ($input['razorpay_signature'] ?? ''));
$sessionOrderId = (string) ($_SESSION['razorpay_order_id'] ?? '');

if ($paymentId === '' || $orderId === '' || $signature === '') {
    respond(400, ['status' => 'error', 'message' => 'Missing Razorpay payment verification fields.']);
}

if ($sessionOrderId === '' || !hash_equals($sessionOrderId, $orderId)) {
    session_unset();
    respond(403, ['status' => 'error', 'message' => 'Payment order mismatch.']);
}

try {
    $api = new Api($config['razorpay']['key_id'], $config['razorpay']['key_secret']);
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $signature,
    ]);

    $_SESSION['is_paid'] = true;
    $_SESSION['onboarding']['razorpayPaymentId'] = $paymentId;
    $_SESSION['onboarding']['razorpayOrderId'] = $orderId;
    $_SESSION['onboarding']['paidAt'] = date(DATE_ATOM);

    respond(200, ['status' => 'success']);
} catch (Throwable $error) {
    session_unset();
    respond(403, ['status' => 'error', 'message' => 'Payment signature verification failed.']);
}
