<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

// Kullanıcı bakiyesi güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_balance'])) {
    $userId = intval($_POST['user_id']);
    $newBalance = floatval($_POST['new_balance']);

    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("di", $newBalance, $userId);
        if ($stmt->execute()) {
            $message = "Kullanıcı bakiyesi başarıyla güncellendi!";
        } else {
            $message = "Bakiyeyi güncellerken bir hata oluştu.";
        }
        $stmt->close();
    } else {
        $message = "SQL hatası: " . $conn->error;
    }
}

// Tüm kullanıcıları çek
$result = $conn->query("SELECT id, username, email, balance FROM users");
if (!$result) {
    die("Kullanıcıları çekerken hata: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kullanıcı Yönetimi</title>
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
        .table {
            background: #202020;
            color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .form-control {
            background: #121212;
            color: white;
            border: none;
        }
        .form-control:focus {
            background: #2a2a2a;
            outline: none;
            border-color: #6a5acd;
        }
        .btn-primary {
            background: #6a5acd;
            border: none;
        }
        .btn-primary:hover {
            background: #5641d9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Admin Panel 🌟</h3>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_users.php">Kullanıcı Yönetimi</a>
        <a href="admin_add_card.php">Kart Ekle</a>
        <a href="admin_tickets.php">Destek Talepleri</a>
    </div>

    <div class="dashboard-content">
        <h1 class="text-center">Kullanıcı Yönetimi</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <table class="table table-dark table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Email</th>
                    <th>Bakiye</th>
                    <th>Yeni Bakiye</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>$<?php echo number_format($user['balance'], 2); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="number" step="0.01" name="new_balance" class="form-control" placeholder="Yeni bakiye" required>
                        </td>
                        <td>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
