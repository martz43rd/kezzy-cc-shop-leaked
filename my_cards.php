<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');
$userId = $_SESSION['user_id'];

// Arama işlemi
$search = '';
$query = "SELECT * FROM cards WHERE user_id = $userId";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = trim($_GET['search']);
    if (!empty($search)) {
        $query .= " AND (full_card LIKE '%$search%' OR country LIKE '%$search%')";
    }
}

$myCards = $conn->query($query);

// Son alınan kart
$lastCardQuery = $conn->prepare("SELECT id FROM cards WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$lastCardQuery->bind_param("i", $userId);
$lastCardQuery->execute();
$lastCard = $lastCardQuery->get_result()->fetch_assoc();
$lastCardId = $lastCard['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            font-family: 'Poppins', sans-serif;
            color: white;
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
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease-in-out;
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

        /* Main Content */
        .dashboard-content {
            margin-left: 270px;
            padding: 30px;
        }

        .dashboard-content h1 {
            text-align: center;
            color: #4ef3c6;
            text-shadow: 0px 0px 10px #4ef3c6, 0px 0px 20px #38a3a5;
            margin-bottom: 30px;
        }

        /* Search Bar */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Card Container */
        .card-container {
            background: #1f1f1f;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .card-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.8);
        }

        .card-container.last-card {
            border: 3px solid #ff4500;
            box-shadow: 0 0 20px #ff4500;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Kezzy Shop 🌟</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="buy_cc.php">Buy CC</a>
        <a href="my_cards.php">My Cards</a>
        <a href="add_balance.php">Add Balance</a>
        <a href="checker.php">Checker</a>
        <a href="tickets.php">Tickets</a>
    </div>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <!-- Main Content -->
    <div class="dashboard-content">
        <h1>My Cards 🃏</h1>

        <!-- Search Bar -->
        <form method="GET" class="search-bar">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by Card Info or Country" 
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Cards List -->
        <div class="row">
            <?php if ($myCards->num_rows > 0): ?>
                <?php while ($card = $myCards->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card-container <?php echo $card['id'] === $lastCardId ? 'last-card' : ''; ?>">
                            <h4>Card Info:</h4>
                            <p><strong><?php echo htmlspecialchars($card['full_card']); ?></strong></p>
                            <p>🌍 Country: <?php echo htmlspecialchars($card['country']); ?></p>
                            <p>💰 Price: $<?php echo number_format($card['price'], 2); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">You haven't purchased any cards yet!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
	
</body>
</html>
