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
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';

// Submit a ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO tickets (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $message);
        $stmt->execute();
        $successMessage = "Your ticket has been successfully submitted. We will respond shortly.";
    } else {
        $errorMessage = "Please enter a message.";
    }
}

// Fetch user's tickets
$ticketsQuery = $conn->prepare("SELECT message, admin_reply, created_at FROM tickets WHERE user_id = ?");
$ticketsQuery->bind_param("i", $userId);
$ticketsQuery->execute();
$tickets = $ticketsQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
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

        /* Dashboard Content */
        .dashboard-content {
            margin-left: 270px;
            padding: 20px;
        }

        .dashboard-content h1, h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #4ef3c6;
            margin-bottom: 30px;
        }

        .form-container {
            background: linear-gradient(135deg, #232323, #191919);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-in-out;
            margin-bottom: 30px;
        }

        textarea {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 15px;
            width: 100%;
            height: 150px;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            border: none;
            color: black;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0px 0px 10px rgba(78, 243, 198, 0.8);
        }

        .table-container {
            background: linear-gradient(135deg, #232323, #191919);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-in-out;
        }

        .table-dark {
            border-radius: 15px;
            overflow: hidden;
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
        <h1>Tickets</h1>
        <div class="form-container">
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php elseif (isset($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <form method="POST">
                <textarea name="message" id="message" placeholder="Describe your issue or query..."></textarea>
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
        </div>

        <h2>Your Tickets</h2>
        <div class="table-container">
            <table class="table table-dark table-striped mt-3">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Admin Reply</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ticket = $tickets->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['message']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['admin_reply'] ?? 'No reply yet'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
