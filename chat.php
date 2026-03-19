<?php
// यह फ़ाइल सिर्फ़ JSON फॉर्मेट में बात करेगी
header('Content-Type: application/json');

// यूज़र का मैसेज और पेज का नाम प्राप्त करना
$data = json_decode(file_get_contents('php://input'), true);
$user_message = $data['message'] ?? '';
$page_context = $data['context'] ?? 'Website';

if(empty($user_message)){
    echo json_encode(['reply' => 'Please ask a question.']);
    exit;
}

// ==========================================
// 🔑 अपना Google Gemini API Key यहाँ डालें
// ==========================================
$api_key = "AIzaSyDnQQzDob2bqf4CznbjJmn5g2KKQPPYSM0"; 

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;

// 🧠 AI को निर्देश (System Prompt) - यह बॉट को बताएगा कि उसे क्या बोलना है
$system_instruction = "You are a friendly customer support assistant for 'Vyapar Wallah', a premium digital marketing agency in Bihar. We specialize in ROI-driven marketing, web design, and lead generation specifically for the Healthcare (Doctors, Clinics) and Education (Schools, Coaching) sectors. 
The user is currently browsing the '$page_context' page of our website. 
Answer their question based on this context. Keep your answer short (maximum 2-3 sentences), polite, and use a mix of Hindi and English (Hinglish). Do not use markdown like bold (**) or bullet points. Suggest them to contact via WhatsApp (+91-87641492) if they want to buy a plan or need deep consultancy.";

// API को भेजने के लिए डेटा तैयार करना
$payload = [
    "contents" => [
        [
            "role" => "user", 
            "parts" => [
                ["text" => $system_instruction . "\n\nUser Question: " . $user_message]
            ]
        ]
    ]
];

// cURL Request (API को कॉल करना)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

// API का जवाब समझना और वेबसाइट पर भेजना
$result = json_decode($response, true);

if(isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['reply' => trim($ai_reply)]);
} else {
    echo json_encode(['reply' => 'Sorry, our experts are currently busy. Please contact us on WhatsApp!']);
}
?>