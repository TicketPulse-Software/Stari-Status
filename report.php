<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];

    $stmt = $conn->prepare("SELECT url FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();

    if ($service) {
        $message = "Issue reported for service: " . $service['url'];
        $to = $_SESSION['phone']; // Admin's phone number from session

        $url = 'https://rest.clicksend.com/v3/sms/send';
        $data = [
            'messages' => [
                [
                    'source' => 'php',
                    'from' => 'ClickSend',
                    'body' => $message,
                    'to' => $to
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode('your_clicksend_username:your_clicksend_api_key')
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo "Report sent successfully!";
    } else {
        echo "Service not found.";
    }
}

$conn->close();
?>
