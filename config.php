<?php
declare(strict_types=1);

if (!defined('FAST2SMS_API_KEY')) {
    define('FAST2SMS_API_KEY', getenv('FAST2SMS_API_KEY') ?: 'Efz4cOXyxDNgR8jCQ1rpZdmGnIbiMl26PJaBKYSVsw0oAutH9vtbjZqs4QNMYzfp7dnkoCT2GVAia8um');
}

if (!defined('WHATSAPP_TEMPLATE_NAME')) {
    // Replace this with the exact approved WhatsApp template name from your Fast2SMS panel.
    define('WHATSAPP_TEMPLATE_NAME', 'welcoem_message');
}

if (!defined('FAST2SMS_WHATSAPP_MESSAGE_ID')) {
    // Replace this with the Fast2SMS WhatsApp Manager message_id for the approved template.
    define('FAST2SMS_WHATSAPP_MESSAGE_ID', '');
}

if (!defined('FAST2SMS_WHATSAPP_PHONE_NUMBER_ID')) {
    // Replace this with your Fast2SMS WABA phone_number_id.
    define('FAST2SMS_WHATSAPP_PHONE_NUMBER_ID', '');
}

return [
    'razorpay' => [
        'key_id' => 'rzp_live_Sn9bOtOiEoupFy',
        'key_secret' => 'tN06a2mOmLzfYyBfbykiaZHF',
    ],
    'smtp' => [
        'host' => 'smtp.hostinger.com',
        'username' => 'onboarding@vyaparwallah.com',
        'password' => 'Avinashjha@111',
        'secure' => 'ssl',
        'port' => 465,
        'from_email' => 'onboarding@vyaparwallah.com',
        'from_name' => 'Vyapar Wallah',
        'reply_to_email' => 'onboarding@vyaparwallah.com',
        'reply_to_name' => 'Vyapar Wallah',
    ],
];
