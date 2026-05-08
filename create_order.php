<?php
// 1. सर्वर को बताएं कि हम JSON फॉर्मेट में बात कर रहे हैं
header('Content-Type: application/json');

// 2. Razorpay SDK को फाइल से लिंक करें (ध्यान दें: razorpay-php फोल्डर सर्वर पर होना चाहिए)
require_once('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

// ⚠️ 3. अपनी असली API Keys यहाँ डालें (Razorpay Dashboard > Settings > API Keys)
$api_key = 'rzp_live_SmiNIKsNL3hmlV'; 
$api_secret = '0CgSCOhrWoqjmAY9XjxEvkKm';

// 4. Frontend से आया हुआ अमाउंट कैच करना (JSON और POST दोनों तरीकों से)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// अमाउंट चेक करना
$rupeeAmount = isset($_POST['amount']) ? $_POST['amount'] : (isset($input['amount']) ? $input['amount'] : 0);

// अगर अमाउंट नहीं मिला या 0 है
if (empty($rupeeAmount) || $rupeeAmount <= 0) {
    echo json_encode(['error' => 'Error: Payment amount is missing or invalid!']);
    exit;
}

// 5. रुपये (Rupees) को पैसे (Paise) में बदलना
// (उदा: ₹5000 * 100 = 500000 पैसे)
$amountInPaise = intval(floatval($rupeeAmount) * 100);

// Security Check: Razorpay कम से कम ₹1 (100 पैसे) लेता है
if ($amountInPaise < 100) {
    echo json_encode(['error' => 'Razorpay API Error: Order amount must be at least ₹1']);
    exit;
}

// 6. Razorpay Server से Secure Order ID मंगाना
try {
    $api = new Api($api_key, $api_secret);
    
    // सुरक्षित आर्डर का डेटा
    $orderData = [
        'receipt'         => 'vw_rcpt_' . time(), // vw = Vyapar Wallah
        'amount'          => $amountInPaise, 
        'currency'        => 'INR',
        'payment_capture' => 1 // Payment success होते ही अपने आप खाते में आ जाएगा
    ];

    // Razorpay से Order ID क्रिएट करना
    $razorpayOrder = $api->order->create($orderData);
    
    // 7. Success: Order ID को वापस Frontend (JavaScript) को भेजना
    echo json_encode(['order_id' => $razorpayOrder['id']]);

} catch(Exception $e) {
    // 8. Fail: अगर कोई एरर आई तो उसे कैच करके दिखाना
    echo json_encode(['error' => 'Razorpay Error: ' . $e->getMessage()]);
}
?>