<?php
session_start();
include 'include/db.php';




// Fungsi kirim email sederhana
function send_email($to, $subject, $message) {
    $headers = "From: admin@shirowash.com\r\n";
    mail($to, $subject, $message, $headers);
}

// Handle kirim email pembayaran berhasil
if (isset($_GET['send_payment_success']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $stmt = $pdo->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $message = "Pembayaran untuk pesanan #$order_id telah berhasil dikonfirmasi. Total: Rp " . number_format($order['total'], 0, ',', '.') . ".";
        send_email($order['email'], "Pembayaran Berhasil - ShiroWash", $message);
        $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([$order_id]);
    }
    header('Location: admin_orders.php');
    exit;
}

// Handle kirim email pengiriman
if (isset($_GET['send_shipping']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $stmt = $pdo->prepare("SELECT o.*, u.email, u.address FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        $message = "Barang untuk pesanan #$order_id akan dikirim ke alamat: " . $order['address'] . ". Terima kasih.";
        send_email($order['email'], "Barang Akan Dikirim - ShiroWash", $message);
        $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?")->execute([$order_id]);
    }
    header('Location: admin_orders.php');
    exit;
}

// Ambil semua pesanan
$orders = $pdo->query("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola Pesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9f9f9; margin: 0; }
        nav { background: rgba(0,0,0,0.85); color: #fff; padding: 12px 40px; display: flex; justify-content: space-between; }
        nav a { color: #fff; text-decoration: none; margin: 0 10px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 18px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <div>Admin Panel</div>
        <div>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_products.php">Produk</a>
            <a href="admin_orders.php">Pesanan</a>
            <a href="admin_logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h1>Kelola Pesanan</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email User</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                    <td>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <?php if ($order['status'] === 'pending'): ?>
                            <a href="?send_payment_success=1&order_id=<?php echo $order['id']; ?>">Kirim Email Pembayaran Berhasil</a> |
                        <?php endif; ?>
                        <?php if ($order['status'] === 'paid'): ?>
                            <ahref="?send_shipping=1&order_id=<?php echo $order['id']; ?>">Kirim Email Pengiriman</a>
                        <?php endif; ?>
                        <?php if ($order['status'] === 'shipped'): ?>
                            <span>Sudah Dikirim</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>