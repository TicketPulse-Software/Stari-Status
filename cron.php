<?php
include 'includes/db.php';

function check_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_code === 200;
}

$services = $conn->query("SELECT id, url FROM services");

while ($service = $services->fetch_assoc()) {
    $status = check_url($service['url']) ? 'Operational' : 'Down';
    $stmt = $conn->prepare("INSERT INTO service_status (service_id, status, checked_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $service['id'], $status);
    $stmt->execute();

    // If service is down, send a status update via Clicksend API
    if ($status === 'Down') {
        $message = "Service " . $service['url'] . " is down.";
        $admin_result = $conn->query("SELECT phone FROM users WHERE username='admin'");
        $admin = $admin_result->fetch_assoc();
        $to = $admin['phone'];

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
    }
}

$conn->close();
?>
