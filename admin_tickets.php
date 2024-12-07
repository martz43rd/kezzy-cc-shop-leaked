<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ccshop');

// Tüm ticketları getir
$ticketsQuery = $conn->query("
    SELECT tickets.id, users.username, tickets.message, tickets.admin_reply, tickets.created_at 
    FROM tickets 
    INNER JOIN users ON tickets.user_id = users.id
    ORDER BY tickets.created_at DESC
");

// Admin cevabı
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = intval($_POST['ticket_id']);
    $reply = trim($_POST['reply']);
    if (!empty($reply)) {
        $stmt = $conn->prepare("UPDATE tickets SET admin_reply = ? WHERE id = ?");
        $stmt->bind_param("si", $reply, $ticketId);
        $stmt->execute();
        header("Location: admin_tickets.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Ticket Yönetimi</h1>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Mesaj</th>
                    <th>Admin Cevabı</th>
                    <th>Gönderildiği Tarih</th>
                    <th>Cevap Ver</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ticket = $ticketsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['message']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['admin_reply'] ?? 'Cevaplanmadı'); ?></td>
                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                <textarea name="reply" class="form-control mb-2" rows="2" placeholder="Cevap yazın..."></textarea>
                                <button type="submit" class="btn btn-success btn-sm">Gönder</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
