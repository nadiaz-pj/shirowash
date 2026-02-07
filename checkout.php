<?php
session_start();
include 'include/db.php';

// Pastikan ada keranjang dan metode pembayaran
if (empty($_SESSION['cart']) || !isset($_SESSION['payment_method'])) {
    header('Location: cart.php');
    exit;
}

// Hitung total seperti di cart.php
$cartItems = [];
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$item['id']]);
    $product = $stmt->fetch();
    if ($product) {
        $subtotal = $product['price'] * $item['quantity'];
        $total += $subtotal;
        $cartItems[] = array_merge($product, $item, ['subtotal' => $subtotal]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Checkout</title>
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
    .container { max-width: 1200px; margin: 30px auto; padding: 0 18px 60px; }
    .checkout-summary { background: #fff; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 20px; }
    .btn-confirm { background: #28a745; color: #fff; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
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
      <li><a href="profil.php">Profil</a></li>
    </ul>
  </nav>

  <main class="container">
    <h1>Ringkasan Checkout</h1>
    <div class="checkout-summary">
      <h3>Metode Pembayaran: <?= htmlspecialchars($_SESSION['payment_method']) ?></h3>
      <ul>
        <?php foreach ($cartItems as $item): ?>
          <li><?= htmlspecialchars($item['name']) ?></li>
          <p>Jumlah: <?= $item['quantity'] ?></p>
          <p>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
        <?php endforeach; ?>
      </ul>
      <h3>Total: Rp <?= number_format($total, 0, ',', '.') ?></h3>
            <button class="btn-confirm" onclick="confirmCheckout()">Konfirmasi Pembayaran</button>
    </div>
  </main>

  <footer>
    <p>&copy; 2023 ShiroWash AutoCare. Semua hak dilindungi.</p>
  </footer>

  <script>
    function confirmCheckout() {
      if (confirm('Apakah Anda yakin ingin melanjutkan pembayaran?')) {
        // Arahkan ke halaman proses checkout atau kirim data
        window.location.href = 'process_checkout.php'; // Ganti dengan URL yang sesuai
      }
    }
  </script>
</body>
</html>
