<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Token doğrulama
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['checker_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
        exit;
    }

    $card = $_POST['card'] ?? '';
    $apiUrl = "" . urlencode($card);
    $response = file_get_contents($apiUrl);

    if (strpos($response, 'Nice Payment Method Successfully Added!') !== false) {
        echo json_encode(['status' => 'live']);
    } else {
        echo json_encode(['status' => 'declined']);
    }
}
?>
