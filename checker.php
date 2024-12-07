<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User data not found.");
}

$userBalance = floatval($user['balance']); // User balance
$costPerCheck = 0.02; // Cost per API request

// Generate a token for security
if (!isset($_SESSION['checker_token'])) {
    $_SESSION['checker_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['card'], $_POST['gate'], $_POST['token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters.']);
        exit;
    }

    $card = $_POST['card'];
    $gate = $_POST['gate'];
    $token = $_POST['token'];

    if ($token !== $_SESSION['checker_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid token.']);
        exit;
    }

    if ($userBalance < $costPerCheck) {
        echo json_encode(['status' => 'insufficient_balance', 'message' => 'Insufficient balance.']);
        exit;
    }

    // Deduct balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param('di', $costPerCheck, $userId);
    $stmt->execute();

    $apiResponse = null;
    if ($gate === 'gate1') {
        $apiResponse = file_get_contents("" . urlencode($card));
    } elseif ($gate === 'gate2') {
        $apiResponse = file_get_contents("" . urlencode($card));
    } elseif ($gate === 'gate3') {
        $apiResponse = file_get_contents("" . urlencode($card));
    }

    if (strpos($apiResponse, 'Live') !== false) {
        echo json_encode(['status' => 'live', 'card' => $card]);
    } else {
        echo json_encode(['status' => 'declined', 'card' => $card]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            font-family: 'Poppins', sans-serif;
            color: white;
            margin: 0;
        }

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

        .dashboard-content {
            margin-left: 270px;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            color: #4ef3c6;
            margin-bottom: 30px;
        }

        .box {
            background: linear-gradient(135deg, #232323, #191919);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-in-out;
        }

        textarea {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 10px;
            width: 100%;
            height: 150px;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-container {
            display: flex;
            gap: 15px;
        }

        .btn-success, .btn-danger {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .btn-success:hover, .btn-danger:hover {
            transform: scale(1.05);
        }

        #approvedResults p {
            color: #4caf50;
            margin: 5px 0;
        }

        #declinedResults p {
            color: #f44336;
            margin: 5px 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Kezzy Shop 🌟</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="buy_cc.php">Buy CC</a>
        <a href="my_cards.php">My Cards</a>
        <a href="add_balance.php">Add Balance</a>
        <a href="checker.php">Checker</a>
        <a href="tickets.php">Tickets</a>
    </div>

    <div class="dashboard-content">
        <h1>Checker</h1>

        <div class="box">
            <h4>Live Cards</h4>
            <div id="approvedResults"></div>
        </div>

        <div class="box">
            <h4>Declined Cards</h4>
            <div id="declinedResults"></div>
        </div>

        <div class="box">
            <h4>Card Checking</h4>
            <textarea id="cardList" placeholder="Enter cards, one per line"></textarea>
            <select id="gateSelect" class="form-control mt-3">
                <option value="gate1">GATE #1</option>
                <option value="gate2">GATE #2 - Bakım</option>
                <option value="gate3">GATE #3</option>
            </select>
            <div class="btn-container mt-3">
                <button class="btn btn-success" onclick="startChecking()">Start</button>
                <button class="btn btn-danger" onclick="stopChecking()">Stop</button>
            </div>
        </div>
    </div>

    <script>
        let index = 0;
        let cards = [];
        let isChecking = false;

        function startChecking() {
            const gate = document.getElementById('gateSelect').value;
            const cardInput = document.getElementById('cardList').value.trim();
            if (!cardInput) {
                alert('Please add cards to check.');
                return;
            }

            cards = cardInput.split('\n');
            index = 0;
            isChecking = true;
            processCard(gate);
        }

        function processCard(gate) {
            if (index >= cards.length) {
                alert('All cards checked.');
                isChecking = false;
                return;
            }

            const card = cards[index];
            fetch('checker.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `card=${encodeURIComponent(card)}&gate=${encodeURIComponent(gate)}&token=<?php echo $_SESSION['checker_token']; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'live') {
                    document.getElementById('approvedResults').innerHTML += `<p>${card} - Live</p>`;
                } else if (data.status === 'insufficient_balance') {
                    alert('Insufficient balance. Stopping checking process.');
                    isChecking = false;
                    return;
                } else {
                    document.getElementById('declinedResults').innerHTML += `<p>${card} - Declined</p>`;
                }

                index++;
                processCard(gate);
            })
            .catch(error => {
                alert('Error: ' + error.message);
                isChecking = false;
            });
        }

        function stopChecking() {
            alert('Checking process stopped.');
            isChecking = false;
        }
    </script>
</body>
</html>
