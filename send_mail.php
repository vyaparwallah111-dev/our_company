<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

session_start();

header('Content-Type: application/json');

$config = require __DIR__ . '/config.php';

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function respond(string $status, string $message = ''): void
{
    $payload = ['status' => $status];
    if ($message !== '') {
        $payload['message'] = $message;
    }

    echo json_encode($payload);
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function moneyValue(mixed $value): float
{
    $normalized = preg_replace('/[^\d.-]/', '', (string) $value);
    return is_numeric($normalized) ? (float) $normalized : 0.0;
}

function formatMoney(float $value): string
{
    return number_format($value, 0, '.', ',');
}

function sessionString(array $source, string $key, string $fallback = ''): string
{
    return trim((string) ($source[$key] ?? $fallback));
}

function logWhatsAppError(string $message, array $context = []): void
{
    $entry = [
        'timestamp' => date(DATE_ATOM),
        'message' => $message,
        'context' => $context,
    ];

    error_log(json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, __DIR__ . '/fast2sms_whatsapp_errors.log');
}

function writeWhatsAppDebugLog(string $phone, int $statusCode, string $response, string $curlError, array $payload = []): void
{
    unset($payload['authorization']);

    $line = sprintf(
        "[%s] Phone: %s | Status: %d | Response: %s | cURL Error: %s | Payload: %s%s",
        date('Y-m-d H:i:s'),
        $phone,
        $statusCode,
        $response !== '' ? $response : 'EMPTY',
        $curlError !== '' ? $curlError : 'NONE',
        json_encode($payload, JSON_UNESCAPED_SLASHES),
        PHP_EOL
    );

    @file_put_contents(__DIR__ . '/whatsapp_debug_log.txt', $line, FILE_APPEND | LOCK_EX);
}

function sessionValue(string $sessionKey, array $fallbackSource, string $fallbackKey, string $default = ''): string
{
    if (isset($_SESSION[$sessionKey])) {
        return trim((string) $_SESSION[$sessionKey]);
    }

    return sessionString($fallbackSource, $fallbackKey, $default);
}

function sanitizeIndianWhatsAppNumber(string $phoneNumber): string
{
    $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';

    if (strlen($digits) === 10) {
        return '91' . $digits;
    }

    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        return '91' . substr($digits, 1);
    }

    return $digits;
}

function sendFast2SmsWhatsAppTemplate(string $phoneNumber, string $doctorName, string $clinicName): void
{
    if (!defined('FAST2SMS_API_KEY') || FAST2SMS_API_KEY === '' || FAST2SMS_API_KEY === 'your_fast2sms_api_key_here') {
        logWhatsAppError('Fast2SMS API key is not configured.');
        return;
    }

    if (!defined('WHATSAPP_TEMPLATE_NAME') || WHATSAPP_TEMPLATE_NAME === '' || WHATSAPP_TEMPLATE_NAME === 'your_approved_template_name_here') {
        logWhatsAppError('Fast2SMS WhatsApp template name is not configured.');
        return;
    }

    $numbers = sanitizeIndianWhatsAppNumber($phoneNumber);
    $vars = $doctorName . '|' . $clinicName;

    if ($numbers === '') {
        logWhatsAppError('Fast2SMS WhatsApp number is missing.', ['doctorName' => $doctorName, 'clinicName' => $clinicName]);
        writeWhatsAppDebugLog('', 0, 'Request not sent: phone number is missing.', '', [
            'template_name' => WHATSAPP_TEMPLATE_NAME,
            'variables_values' => $vars,
        ]);
        return;
    }

    if (!function_exists('curl_init')) {
        logWhatsAppError('PHP cURL extension is not available.');
        writeWhatsAppDebugLog($numbers, 0, 'Request not sent: PHP cURL extension is not available.', '', [
            'template_name' => WHATSAPP_TEMPLATE_NAME,
            'variables_values' => $vars,
        ]);
        return;
    }

    $messageId = defined('FAST2SMS_WHATSAPP_MESSAGE_ID') ? trim((string) FAST2SMS_WHATSAPP_MESSAGE_ID) : '';
    $phoneNumberId = defined('FAST2SMS_WHATSAPP_PHONE_NUMBER_ID') ? trim((string) FAST2SMS_WHATSAPP_PHONE_NUMBER_ID) : '';

    $payload = [
        'authorization' => FAST2SMS_API_KEY,
        'message_id' => $messageId,
        'phone_number_id' => $phoneNumberId,
        'numbers' => $numbers,
        'variables_values' => $vars,
    ];

    if ($messageId === '' || $phoneNumberId === '') {
        logWhatsAppError('Fast2SMS WhatsApp message_id or phone_number_id is not configured.', [
            'messageIdConfigured' => $messageId !== '',
            'phoneNumberIdConfigured' => $phoneNumberId !== '',
        ]);
        writeWhatsAppDebugLog($numbers, 0, 'Request not sent: configure FAST2SMS_WHATSAPP_MESSAGE_ID and FAST2SMS_WHATSAPP_PHONE_NUMBER_ID.', '', $payload);
        return;
    }

    $endpoint = 'https://www.fast2sms.com/dev/whatsapp?' . http_build_query($payload);
    $curl = curl_init($endpoint);

    if ($curl === false) {
        logWhatsAppError('Unable to initialize cURL for Fast2SMS WhatsApp request.');
        writeWhatsAppDebugLog($numbers, 0, 'Request not sent: unable to initialize cURL.', '', $payload);
        return;
    }

    curl_setopt_array($curl, [
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    writeWhatsAppDebugLog($numbers, $httpCode, is_string($response) ? $response : '', $curlError, [
        'endpoint' => 'https://www.fast2sms.com/dev/whatsapp',
        'message_id' => $messageId,
        'phone_number_id' => $phoneNumberId,
        'numbers' => $numbers,
        'variables_values' => $vars,
        'template_name' => WHATSAPP_TEMPLATE_NAME,
    ]);

    if ($response === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300) {
        logWhatsAppError('Fast2SMS WhatsApp API request failed.', [
            'httpCode' => $httpCode,
            'curlError' => $curlError,
            'response' => is_string($response) ? substr($response, 0, 1000) : '',
            'numbers' => $numbers,
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond('error', 'Only POST requests are allowed.');
}

if (!isset($_SESSION['is_paid']) || $_SESSION['is_paid'] !== true || !isset($_SESSION['onboarding']) || !is_array($_SESSION['onboarding'])) {
    http_response_code(403);
    respond('error', 'Unauthorized payment session.');
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody ?: '', true);

if (!is_array($data)) {
    http_response_code(400);
    respond('error', 'Invalid JSON payload.');
}

$onboarding = $_SESSION['onboarding'];
$email = sessionString($onboarding, 'email');
$rawName = sessionString($onboarding, 'doctorName', 'Doctor');
$rawClinic = sessionString($onboarding, 'clinicName', 'your clinic');
$mobile = sessionString($onboarding, 'mobile', 'N/A');
$whatsapp = sessionString($onboarding, 'whatsapp', 'N/A');
$txnId = sessionString($onboarding, 'razorpayPaymentId', 'N/A');
$setupCost = moneyValue($onboarding['oneTimeCost'] ?? 0);
$monthlyCost = moneyValue($onboarding['monthlyCost'] ?? 0);
$advancePaid = moneyValue($onboarding['paidAmount'] ?? 0);
$balanceDue = $setupCost - $advancePaid;
$safeSetupCost = formatMoney($setupCost);
$safeMonthlyCost = formatMoney($monthlyCost);
$safeAdvancePaid = formatMoney($advancePaid);
$safeBalanceDue = formatMoney($balanceDue);
$rxPdfRaw = (string) ($data['rxPdf'] ?? '');
$invPdfRaw = (string) ($data['invoicePdf'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    respond('error', 'A valid recipient email is required.');
}

if ($rxPdfRaw === '' || $invPdfRaw === '') {
    http_response_code(400);
    respond('error', 'One or more PDF attachments are missing.');
}

function decodePdf(string $raw): string|false
{
    $base64 = trim($raw);
    if (strpos($base64, ',') !== false) {
        $parts = explode(',', $base64, 2);
        $base64 = $parts[1] ?? '';
    }
    $base64 = str_replace(' ', '+', $base64);
    $decoded = base64_decode($base64, true);

    if ($decoded === false || strncmp($decoded, '%PDF', 4) !== 0) {
        return false;
    }

    return $decoded;
}

$decodedRx = decodePdf($rxPdfRaw);
$decodedInv = decodePdf($invPdfRaw);

if ($decodedRx === false || $decodedInv === false) {
    http_response_code(400);
    respond('error', 'Invalid PDF data provided.');
}

$name = h($rawName);
$clinic = h($rawClinic);
$safeEmail = h($email);
$safeMobile = h($mobile);
$safeWhatsapp = h($whatsapp);
$safeTxnId = h($txnId);

$mail = null;

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $config['smtp']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp']['username'];
    $mail->Password = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['secure'] === 'tls'
        ? PHPMailer::ENCRYPTION_STARTTLS
        : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int) $config['smtp']['port'];

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
    $mail->addAddress($email, $rawName);
    $mail->addReplyTo($config['smtp']['reply_to_email'], $config['smtp']['reply_to_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Welcome to Vyapar Wallah - Onboarding Confirmed';
    $mail->Body = <<<HTML
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #f8fafc; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <div style="background-color: #0A4C95; padding: 30px 20px; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 900; letter-spacing: -0.5px;">Vyapar<span style="color: #F37021;">Wallah.</span></h1>
        <p style="color: #cbd5e1; margin: 5px 0 0 0; font-size: 14px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase;">Premium Healthcare Marketing</p>
    </div>
    <div style="padding: 35px 30px; color: #1e293b; line-height: 1.6;">
        <h2 style="color: #0A4C95; font-size: 22px; margin-top: 0;">Welcome, Dr. {$name}! 🎉</h2>
        <p style="font-size: 15px; color: #334155;">
            Aapka <strong>Vyapar Wallah Media & Marketing</strong> par swagat hai. Aapka onboarding process <strong>{$clinic}</strong> ke liye successfully complete ho gaya hai.
        </p>
        <p style="font-size: 15px; color: #334155;">
            Aaj se aapke clinic ki digital growth, patient footfall aur brand building ki zimmedari hamari hai. Humari expert team ne aapke project par blueprint banana shuru kar diya hai.
        </p>
        <div style="background-color: #fff3ed; border-left: 4px solid #F37021; padding: 20px; margin: 30px 0; border-radius: 0 8px 8px 0;">
            <h3 style="color: #F37021; margin: 0 0 8px 0; font-size: 16px; display: flex; align-items: center;">
                💡 The Vyapar Wallah Standard
            </h3>
            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;">
                <strong>Kya aap jante hain?</strong> Humne pichle kuch saalon me Bihar (Patna, Muzaffarpur, Samastipur) ke <strong>50+ top clinics aur hospitals</strong> ki patient footfall ko apni data-driven digital strategies se 300% tak badhaya hai. Hum sirf marketing nahi karte, hum doctors ka ek trusted brand banate hain.
            </p>
        </div>
        <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <p style="margin: 0; font-size: 14px; color: #475569;">
                📎 <strong>Important Note:</strong> Aapki digital prescription aur payment invoice niche attach kar di gayi hain. Amount Paid: ₹{$safeAdvancePaid}. Balance Due: ₹{$safeBalanceDue}. Transaction ID: {$safeTxnId}.
            </p>
        </div>
        <p style="font-size: 15px; color: #334155;">
            Aapne jo weekly meeting ka din aur samay select kiya hai, humari team us waqt aapse review ke liye connect karegi.
        </p>
        <p style="margin-top: 40px; font-size: 15px;">
            <i style="color: #0A4C95;">Marketing se Practice ki Growth!</i> <br><br>
            Best Regards,<br>
            <strong style="color: #0A4C95; font-size: 18px;">Team Vyapar Wallah</strong><br>
            <span style="font-size: 13px; color: #64748b;">Your Digital Growth Partner</span>
        </p>
    </div>
    <div style="background-color: #1e293b; color: #94a3b8; text-align: center; padding: 20px; font-size: 12px;">
        <p style="margin: 0 0 5px 0;">&copy; 2026 Vyapar Wallah Media & Marketing. All Rights Reserved.</p>
        <p style="margin: 0;">New Alkapuri, Gardanibagh, Patna, Bihar - 800002</p>
        <p style="margin: 5px 0 0 0; color: #F37021;">support@vyaparwallah.com | +91-9187641492</p>
    </div>
</div>
HTML;
    $mail->AltBody = "Welcome to Vyapar Wallah, Dr. {$rawName}! Your onboarding for {$rawClinic} is complete. Advance paid: ₹{$safeAdvancePaid}. Balance: ₹{$safeBalanceDue}. Transaction ID: {$txnId}. Docs attached.";

    $mail->addStringAttachment(
        $decodedRx,
        'Vyapar-Wallah-Prescription.pdf',
        'base64',
        'application/pdf'
    );
    $mail->addStringAttachment(
        $decodedInv,
        'Vyapar-Wallah-Invoice.pdf',
        'base64',
        'application/pdf'
    );

    // Send Email 1: Client Welcome
    $mail->send();

    // --- Email 2: Admin Notification ---
    // Clear previous recipients before adding the admin
    $mail->clearAddresses();
    $mail->addAddress('vyaparwallah111@gmail.com'); // REMINDER: Change this to your real admin email

    $mail->Subject = '🚨 New Client Onboarded: ' . $clinic;

    // Construct Admin Notification Body with a clean HTML data table
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #333;'>
        <h2 style='color: #0A4C95;'>New Client Onboarding Record</h2>
        <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 600px; border: 1px solid #ddd;'>
            <tr style='background-color: #f2f2f2;'><th style='text-align: left;'>Field</th><th style='text-align: left;'>Details</th></tr>
            <tr><td><strong>Doctor Name</strong></td><td>Dr. {$name}</td></tr>
            <tr><td><strong>Clinic Name</strong></td><td>{$clinic}</td></tr>
            <tr><td><strong>Phone (Mobile)</strong></td><td>{$safeMobile}</td></tr>
            <tr><td><strong>WhatsApp</strong></td><td>{$safeWhatsapp}</td></tr>
            <tr><td><strong>Email Address</strong></td><td>{$safeEmail}</td></tr>
            <tr><td><strong>Setup Cost</strong></td><td>₹{$safeSetupCost}</td></tr>
            <tr><td><strong>Monthly Cost</strong></td><td>₹{$safeMonthlyCost}</td></tr>
            <tr><td><strong>Advance Paid</strong></td><td>₹{$safeAdvancePaid}</td></tr>
            <tr><td><strong>Balance Due</strong></td><td>₹{$safeBalanceDue}</td></tr>
            <tr><td><strong>Razorpay Payment ID</strong></td><td>{$safeTxnId}</td></tr>
        </table>
        <p style='margin-top: 20px;'>The onboarding PDF document is attached for your reference.</p>
    </div>";

    $mail->AltBody = "New Client Onboarded: Dr. {$rawName} from {$rawClinic}. Setup: ₹{$safeSetupCost}. Monthly: ₹{$safeMonthlyCost}. Advance paid: ₹{$safeAdvancePaid}. Balance due: ₹{$safeBalanceDue}. Payment ID: {$txnId}.";
    
    $mail->send(); // Send Admin Notification Email

    $clientPhone = sessionValue('clientPhone', $onboarding, 'whatsapp', $mobile);
    $whatsappDoctorName = sessionValue('docName', $onboarding, 'doctorName', $rawName);
    $whatsappClinicName = sessionValue('clinicName', $onboarding, 'clinicName', $rawClinic);

    try {
        sendFast2SmsWhatsAppTemplate($clientPhone, $whatsappDoctorName, $whatsappClinicName);
    } catch (Throwable $whatsAppError) {
        logWhatsAppError('Unexpected Fast2SMS WhatsApp error.', ['error' => $whatsAppError->getMessage()]);
    }

    unset($_SESSION['is_paid']);
    session_destroy(); // Destroy session to prevent replay attacks after successful flow
    respond('success');
} catch (Exception $error) {
    http_response_code(500);
    $message = $mail instanceof PHPMailer && $mail->ErrorInfo !== ''
        ? $mail->ErrorInfo
        : $error->getMessage();
    respond('error', 'Mailer Error: ' . $message);
} catch (Throwable $error) {
    http_response_code(500);
    respond('error', 'Server Error: ' . $error->getMessage());
}
