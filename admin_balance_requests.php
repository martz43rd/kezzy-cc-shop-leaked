<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');

// Talepleri listele
$requestsQuery = $conn->query("SELECT balance_requests.id, users.username, balance_requests.amount, balance_requests.status FROM balance_requests INNER JOIN users ON balance_requests.user_id = users.id WHERE balance_requests.status = 'pending'");

// Talep onaylama veya reddetme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Talebi onayla
        $approveQuery = $conn->prepare("UPDATE balance_requests SET status = 'approved' WHERE id = ?");
        $approveQuery->bind_param("i", $requestId);
        $approveQuery->execute();

        // Kullanıcının bakiyesini artır
        $updateBalanceQuery = $conn->prepare("UPDATE users INNER JOIN balance_requests ON users.id = balance_requests.user_id SET users.balance = users.balance + balance_requests.amount WHERE balance_requests.id = ?");
        $updateBalanceQuery->bind_param("i", $requestId);
        $updateBalanceQuery->execute();

    } elseif ($action === 'reject') {
        // Talebi reddet
        $rejectQuery = $conn->prepare("UPDATE balance_requests SET status = 'rejected' WHERE id = ?");
        $rejectQuery->bind_param("i", $requestId);
        $rejectQuery->execute();
    }
    header("Location: admin_balance_requests.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakiye Talepleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Bakiye Talepleri</h1>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($request = $requestsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                        <td>$<?php echo number_format($request['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button name="action" value="approve" class="btn btn-success btn-sm">Onayla</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button name="action" value="reject" class="btn btn-danger btn-sm">Reddet</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
