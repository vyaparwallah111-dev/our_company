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
    respond(405, ['error' => 'Only POST requests are allowed.']);
}

$config = require __DIR__ . '/config.php';
$input = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($input)) {
    respond(400, ['error' => 'Invalid JSON payload.']);
}

$doctorName = trim((string) ($input['doctorName'] ?? ''));
$clinicName = trim((string) ($input['clinicName'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$setupCost = (float) ($input['setupCost'] ?? $input['oneTimeCost'] ?? 0);
$monthlyCost = (float) ($input['monthlyCost'] ?? 0);
$paymentOption = trim((string) ($input['paymentOption'] ?? 'advance90'));
$customAmount = (float) ($input['customAmount'] ?? 0);
$finalAmount = 0.0;

if ($doctorName === '' || $clinicName === '') {
    respond(400, ['error' => 'Doctor name and clinic name are required.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, ['error' => 'A valid client email is required.']);
}

if ($setupCost <= 0 || $monthlyCost <= 0) {
    respond(400, ['error' => 'Setup cost and monthly cost must be greater than zero.']);
}

if ($paymentOption === 'advance90') {
    $finalAmount = (float) round($setupCost * 0.9);
} elseif ($paymentOption === 'other') {
    $finalAmount = $customAmount;
} else {
    respond(400, ['error' => 'Invalid payment option selected.']);
}

if ($finalAmount <= 0) {
    respond(400, ['error' => 'Order amount must be greater than zero.']);
}

$amountInPaise = (int) round($finalAmount * 100);

if ($amountInPaise < 100) {
    respond(400, ['error' => 'Order amount must be at least INR 1.']);
}

// Store ALL client details in the session securely BEFORE creating the Razorpay order
$_SESSION['is_paid'] = false;
$_SESSION['calculatedAdvance'] = $finalAmount;
$_SESSION['onboarding'] = [
    'doctorName' => $doctorName,
    'clinicName' => $clinicName,
    'email' => $email,
    'mobile' => trim((string) ($input['mobile'] ?? '')),
    'whatsapp' => trim((string) ($input['whatsapp'] ?? '')),
    'websiteLink' => trim((string) ($input['websiteLink'] ?? '')),
    'gmbLink' => trim((string) ($input['gmbLink'] ?? '')),
    'fbLink' => trim((string) ($input['fbLink'] ?? '')),
    'instaLink' => trim((string) ($input['instaLink'] ?? '')),
    'services' => is_array($input['services'] ?? null) ? array_values($input['services']) : [],
    'servicesText' => trim((string) ($input['servicesText'] ?? '')),
    'startDate' => trim((string) ($input['startDate'] ?? '')),
    'endDate' => trim((string) ($input['endDate'] ?? '')),
    'oneTimeCost' => $setupCost,
    'monthlyCost' => $monthlyCost,
    'paymentMethod' => 'Razorpay Checkout',
    'paymentOption' => $paymentOption,
    'customAmount' => $paymentOption === 'other' ? $customAmount : 0,
    'calculatedAdvance' => $finalAmount,
    'paidAmount' => $finalAmount,
    'paymentDate' => trim((string) ($input['paymentDate'] ?? '')),
    'meetDay' => trim((string) ($input['meetDay'] ?? '')),
    'meetTime' => trim((string) ($input['meetTime'] ?? '')),
    'signatureImage' => (string) ($input['signatureImage'] ?? ''),
    'razorpayOrderId' => '', // Will be updated after creation
    'razorpayPaymentId' => '',
    'submittedAt' => date(DATE_ATOM),
];

try {
    $api = new Api($config['razorpay']['key_id'], $config['razorpay']['key_secret']);

    $razorpayOrder = $api->order->create([
        'receipt' => 'vw_rcpt_' . time(),
        'amount'  => $amountInPaise,
        'currency' => 'INR',
        'payment_capture' => 1,
        'notes' => [
            'clinic_name' => $clinicName,
            'doctor_name' => $doctorName,
        ],
    ]);

    // Update session with the generated Order ID
    $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
    $_SESSION['onboarding']['razorpayOrderId'] = $razorpayOrder['id'];

    respond(200, [
        'order_id' => $razorpayOrder['id'],
        'amount'   => $amountInPaise,
        'key_id'   => $config['razorpay']['key_id'],
    ]);
} catch (Throwable $error) {
    respond(500, ['error' => 'Razorpay Error: ' . $error->getMessage()]);
}
