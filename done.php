<?php
session_start();
include 'include/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart']) || !isset($_SESSION['payment_method'])) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$item['id']]);
    $product = $stmt->fetch();
    if ($product) {
        $total += $product['price'] * $item['quantity'];
    }
}

$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, total, payment_method, status, created_at)
    VALUES (?, ?, ?, 'pending', NOW())
");
$stmt->execute([$_SESSION['user_id'], $total, $_SESSION['payment_method']]);
$order_id = $pdo->lastInsertId();

foreach ($_SESSION['cart'] as $item) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$item['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, price, quantity)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['id'],
            $product['price'],
            $item['quantity']
        ]);
    }
}

unset($_SESSION['cart']);
unset($_SESSION['payment_method']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pesanan Berhasil</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    *{margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif;}
    body{background:#f9f9f9; color:#333; min-height:100vh; display:flex; flex-direction:column;}
    nav {background:rgba(0,0,0,0.85);color:#fff;display:flex; justify-content:space-between; align-items:center; padding:12px 40px; position: sticky; top:0;box-shadow:0 2px 8px rgba(0,0,0,0.4);}
    nav .logo {display:flex; align-items:center; gap:12px; font-weight:700; font-size:22px; color: white;user-select:none;}
    nav .logo img {width:48px; height:48px; object-fit:contain;}
    nav ul {list-style:none; display:flex; gap:28px; font-weight:500;}
    nav ul li a {color:white; text-decoration:none; padding:6px 0; position:relative; transition:color 0.3s ease;}
    nav ul li a::after {content:''; position:absolute; width:0; height:2px; bottom:-4px; left:0; background:#00bfff; transition:width 0.3s ease;}
    nav ul li a:hover, nav ul li a:focus {color:#00bfff;}
    nav ul li a:hover::after, nav ul li a:focus::after {width:100%;}
    .container { max-width: 1200px; margin: 30px auto; padding: 0 18px 60px; text-align: center; }
    .success-message { background: #fff; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 40px; }
    .btn-back { background: #007bff; color: #fff; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px; }
    footer { background: #111; color: #ccc; text-align: center; padding: 14px; margin-top: auto; }
  </style>
</head>
<body>
  <nav>
    <div class="logo">
      <img src="asset/logo-shirowash.png" alt="Logo ShiroWash">
      ShiroWash AutoCare
    </div>
    <ul>
      <li><a href="index.php">Beranda</a></li>
      <li><a href="products.php">Produk</a></li>
      <li><a href="cart.php">Keranjang</a></li>
      <li><a href="profil.php">Logout</a></li>
    </ul>
  </nav>

  <main class="container">
    <div class="success-message">
      <h1>Pesanan Berhasil!</h1>
      <p>Terima kasih telah berbelanja di ShiroWash AutoCare. Pesanan Anda dengan ID #<?= $order_id ?> telah diterima dan sedang diproses.</p>
      <p>Total Pembayaran: Rp <?= number_format($total, 0, ',', '.') ?> via <?= htmlspecialchars($_SESSION['payment_method'] ?? 'Tidak diketahui') ?>.</p>
      <p>Anda akan menerima konfirmasi lebih lanjut via email atau WhatsApp.</p>
      <button class="btn-back" onclick="window.location.href='index.php'">Kembali ke Beranda</button>
      <button class="btn-back" onclick="window.location.href='products.php'">Lanjut Belanja</button>
    </div>
  </main>

  <footer>
    <p>&copy; 2023 ShiroWash AutoCare. Semua hak dilindungi.</p>
  </footer>
</body>
</html>