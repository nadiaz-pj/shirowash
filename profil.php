<?php
session_start();
include 'include/db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f8;
            margin: 0;
        }

        nav {
            background: #111;
            padding: 14px 36px;
            color: #fff;
            display: flex;
            justify-content: space-between;
        }

        nav a { 
            color:#fff; text-decoration:none; font-weight:500; margin-left: 18px;
        }
        nav a:hover { 
            color:#00bfff; 
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
            color: #111;
        }

        .profile-item {
            margin-bottom: 14px;
        }

        .profile-item label {
            display: block;
            color: #666;
            font-size: 0.9rem;
        }

        .profile-item span {
            font-weight: 600;
            font-size: 1rem;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            text-align: center;
        }

        .btn-logout {
            background: #dc3545;
            color: #fff;
        }

        .btn-logout:hover {
            background: #b02a37;
        }

        .btn-home {
            background: #007bff;
            color: #fff;
        }

        .btn-home:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<nav>
    <div>ShiroWash AutoCare</div>
    <div>
        <a href="index.php">Beranda</a>
        <a href="cart.php">Keranjang</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Profil Saya</h2>

    <div class="profile-item">
        <label>Nama</label>
        <span><?= htmlspecialchars($user['name']) ?></span>
    </div>

    <div class="profile-item">
        <label>Email</label>
        <span><?= htmlspecialchars($user['email']) ?></span>
    </div>

    <div class="profile-item">
        <label>Bergabung Sejak</label>
        <span><?= date('d M Y', strtotime($user['created_at'])) ?></span>
    </div>

    <h3 style="margin-top:30px;">Pesanan Saya</h3>

<?php if (empty($orders)): ?>
    <p>Belum ada pesanan.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div style="border:1px solid #ddd; border-radius:10px; padding:16px; margin-bottom:14px;">
            <p><strong>ID Pesanan:</strong> #<?= $order['id'] ?></p>
            <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($order['created_at'])) ?></p>
            <p><strong>Status:</strong>
                <span style="
                    padding:4px 10px;
                    border-radius:20px;
                    background:<?= $order['status']=='pending'?'#ffc107':'#28a745' ?>;
                    color:#000;
                    font-size:0.85rem;
                ">
                    <?= ucfirst($order['status']) ?>
                </span>
            </p>
            <p><strong>Total:</strong> Rp <?= number_format($order['total'],0,',','.') ?></p>

            <!-- Detail Produk -->
            <details style="margin-top:10px;">
                <summary style="cursor:pointer;font-weight:600;">Detail Produk</summary>
                <ul>
                <?php
                $stmt = $pdo->prepare("
                    SELECT oi.*, p.name
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order['id']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($items as $item):
                ?>
                    <li>
                        <?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?>
                        (Rp <?= number_format($item['price'],0,',','.') ?>)
                    </li>
                <?php endforeach; ?>
                </ul>
            </details>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

    <div class="actions">
        <a href="index.php" class="btn btn-home">Kembali</a>
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
</div>

</body>
</html>
