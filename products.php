<?php
session_start();
include 'include/db.php';  // Sertakan koneksi database

// Ambil kategori dari URL jika ada
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Query produk dengan filter kategori
if ($selectedCategory) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY id");
    $stmt->execute([$selectedCategory]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id");
}
$filteredProducts = $stmt->fetchAll();  // Gunakan $filteredProducts, bukan $products

// Dapatkan kategori unik untuk filter
$stmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Katalog Produk</title>
  <style>
    /* Gunakan style yang sama dengan index.php, disingkat dan fokus pada produk */
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
    h1{text-align:center; padding:40px 20px; font-size:2.8rem; font-weight:700;}

    /* Filter Kategori */
    .filter {text-align:center; margin-bottom:30px;}
    .filter a {display:inline-block; margin:0 10px; padding:8px 16px; background:#f8f9fa; color:#333; text-decoration:none; border-radius:6px; font-weight:500; transition:background 0.2s;}
    .filter a:hover, .filter a.active {background:#007bff; color:#fff;}

    .products {max-width:1140px; margin:0 auto 80px auto; padding:0 20px; display:grid; gap:30px; grid-template-columns:repeat(auto-fit, minmax(280px,1fr));}
    .product-card {background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); padding:18px; display:flex; flex-direction:column; align-items:center; transition:transform 0.25s ease, box-shadow 0.25s ease;}
    .product-card:hover {transform:translateY(-8px); box-shadow:0 20px 35px rgba(0,0,0,0.15);}
    .product-card img {width:100%; height:200px; object-fit:cover; border-radius:10px; margin-bottom:16px; user-select:none;}
    .product-card h3 {font-weight:700; font-size:1.25rem; margin-bottom:10px; color:#222; user-select:none;}
    .product-card p {font-size:0.95rem; color:#555; margin-bottom:20px; text-align:center; flex-grow:1; user-select:none;}

    /* Badge Best Seller */
    .badge {display:inline-block; background:#ffbf00; color:#000; padding:6px 10px; border-radius:999px; font-weight:700; font-size:0.85rem; margin-bottom:10px;}

    .product-actions {display:flex; gap:10px; width:100%; justify-content:center;}
    .btn-detail, .btn-add-cart {padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:600; font-size:0.9rem; transition:background 0.3s ease;}
    .btn-detail {background:#28a745; color:#fff;}
    .btn-detail:hover {background:#218838;}
    .btn-add-cart {background:#007bff; color:#fff; border:none; cursor:pointer;}
    .btn-add-cart:hover {background:#0056b3;}

    footer {background:#111; color:#ccc; text-align:center; padding:18px 10px; font-size:14px; font-weight:400; margin-top:auto; user-select:none;}
  </style>
</head>
<body>
  <nav>
    <div class="logo">
      <img src="asset/logo-shirowash.png" alt="Logo ShiroWash" />
      ShiroWash AutoCare
    </div>
    <ul>
      <li><a href="index.php">Beranda</a></li>
      <li><a href="products.php" aria-current="page">Produk</a></li>
      <li><a href="cart.php">Keranjang</a></li>
      <li><a href="profil.php">Profil</a></li>
    </ul>
  </nav>

  <h1>Daftar Produk ShiroWash AutoCare</h1>

  <!-- Filter Kategori -->
  <div class="filter">
    <a href="products.php" class="<?= !$selectedCategory ? 'active' : '' ?>">Semua</a>
    <?php foreach ($categories as $cat): ?>
      <a href="products.php?category=<?= urlencode($cat) ?>" class="<?= $selectedCategory === $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></a>
    <?php endforeach; ?>
  </div>

  <section class="products">
    <?php if (!empty($filteredProducts)): ?>
      <?php foreach ($filteredProducts as $product):  // Ganti $products dengan $filteredProducts ?>
        <article class="product-card">
          <img src="asset/<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
          <h3><?= htmlspecialchars($product['name']) ?></h3>
          <?php if (!empty($product['bestseller'])): ?>
            <span class="badge">Best Seller</span>
          <?php endif; ?>
          <p><?= htmlspecialchars($product['description']) ?></p>  <!-- Ganti 'desc' dengan 'description' -->
          <div class="product-actions">
            <a class="btn-detail" href="detail.php?id=<?= (int)$product['id'] ?>">Detail</a>
            <form action="cart.php" method="POST" style="display:inline;">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
              <button type="submit" class="btn-add-cart">Tambah ke Keranjang</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; grid-column:1/-1;">Tidak ada produk dalam kategori ini.</p>
    <?php endif; ?>
  </section>

  <footer>
    &copy; <?= date('Y') ?> ShiroWash AutoCare. All Rights Reserved.
  </footer>
</body>
</html>
