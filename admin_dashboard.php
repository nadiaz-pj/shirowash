<?php
session_start();
include 'include/db.php';



// Ambil data ringkasan
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9f9f9; margin: 0; }
        nav { background: rgba(0,0,0,0.85); color: #fff; padding: 12px 40px; display: flex; justify-content: space-between; }
        nav .logo { font-weight: 700; }
        nav ul { list-style: none; display: flex; gap: 20px; }
        nav a { color: #fff; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 18px; }
        .summary { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); flex: 1; text-align: center; }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Admin Panel - ShiroWash AutoCare</div>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_products.php">Kelola Produk</a></li>
            <li><a href="admin_orders.php">Kelola Pesanan</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Dashboard Admin</h1>
        <div class="summary">
            <div class="card">
                <h3>Total Produk</h3>
                <p><?php echo $product_count; ?></p>
            </div>
            <div class="card">
                <h3>Total Pesanan</h3>
                <p><?php echo $order_count; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
