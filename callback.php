<?php
// Callback için endpoint
$secret = "P6FFgOMOa7WGOXkCWeU72V8/XcTIwcyY"; // NOWPayments kontrol panelinden webhook secret'ınızı alın

// Gelen IPN verisini oku
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Güvenlik kontrolü: İmzayı doğrula
if (!isset($_SERVER['HTTP_X_NOWPAYMENTS_SIG'])) {
    http_response_code(400);
    die("Eksik imza.");
}

$receivedSig = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'];
$computedSig = hash_hmac('sha256', $input, $secret);

if ($receivedSig !== $computedSig) {
    http_response_code(400);
    die("İmza doğrulaması başarısız.");
}

// Veritabanı bağlantısı
$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    http_response_code(500);
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

// Gelen verileri işle
if (isset($data['payment_status']) && $data['payment_status'] === 'finished') {
    $userId = intval($data['order_id']);
    $amount = floatval($data['pay_amount']);
    $payAddress = $data['pay_address'];

    // Kullanıcının bakiyesini güncelle
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        die("SQL Hatası: " . $conn->error);
    }
    $stmt->bind_param('di', $amount, $userId);
    $stmt->execute();

    // Ödemenin durumunu güncelle
    $stmt = $conn->prepare("UPDATE payments SET status = 'completed', updated_at = NOW() WHERE user_id = ? AND pay_address = ?");
    if (!$stmt) {
        http_response_code(500);
        die("SQL Hatası: " . $conn->error);
    }
    $stmt->bind_param('is', $userId, $payAddress);
    $stmt->execute();

    // Başarılı yanıt gönder
    http_response_code(200);
    echo "Ödeme başarıyla işlendi.";
    exit;
} else {
    http_response_code(400);
    echo "Geçersiz ödeme durumu.";
    exit;
}
?>
