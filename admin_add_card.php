<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');

// Kart ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardData = trim($_POST['card_data']);
    $lines = explode("\n", $cardData); // Satırlara ayır
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    foreach ($lines as $line) {
        $line = trim($line); // Boşlukları kaldır
        if (!empty($line)) {
            try {
                list($fullCard, $country, $price) = explode(":", $line);
                $bin = substr($fullCard, 0, 6);

                $insertCard = $conn->prepare("INSERT INTO cards (bin, country, price, full_card, status) VALUES (?, ?, ?, ?, 'available')");
                $insertCard->bind_param("ssds", $bin, $country, $price, $fullCard);

                if ($insertCard->execute()) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Hata: $line";
                }
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Geçersiz format: $line";
            }
        }
    }

    $successMessage = "$successCount kart başarıyla eklendi!";
    if ($errorCount > 0) {
        $errorMessage = "$errorCount hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kart Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1f1f1f, #121212);
            color: white;
            font-family: 'Arial', sans-serif;
        }
        .sidebar {
            background: #202020;
            width: 250px;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s ease;
        }
        .sidebar a:hover {
            background: #6a5acd;
        }
        .dashboard-content {
            margin-left: 270px;
            padding: 20px;
        }
        .form-container {
            background: #202020;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="admin_panel.php">Dashboard</a>
        <a href="admin_add_card.php">Kart Yönetimi</a>
        <a href="admin_users.php">Kullanıcı Yönetimi</a>
        <a href="admin_logout.php">Çıkış Yap</a>
    </div>

    <div class="dashboard-content">
        <h1>Kart Ekle</h1>
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <ul class="text-danger">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kart Verileri (Her satır bir kart)</label>
                    <textarea class="form-control" name="card_data" rows="10" placeholder="4388576125381593|09|2028|958:USA:10.00&#10;4500041015447064|03|2028|408:USA:10.00" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Kartları Ekle</button>
            </form>
        </div>
    </div>
</body>
</html>
