<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');

// İstatistikler
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalCards = $conn->query("SELECT COUNT(*) AS total FROM cards")->fetch_assoc()['total'];
$soldCards = $conn->query("SELECT COUNT(*) AS total FROM cards WHERE status = 'sold'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #121212;
            color: white;
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
        .stats-card {
            background: #202020;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h3>Admin Panel</h3>
    <a href="admin_panel.php">Dashboard</a>
    <a href="admin_add_card.php">Kart Yönetimi</a>
    <a href="admin_balance_requests.php">Bakiye Talepleri</a>
    <a href="admin_users.php">Kullanıcı Yönetimi</a>
    <a href="admin_tickets.php">Ticket Yönetimi</a>
    <a href="admin_logout.php">Çıkış Yap</a>
</div>

    <div class="dashboard-content">
        <h1>Hoş Geldiniz, <?php echo $_SESSION['admin_username']; ?>!</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>🧑‍💻 Toplam Kullanıcı</h4>
                    <p><?php echo $totalUsers; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>📦 Toplam Kart</h4>
                    <p><?php echo $totalCards; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>📊 Satılan Kart</h4>
                    <p><?php echo $soldCards; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
