<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$api_url = "https://api.nowpayments.io/v1/payment";
$api_key = "FMAHWAE-1ZD4GHE-HR5T2SW-A6EC1N8";

$conn = new mysqli('localhost', 'root', '', 'ccshop');
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

$payAddress = '';
$payAmount = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $data = [
            "price_amount" => $amount,
            "price_currency" => "usd",
            "pay_currency" => "trx",
            "order_id" => $_SESSION['user_id'],
            "ipn_callback_url" => "https://13.53.52.84/callback.php" 
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-api-key: $api_key"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status === 201 && $response) {
            $response_data = json_decode($response, true);
            if (!empty($response_data['pay_address']) && !empty($response_data['pay_amount'])) {
                $payAddress = $response_data['pay_address'];
                $payAmount = $response_data['pay_amount'];

                $stmt = $conn->prepare("INSERT INTO payments (user_id, pay_address, status) VALUES (?, ?, 'waiting')");
                $stmt->bind_param('is', $_SESSION['user_id'], $payAddress);
                $stmt->execute();
            } else {
                $error = "API response did not contain expected information.";
            }
        } else {
            $error = "API request failed: HTTP $http_status.";
        }
    } else {
        $error = "Please enter a valid amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Balance</title>
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
            background: linear-gradient(135deg, #232323, #191919);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5);
            color: white;
        }

        h1 {
            text-align: center;
            color: #4ef3c6;
            text-shadow: 0px 0px 10px #4ef3c6, 0px 0px 20px #38a3a5;
            margin-bottom: 30px;
        }

        /* Buttons */
        .btn-primary, .btn-success {
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            color: black;
        }

        .btn-primary:hover {
            box-shadow: 0px 0px 15px rgba(78, 243, 198, 0.7);
            transform: scale(1.05);
        }

        .btn-success {
            background: linear-gradient(135deg, #56ab2f, #a8e063);
        }

        .btn-success:hover {
            box-shadow: 0px 0px 15px rgba(86, 171, 47, 0.7);
            transform: scale(1.05);
        }

        .info-card {
            background: #16213e;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        }

        .info-card h5 {
            font-size: 18px;
            color: #4ef3c6;
            margin-bottom: 10px;
        }

        .info-card p {
            color: #bbb;
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
        <h1>Add Balance</h1>
        <?php if (!empty($payAddress) && !empty($payAmount)): ?>
            <div class="info-card">
                <h5>Payment Information</h5>
                <p><strong>Pay to Address:</strong> <?php echo htmlspecialchars($payAddress); ?></p>
                <p><strong>Amount:</strong> <?php echo htmlspecialchars($payAmount); ?> TRX</p>
                <form method="POST" action="confirm_payment.php">
                    <input type="hidden" name="pay_address" value="<?php echo htmlspecialchars($payAddress); ?>">
                    <input type="hidden" name="pay_amount" value="<?php echo htmlspecialchars($payAmount); ?>">
                    <button type="submit" class="btn btn-success w-100">I have paid</button>
                </form>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (USD)</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Proceed</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
