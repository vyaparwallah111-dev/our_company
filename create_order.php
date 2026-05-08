<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Install SDK first:
// composer require razorpay/razorpay
//
// Keep the secret on the server only. Do not place it in onboarding.html.
$keyId = getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_REPLACE_WITH_YOUR_KEY_ID';
$keySecret = getenv('RAZORPAY_KEY_SECRET') ?: 'REPLACE_WITH_YOUR_KEY_SECRET';

function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Only POST requests are allowed.',
    ]);
}

if (strpos($keyId, 'REPLACE_WITH') !== false || strpos($keySecret, 'REPLACE_WITH') !== false) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Razorpay API keys are not configured on the server.',
    ]);
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Razorpay PHP SDK is not installed. Run composer require razorpay/razorpay.',
    ]);
}

require $autoloadPath;

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody ?: '', true);

if (!is_array($input)) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON request.',
    ]);
}

$amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);

if ($amount === false || $amount <= 0) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'A valid payment amount is required.',
    ]);
}

$amountInPaise = (int) round($amount * 100);

if ($amountInPaise < 100) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Minimum payable amount is Rs. 1.',
    ]);
}

$clinicName = trim((string) ($input['clinic_name'] ?? 'Vyapar Wallah Client'));
$doctorName = trim((string) ($input['doctor_name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$mobile = trim((string) ($input['mobile'] ?? ''));
$services = trim((string) ($input['services'] ?? ''));

try {
    $api = new \Razorpay\Api\Api($keyId, $keySecret);

    $order = $api->order->create([
            'receipt' => 'vw_' . time() . '_' . bin2hex(random_bytes(4)),
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'payment_capture' => 1,
            'notes' => [
            'clinic_name' => substr($clinicName, 0, 120),
            'doctor_name' => substr($doctorName, 0, 120),
            'email' => substr($email, 0, 120),
            'mobile' => substr($mobile, 0, 20),
            'services' => substr($services, 0, 240),
        ],
    ]);

    jsonResponse(200, [
        'success' => true,
        'key_id' => $keyId,
        'order_id' => $order['id'],
        'amount' => $order['amount'],
        'currency' => $order['currency'],
    ]);
} catch (Throwable $error) {
    error_log('Razorpay order creation failed: ' . $error->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Unable to create Razorpay order. Please try again.',
    ]);
}
