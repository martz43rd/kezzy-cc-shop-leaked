<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

// Fetch user data
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

// Total user count
$totalUsersQuery = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = $totalUsersQuery ? $totalUsersQuery->fetch_assoc()['total'] : 0;

// Total cards count
$totalCardsQuery = $conn->query("SELECT COUNT(*) AS total FROM cards");
$totalCards = $totalCardsQuery ? $totalCardsQuery->fetch_assoc()['total'] : 0;

// Total sold cards
$soldCardsQuery = $conn->query("SELECT COUNT(*) AS total FROM cards WHERE status = 'sold'");
$soldCards = $soldCardsQuery ? $soldCardsQuery->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* General */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            width: 250px;
            height: 100vh;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.7);
        }

        .sidebar.hidden {
            transform: translateX(-270px);
        }

        .sidebar h3 {
            text-align: center;
            font-size: 1.8rem;
            color: #4ef3c6;
            text-shadow: 0 0 10px #4ef3c6, 0 0 20px #16213e;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 15px 0;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .sidebar a:hover {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            transform: translateX(10px);
            color: black;
        }

        .sidebar-toggle {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
            z-index: 1100;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0px 0px 10px rgba(78, 243, 198, 0.5);
        }

        /* Dashboard */
        .dashboard-content {
            margin-left: 270px;
            padding: 30px;
            transition: margin-left 0.3s ease-in-out;
        }

        .dashboard-content.collapsed {
            margin-left: 0;
        }

        /* Profile Card */
        .profile-card {
            background: linear-gradient(135deg, #232323, #191919);
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .profile-card img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid #4ef3c6;
        }

        .profile-card h4 {
            margin: 0;
            font-size: 1.8rem;
            color: #ff6a00;
            cursor: pointer;
            text-shadow: 0 0 10px #ff6a00, 0 0 20px #ff4500;
            animation: glowEffect 1.5s infinite alternate;
        }

        @keyframes glowEffect {
            from {
                text-shadow: 0 0 10px #ff6a00, 0 0 20px #ff4500;
            }
            to {
                text-shadow: 0 0 20px #ff4500, 0 0 30px #ff6a00;
            }
        }

        .profile-card p {
            font-size: 1.1rem;
            color: #f0ad4e;
            font-weight: bold;
            cursor: pointer;
        }

        .profile-card p:hover {
            text-decoration: underline;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff5656, #c90000);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 16px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: scale(1.1);
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, #1a1a1a, #0d0d0d);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 0;
            width: 100%;
            height: 200%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0));
            transform: rotate(45deg);
            animation: shineEffect 2s infinite linear;
        }

        @keyframes shineEffect {
            0% { top: -100%; }
            100% { top: 200%; }
        }

        .stats-card h4 {
            color: #4ef3c6;
            margin-bottom: 10px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.8);
        }

        /* Banner */
        .banner {
            display: block;
            margin: 40px auto;
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
        }

        .banner:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <div class="sidebar" id="sidebar">
        <h3>Kezzy Shop 🌟</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="buy_cc.php">Buy CC</a>
        <a href="my_cards.php">My Cards</a>
        <a href="add_balance.php">Add Balance</a>
        <a href="checker.php">Checker</a>
        <a href="tickets.php">Tickets</a>
    </div>

    <div class="dashboard-content" id="dashboard-content">
        <div class="profile-card mb-4">
            <a href="profile.php"><img src="http://kezzycc.shop/pp.jpg" alt="Profile Picture"></a>
            <div>
                <h4><a href="profile.php">Welcome <?php echo htmlspecialchars($user['username']); ?>!</a></h4>
                <p><a href="add_balance.php">💰 Balance: $<?php echo number_format($user['balance'], 2); ?></a></p>
            </div>
            <form method="POST" action="logout.php">
                <button class="logout-btn">Logout</button>
            </form>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>🧑‍💻 Total Users</h4>
                    <p><?php echo $totalUsers; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>📦 Total Cards</h4>
                    <p><?php echo $totalCards; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>📊 Sold Cards</h4>
                    <p><?php echo $soldCards; ?></p>
                </div>
            </div>
        </div>

        <a href="https://t.me/kezzyccshop" target="_blank">
            <img src="https://kezzycc.shop/banner.jpg" alt="Banner" class="banner">
        </a>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const dashboardContent = document.getElementById('dashboard-content');
            sidebar.classList.toggle('hidden');
            dashboardContent.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
