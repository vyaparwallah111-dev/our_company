<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$name = htmlspecialchars($data['name'] ?? 'Unknown');
$phone = htmlspecialchars($data['phone'] ?? 'Unknown');

// आपकी ईमेल आईडी
$to = "vyaparwallah111@gmail.com"; 

$subject = "🚀 HOT LEAD from Vyapar AI Chatbot!";
$message = "Hey Team,\n\nYou have a new lead directly from the Website Chatbot:\n\n";
$message .= "Name: " . $name . "\n";
$message .= "WhatsApp No: " . $phone . "\n\n";
$message .= "Please contact them ASAP to close the deal!";

// हेडर सेटिंग
$headers = "From: leads@vyaparwallah.com\r\n";
$headers .= "Reply-To: leads@vyaparwallah.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if(mail($to, $subject, $message, $headers)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>