<?php
session_start();
include 'include/db.php';  // Sertakan koneksi database

// Pastikan user sudah login (tambahan untuk keamanan)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST: Tambah item ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' && isset($_POST['id'])) {
        $productId = (int)$_POST['id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $variants = isset($_POST['variant']) ? $_POST['variant'] : [];

        // Cek apakah item sudah ada di keranjang
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $productId && $item['variants'] == $variants) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $productId,
                'quantity' => $quantity,
                'variants' => $variants
            ];
        }
        header('Location: cart.php');  // Redirect untuk menghindari resubmit
        exit;
    } elseif ($_POST['action'] === 'update' && isset($_POST['update'])) {
        // Update quantity
        foreach ($_POST['update'] as $index => $newQty) {
            if (isset($_SESSION['cart'][$index])) {
                $_SESSION['cart'][$index]['quantity'] = max(1, (int)$newQty);  // Minimal 1
            }
        }
        header('Location: cart.php');
        exit;
    } elseif ($_POST['action'] === 'remove' && isset($_POST['remove'])) {
        // Hapus item
        $index = (int)$_POST['remove'];
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);  // Reindex array
        }
        header('Location: cart.php');
        exit;
    } elseif ($_POST['action'] === 'checkout') {
        // Simpan metode pembayaran dan redirect ke checkout
        $_SESSION['payment_method'] = $_POST['payment_method'];
        header('Location: process_checkout.php');  // Arahkan ke halaman checkout (perbaiki nama file jika perlu)
        exit;
    }
}

// Ambil detail produk untuk setiap item di keranjang
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
  <title>Keranjang</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    * { box-sizing: border-box; margin:0; padding:0; }
    html,body { height:100%; }
    body {
      font-family: 'Poppins', sans-serif;
      color: #222;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: #f9f9f9;
    }

    /* NAV */
    nav {
      background: rgba(0,0,0,0.85);
      color: #fff;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:12px 36px;
      position: sticky;
      top:0;
      z-index:1000;
      box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    }
    .logo { display:flex; gap:12px; align-items:center; font-weight:700; }
    .logo img { width:48px; height:48px; object-fit:contain; }
    nav ul { list-style:none; display:flex; gap:24px; align-items:center; }
    nav a { color:#fff; text-decoration:none; font-weight:500; }
    nav a:hover { color:#00bfff; }

    /* Container */
    .container { width:100%; max-width:1200px; margin: 30px auto; padding: 0 18px 60px; }

    /* Cart Table */
    .cart-table {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      overflow: hidden;
      margin-bottom: 30px;
    }
    .cart-table table {
      width: 100%;
      border-collapse: collapse;
    }
    .cart-table th, .cart-table td {
      padding: 16px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    .cart-table th {
      background: #f8f9fa;
      font-weight: 600;
    }
    .cart-item {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
}

    .quantity-input {
      width: 60px;
      padding: 6px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    .btn-update, .btn-remove {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    .btn-update { background: #28a745; color: #fff; }
    .btn-update:hover { background: #218838; }
    .btn-remove { background: #dc3545; color: #fff; }
    .btn-remove:hover { background: #c82333; }

    /* Total */
    .cart-total {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      padding: 20px;
      text-align: right;
      margin-bottom: 30px;
    }
    .cart-total h3 {
      margin-bottom: 10px;
      color: #333;
    }

    /* Checkout */
    .checkout-form {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      padding: 20px;
    }
    .checkout-form h3 {
      margin-bottom: 20px;
      color: #333;
    }
    .payment-method {
      margin-bottom: 20px;
    }
    .payment-method label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    .payment-method select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    .btn-checkout {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 8px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      border: none;
      cursor: pointer;
      transition: background .15s;
    }
    .btn-checkout:hover {
      background: #0056b3;
    }

    /* Empty Cart */
    .empty-cart {
      text-align: center;
      padding: 40px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    footer { margin-top:auto; background:#111; color:#ccc; text-align:center; padding:14px 10px; }
    @media (max-width:720px) {
      .cart-table table { font-size: 0.9rem; }
      .cart-table th, .cart-table td { padding: 10px; }
    }
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

  <main class="container" role="main">
    <h1>Keranjang Belanja</h1>

    <?php if (!empty($cartItems)): ?>
      <form action="cart.php" method="POST">
        <input type="hidden" name="action" value="update">
        <div class="cart-table">
          <table>
            <thead>
              <tr>
                <th>Produk</th>
                <th>Nama</th>
                <th>Variant</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $index => $item): ?>
                <tr>
                  <td><img src="asset/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item"></td>
                  <td><?= htmlspecialchars($item['name']) ?></td>
                  <td>
                    <?php if (!empty($item['variants'])): ?>
                      <?php foreach ($item['variants'] as $type => $value): ?>
                        <?= ucfirst($type) ?>: <?= htmlspecialchars($value) ?><br>
                      <?php endforeach; ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td><input type="number" name="update[<?= $index ?>]" value="<?= $item['quantity'] ?>" min="1" class="quantity-input"></td>
                  <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                  <td>
                    <button type="submit" name="action" value="update" class="btn-update">Update</button>
                    <button type="submit" name="action" value="remove" class="btn-remove" onclick="this.form.remove.value='<?= $index ?>'">Hapus</button>
                    <input type="hidden" name="remove" value="">
                    </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="cart-total">
        <h3>Total: Rp <?= number_format($total, 0, ',', '.') ?></h3>
      </div>
      </form>

      <div class="checkout-form">
        <h3>Checkout</h3>
        <form action="cart.php" method="POST">
          <input type="hidden" name="action" value="checkout">
          <div class="payment-method">
            <label for="payment_method">Metode Pembayaran:</label>
            <select name="payment_method" id="payment_method" required>
              <option value="">Pilih Metode</option>
              <option value="transfer_bank">Transfer Bank</option>
              <option value="cod">Cash on Delivery (COD)</option>
              <option value="ewallet">E-Wallet (GoPay, OVO, dll.)</option>
            </select>
          </div>
          <button type="submit" class="btn-checkout">Checkout</button>
        </form>
      </div>
    <?php else: ?>
      <div class="empty-cart">
        <h2>Keranjang Kosong</h2>
        <p>Tambah produk ke keranjang untuk melanjutkan.</p>
        <a href="products.php" class="btn-checkout">Lihat Produk</a>
      </div>
    <?php endif; ?>
  </main>

  <footer>
    &copy; <?= date('Y') ?> ShiroWash AutoCare. All Rights Reserved.
  </footer>
</body>
</html>