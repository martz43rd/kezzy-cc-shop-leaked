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

$api_url = "https://api.nowpayments.io/v1/payment";
$api_key = "FMAHWAE-1ZD4GHE-HR5T2SW-A6EC1N8";

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$userId = $_SESSION['user_id'];
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payAddress = isset($_POST['pay_address']) ? sanitize_input($_POST['pay_address']) : '';
    $payAmount = isset($_POST['pay_amount']) ? floatval($_POST['pay_amount']) : 0;

    if (empty($payAddress) || $payAmount <= 0) {
        $errorMessage = "Invalid payment details.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND pay_address = ? AND status = 'waiting'");
        if (!$stmt) {
            $errorMessage = "SQL Error: " . $conn->error;
        } else {
            $stmt->bind_param('is', $userId, $payAddress);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $errorMessage = "Payment not found or already processed.";
            } else {
                $payment = $result->fetch_assoc();
                $paymentId = $payment['id'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url . '/' . $paymentId);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "x-api-key: $api_key"
                ]);

                $response = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_status !== 200) {
                    $errorMessage = "Payment verification failed.";
                } else {
                    $response_data = json_decode($response, true);
                    if ($response_data['payment_status'] === 'finished') {
                        $amountInUSD = floatval($response_data['price_amount']);
                        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                        $stmt->bind_param('di', $amountInUSD, $userId);
                        $stmt->execute();

                        $stmt = $conn->prepare("UPDATE payments SET status = 'completed', updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param('i', $paymentId);
                        $stmt->execute();

                        $successMessage = "Payment successful! Your balance has been updated.";
                    } else {
                        $errorMessage = "Payment not completed. Please try again.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            font-family: 'Poppins', sans-serif;
            color: white;
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

        /* Main Content */
        .container {
            margin-left: 270px;
            margin-top: 50px;
            max-width: 600px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, #232323, #191919);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        h1 {
            color: #4ef3c6;
            text-shadow: 0px 0px 10px #4ef3c6, 0px 0px 20px #38a3a5;
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn-primary, .btn-secondary {
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            color: black;
        }

        .btn-primary:hover {
            box-shadow: 0px 0px 15px rgba(78, 243, 198, 0.7);
            transform: scale(1.05);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ff6a00, #ff4500);
            color: white;
        }

        .btn-secondary:hover {
            box-shadow: 0px 0px 15px rgba(255, 106, 0, 0.7);
            transform: scale(1.05);
        }

        /* Success and Error Messages */
        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success-message {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
        }

        .error-message {
            background: linear-gradient(135deg, #f44336, #b71c1c);
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>CC Shop 🌟</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="buy_cc.php">Buy CC</a>
        <a href="my_cards.php">My Cards</a>
        <a href="add_balance.php">Add Balance</a>
        <a href="checker.php">Checker</a>
        <a href="tickets.php">Tickets</a>
    </div>

    <div class="container">
        <h1>Confirm Payment</h1>
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="pay_address" value="<?php echo htmlspecialchars($payAddress ?? ''); ?>">
            <input type="hidden" name="pay_amount" value="<?php echo htmlspecialchars($payAmount ?? 0); ?>">
            <button type="submit" class="btn btn-primary">Check Payment Status</button>
        </form>
        <a href="add_balance.php" class="btn btn-secondary">New Payment Request</a>
    </div>
</body>
</html>
