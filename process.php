<?php
// 1. Check if the form was actually posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 🔒 SECURITY 1: Honeypot Check (Blocks Spam Bots)
    if (!empty($_POST['bot_check'])) {
        die("Security Error: Spam Bot Detected!");
    }

    // 🔒 SECURITY 2: Strict Sanitization (Prevents Hacking & XSS)
    $name     = htmlspecialchars(trim($_POST["user_name"]), ENT_QUOTES, 'UTF-8');
    $email    = filter_var(trim($_POST["user_email"]), FILTER_SANITIZE_EMAIL);
    $phone    = htmlspecialchars(trim($_POST["user_phone"]), ENT_QUOTES, 'UTF-8');
    $business = htmlspecialchars(trim($_POST["business_name"]), ENT_QUOTES, 'UTF-8');
    $plan     = htmlspecialchars(trim($_POST["selected_plan"]), ENT_QUOTES, 'UTF-8');
    $message  = htmlspecialchars(trim($_POST["user_message"]), ENT_QUOTES, 'UTF-8');

    // 🔒 SECURITY 3: Validate Email Format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Invalid Email Address! Please try again.'); window.history.back();</script>");
    }

    // ==========================================
    // ⚙️ SETUP YOUR EMAILS HERE
    // ==========================================
    $admin_email = "Hello@vyaparwallah.com"; // <-- यहाँ वो ईमेल डालें जिस पर आपको लीड्स चाहिए
    $sender_email = "no-reply@vyaparwallah.com"; // <-- यहाँ अपनी वेबसाइट का कोई भी ईमेल डालें (from address)

    // ==========================================
    // 📩 1. EMAIL TO YOU (ADMIN)
    // ==========================================
    $admin_subject = "🚀 New Lead: $plan - $business";
    
    $admin_body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 10px;'>
        <h2 style='color: #0A4C95; border-bottom: 2px solid #F37021; padding-bottom: 10px;'>New Pricing Plan Request</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>WhatsApp:</strong> $phone</p>
        <p><strong>Business Name:</strong> $business</p>
        <p><strong>Selected Plan:</strong> <span style='background: #F37021; color: white; padding: 4px 10px; border-radius: 5px; font-weight: bold;'>$plan</span></p>
        <p><strong>Message/Requirement:</strong><br> " . nl2br($message) . "</p>
    </div>";

    $admin_headers  = "MIME-Version: 1.0" . "\r\n";
    $admin_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $admin_headers .= "From: Vyapar Wallah Leads <$sender_email>" . "\r\n";
    $admin_headers .= "Reply-To: $email" . "\r\n"; 

    // Send Admin Email
    $mail_sent = mail($admin_email, $admin_subject, $admin_body, $admin_headers);

    if ($mail_sent) {
        
        // ==========================================
        // 📩 2. AUTOMATED EMAIL TO USER (CLIENT)
        // ==========================================
        $user_subject = "Welcome to Vyapar Wallah! Your Growth Journey Begins 🚀";
        
        $user_body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;'>
            <h2 style='color: #0A4C95;'>Hi $name,</h2>
            <p>Thank you for showing interest in our <strong>$plan</strong> for <strong>$business</strong>.</p>
            <p>We have successfully received your request. One of our growth experts will review your details and contact you at <strong>$phone</strong> very soon to discuss your customized strategy.</p>
            <br>
            <p style='background: #eef2f5; padding: 15px; border-left: 4px solid #F37021; border-radius: 5px;'>
                <strong>Did you know?</strong> We have helped over 200+ businesses and 50+ clinics scale their revenue. We are excited to do the same for you!
            </p>
            <br>
            <p>If you have any urgent questions, feel free to reply directly to this email.</p>
            <hr style='border: none; border-top: 1px solid #cbd5e1; margin: 20px 0;'>
            <p style='color: #64748b; font-size: 0.9rem;'>
                Best Regards,<br>
                <strong>Team Vyapar Wallah</strong><br>
                <a href='https://vyaparwallah.com' style='color: #F37021; text-decoration: none;'>www.vyaparwallah.com</a>
            </p>
        </div>";

        $user_headers  = "MIME-Version: 1.0" . "\r\n";
        $user_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $user_headers .= "From: Vyapar Wallah <$admin_email>" . "\r\n"; // Email goes from your support email

        // Send Auto-Reply to User
        mail($email, $user_subject, $user_body, $user_headers);

        // Success Redirect
       // Success Redirect to Thank You Page with Data
        $encoded_name = urlencode($name);
        $encoded_biz = urlencode($business);
        
        header("Location: thankyou.php?n=$encoded_name&b=$encoded_biz");
        exit();

} else {
    // If accessed directly without form submission
    header("Location: pricing.html");
    exit();
}
?>