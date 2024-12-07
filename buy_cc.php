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

// Fetch user balance
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();
$userBalance = $user['balance'];

// Search and Sort
$search = '';
$sort = 'newest';
$query = "SELECT * FROM cards WHERE status = 'available'";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['search'])) {
        $search = trim($_GET['search']);
        if (!empty($search)) {
            $query .= " AND (bin LIKE '%$search%' OR country LIKE '%$search%')";
        }
    }

    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
        if ($sort === 'price_asc') {
            $query .= " ORDER BY price ASC";
        } elseif ($sort === 'price_desc') {
            $query .= " ORDER BY price DESC";
        } else {
            $query .= " ORDER BY created_at DESC";
        }
    }
}

$cardsQuery = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy CC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* General */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            font-family: 'Poppins', sans-serif;
            color: white;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease-in-out;
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

        /* Main Content */
        .dashboard-content {
            margin-left: 270px;
            padding: 30px;
            transition: margin-left 0.3s ease-in-out;
        }

        /* Search and Sort */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sort-dropdown {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 10px;
            font-weight: bold;
        }

        /* Card Container */
        .card-container {
            background: url('http://kezzycc.shop/cards.png') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.8);
        }

        /* Buy Button */
        .buy-btn {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            border: none;
            color: black;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .buy-btn:hover {
            transform: scale(1.05);
            box-shadow: 0px 0px 10px rgba(78, 243, 198, 0.8);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        .modal-content {
            background: #2f2f40;
            width: 350px;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .modal-success {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
        }

        .modal-error {
            color: #dc3545;
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h3>Kezzy Shop 🌟</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="buy_cc.php">Buy CC</a>
        <a href="my_cards.php">My Cards</a>
        <a href="add_balance.php">Add Balance</a>
        <a href="checker.php">Checker</a>
        <a href="tickets.php">Tickets</a>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content" id="dashboard-content">
        <h1 class="text-center mb-4">Buy CC 💳</h1>

        <!-- Search and Sort -->
        <form method="GET" class="search-bar">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by BIN or Country" 
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <select name="sort" class="sort-dropdown">
                <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest</option>
                <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Price (Low to High)</option>
                <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Price (High to Low)</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Cards -->
        <div class="row">
            <?php if ($cardsQuery->num_rows > 0): ?>
                <?php while ($card = $cardsQuery->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card-container">
                            <h4>BIN: <?php echo htmlspecialchars($card['bin']); ?></h4>
                            <p>🌍 Country: <?php echo htmlspecialchars($card['country']); ?></p>
                            <p>💰 Price: $<?php echo number_format($card['price'], 2); ?></p>
                            <button class="buy-btn" onclick="processPurchase(<?php echo $card['id']; ?>)">Buy</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No cards found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="loadingModal" class="modal-overlay">
        <div class="modal-content" id="modalContent"></div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const dashboardContent = document.getElementById('dashboard-content');
            sidebar.classList.toggle('hidden');
            dashboardContent.classList.toggle('collapsed');
        }

        function showModal(content) {
            const modal = document.getElementById('loadingModal');
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = content;
            modal.style.display = 'flex';
        }

        function hideModal() {
            document.getElementById('loadingModal').style.display = 'none';
        }

        function processPurchase(cardId) {
            showModal('<p>Checking Card...</p>');

            fetch('process_purchase.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `card_id=${cardId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showModal('<p class="modal-success">✔ Card Live!</p>');
                } else if (data.status === 'insufficient_balance') {
                    showModal('<p class="modal-error">✘ Insufficient Balance!</p>');
                } else if (data.status === 'declined') {
                    showModal('<p class="modal-error">✘ Card Declined!</p>');
                } else {
                    showModal('<p class="modal-error">✘ An unexpected error occurred.</p>');
                }

                setTimeout(() => {
                    hideModal();
                    location.reload();
                }, 3000);
            })
            .catch(() => {
                showModal('<p class="modal-error">An error occurred. Please try again.</p>');
                setTimeout(hideModal, 3000);
            });

        }
    </script>
</body>
</html>
