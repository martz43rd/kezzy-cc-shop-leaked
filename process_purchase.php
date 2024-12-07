<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmanız gerekiyor.']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantı hatası.']);
    exit;
}

$userId = $_SESSION['user_id'];
$cardId = intval($_POST['card_id']);

// Kart bilgilerini al
$cardQuery = $conn->prepare("SELECT * FROM cards WHERE id = ? AND status = 'available'");
$cardQuery->bind_param("i", $cardId);
$cardQuery->execute();
$card = $cardQuery->get_result()->fetch_assoc();

if (!$card) {
    echo json_encode(['status' => 'error', 'message' => 'Kart bulunamadı.']);
    exit;
}

$cardData = $card['full_card'];
$cardPrice = $card['price'];

// Kullanıcı bilgilerini al
$userQuery = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

if ($user['balance'] < $cardPrice) {
    // Yetersiz bakiye durumu
    echo json_encode(['status' => 'insufficient_balance', 'message' => 'Bakiye yetersiz.']);
    exit;
}

// API çağrısı
$apiUrl = "" . urlencode($cardData);
$apiResponse = file_get_contents($apiUrl);

// "Nice Payment Method Successfully Added!" kontrolü
if (strpos($apiResponse, "Nice Payment Method Successfully Added!") !== false) {
    // Bakiye düş ve kartı sat
    $newBalance = $user['balance'] - $cardPrice;
    $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $updateBalanceQuery->bind_param("di", $newBalance, $userId);
    $updateBalanceQuery->execute();

    $updateCardStatusQuery = $conn->prepare("UPDATE cards SET status = 'sold', user_id = ? WHERE id = ?");
    $updateCardStatusQuery->bind_param("ii", $userId, $cardId);
    $updateCardStatusQuery->execute();

    echo json_encode(['status' => 'success']);
} else {
    // Kartı sil
    $deleteCardQuery = $conn->prepare("DELETE FROM cards WHERE id = ?");
    $deleteCardQuery->bind_param("i", $cardId);
    $deleteCardQuery->execute();

    echo json_encode(['status' => 'declined']);
}
?>
