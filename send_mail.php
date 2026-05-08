<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond('error', 'Only POST requests are allowed.');
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody ?: '', true);

if (!is_array($data)) {
    http_response_code(400);
    respond('error', 'Invalid JSON payload.');
}

$email = trim((string) ($data['email'] ?? ''));
$rawName = trim((string) ($data['name'] ?? 'Doctor'));
$rawClinic = trim((string) ($data['clinic'] ?? 'your clinic'));
$amount = trim((string) ($data['amount'] ?? '0'));
$pdfBase64 = (string) ($data['pdfBase64'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    respond('error', 'A valid recipient email is required.');
}

if ($pdfBase64 === '') {
    http_response_code(400);
    respond('error', 'PDF attachment data is missing.');
}

$pdfBase64 = trim($pdfBase64);

if (strpos($pdfBase64, ',') !== false) {
    $base64Parts = explode(',', $pdfBase64);
    $pdfBase64 = $base64Parts[1];
}

$pdfBase64 = str_replace(' ', '+', $pdfBase64);
$decodedPdfBinary = base64_decode($pdfBase64);

if ($decodedPdfBinary === false) {
    http_response_code(400);
    respond('error', 'PDF attachment data is not valid Base64 after stripping prefix.');
}

$name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
$clinic = htmlspecialchars($rawClinic, ENT_QUOTES, 'UTF-8');
$safeAmount = htmlspecialchars($amount, ENT_QUOTES, 'UTF-8');

$mail = null;

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com'; // TODO: Replace with your Hostinger SMTP host.
    $mail->SMTPAuth = true; // TODO: Keep true for authenticated SMTP.
    $mail->Username = 'onboarding@vyaparwallah.com'; // TODO: Replace with your official email.
    $mail->Password = 'Avinashjha@111'; // TODO: Replace with your SMTP password or app password.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // TODO: Use ENCRYPTION_STARTTLS if your Hostinger port requires TLS.
    $mail->Port = 465; // TODO: Common Hostinger ports: 465 for SSL, 587 for TLS.

    $mail->CharSet = 'UTF-8';
    $mail->setFrom('onboarding@vyaparwallah.com', 'Vyapar Wallah'); // TODO: Replace with your official From address.
    $mail->addAddress($email, $rawName);
    $mail->addReplyTo('onboarding@vyaparwallah.com', 'Vyapar Wallah'); // TODO: Replace with your official reply-to address.

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
                📎 <strong>Important Note:</strong> Aapke 'Client Onboarding Record' ki digital prescription PDF niche attach kar di gayi hai (jo onboarding ke time aapke device me bhi download ho gayi thi). Kripya ise apne reference ke liye save rakhein.
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
    $mail->AltBody = "Welcome to Vyapar Wallah, {$rawName}! Your onboarding for {$rawClinic} is complete. We have received your initial payment of ₹{$amount}. Your onboarding PDF is attached.";

    $mail->addStringAttachment(
        $decodedPdfBinary,
        'Vyapar-Wallah-Prescription.pdf',
        'base64',
        'application/pdf'
    );

    $mail->send();
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
