<?php
// PHP Error Reporting (ये एरर 500 को फिक्स करने में मदद करता है)
error_reporting(0); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 🔒 SECURITY: Anti-Spam Check
    if (!empty($_POST['bot_check'])) {
        die("Security Error: Spam Bot Detected!");
    }

    // 🔒 SECURITY: Get & Sanitize Data Safely
    $name     = isset($_POST["user_name"]) ? htmlspecialchars(trim($_POST["user_name"]), ENT_QUOTES, 'UTF-8') : 'Client';
    $email    = isset($_POST["user_email"]) ? filter_var(trim($_POST["user_email"]), FILTER_SANITIZE_EMAIL) : '';
    $phone    = isset($_POST["user_phone"]) ? htmlspecialchars(trim($_POST["user_phone"]), ENT_QUOTES, 'UTF-8') : '';
    $business = isset($_POST["business_name"]) ? htmlspecialchars(trim($_POST["business_name"]), ENT_QUOTES, 'UTF-8') : 'Business';
    $plan     = isset($_POST["selected_plan"]) ? htmlspecialchars(trim($_POST["selected_plan"]), ENT_QUOTES, 'UTF-8') : 'Custom Plan';
    $message  = isset($_POST["user_message"]) ? htmlspecialchars(trim($_POST["user_message"]), ENT_QUOTES, 'UTF-8') : 'No message';

    // ⚠️ AGENCY EMAILS (Change these if needed)
    $admin_email = "Hello@vyaparwallah.com"; 
    $sender_email = "info@vyaparwallah.com"; // Hostinger में यह ईमेल बना होना ज़रूरी है

    // --- 1. EMAIL TO ADMIN ---
    $admin_subject = "New Lead: $plan - $business";
    $admin_body = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 10px;'>
        <h2 style='color: #0A4C95;'>New Pricing Plan Request</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>WhatsApp:</strong> $phone</p>
        <p><strong>Business:</strong> $business</p>
        <p><strong>Plan:</strong> $plan</p>
        <p><strong>Message:</strong> $message</p>
    </div>";

    $admin_headers  = "MIME-Version: 1.0\r\n";
    $admin_headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $admin_headers .= "From: Vyapar Wallah <$sender_email>\r\n";
    $admin_headers .= "Reply-To: $email\r\n";

    // We use @ to suppress warnings so it doesn't cause a 500 error if mail server is slow
    $mail_sent = @mail($admin_email, $admin_subject, $admin_body, $admin_headers);

    // --- 2. AUTOMATED EMAIL TO CLIENT ---
    if ($mail_sent && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user_subject = "Welcome to Vyapar Wallah! 🚀";
        $user_body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; border-radius: 10px;'>
            <h2 style='color: #0A4C95;'>Hi $name,</h2>
            <p>Thank you for showing interest in our <strong>$plan</strong> for <strong>$business</strong>.</p>
            <p>We will contact you at <strong>$phone</strong> very soon to discuss your strategy.</p>
            <br>
            <p>Best Regards,<br><strong>Team Vyapar Wallah</strong></p>
        </div>";

        $user_headers  = "MIME-Version: 1.0\r\n";
        $user_headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $user_headers .= "From: Vyapar Wallah <$sender_email>\r\n";

        @mail($email, $user_subject, $user_body, $user_headers);
    }

    // --- 3. REDIRECT TO THANK YOU PAGE ---
    // Make sure no spaces or HTML exist before this header!
    $encoded_name = urlencode($name);
    $encoded_biz = urlencode($business);
    
    header("Location: thankyou.php?n=$encoded_name&b=$encoded_biz");
    exit();

} else {
    // Redirect back to pricing if accessed directly
    header("Location: pricing.html");
    exit();
}
?>