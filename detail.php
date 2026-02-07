<?php
session_start();
include 'include/db.php';  // Sertakan koneksi database

// Ambil ID produk dari URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Query produk berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

// Jika produk tidak ditemukan, redirect
if (!$product) {
    header('Location: index.php');
    exit;
}

// Query varian produk (jika ada)
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_type, variant_value");
$stmt->execute([$productId]);
$variants = $stmt->fetchAll();

// Kelompokkan varian berdasarkan tipe (misal: color => ['Merah', 'Biru'], size => ['Small', 'Large'])
$groupedVariants = [];
foreach ($variants as $variant) {
    $groupedVariants[$variant['variant_type']][] = $variant['variant_value'];
}

// Sanitasi data
$img = htmlspecialchars($product['img'], ENT_QUOTES, 'UTF-8');
$name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8');
$category = htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8');
$isBestseller = $product['bestseller'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detail Produk - <?= $name ?> | ShiroWash AutoCare</title>

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

    /* Main container */
    .container { width:100%; max-width:1200px; margin: 30px auto; padding: 0 18px 60px; }

    /* Product Detail */
    .product-detail {
      display: flex;
      gap: 40px;
      align-items: flex-start;
      margin-bottom: 40px;
    }
    .product-image {
      flex: 1;
      max-width: 400px;
    }
    .product-image img {
      width: 100%;
      height: auto;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    .product-info {
      flex: 2;
    }
    .product-info h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #111;
    }
    .product-category {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 20px;
    }
    .product-desc {
      color: #555;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 20px;
    }
    .badge {
      display: inline-block;
      background: #ffbf00;
      color: #000;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.85rem;
      margin-bottom: 20px;
    }

    /* Varian Produk */
    .product-variants {
      margin-bottom: 20px;
    }
    .product-variants h3 {
      font-size: 1.1rem;
      margin-bottom: 10px;
      color: #333;
    }
    .variant-group {
      margin-bottom: 15px;
    }
    .variant-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
    .variant-options {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .variant-option {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      background: #f8f9fa;
      cursor: pointer;
      transition: background 0.2s;
    }
    .variant-option:hover, .variant-option.selected {
      background: #007bff;
      color: #fff;
      border-color: #007bff;
    }

    .btn-add-cart {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 8px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      transition: background .15s, box-shadow .15s;
      border: none;
      cursor: pointer;
    }
    .btn-add-cart:hover {
      background: #0056b3;
      box-shadow: 0 6px 16px rgba(0,86,179,0.16);
    }
    .btn-back {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 8px;
      background: #6c757d;
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      margin-left: 10px;
      transition: background .15s;
    }
    .btn-back:hover {
      background: #545b62;
    }

    footer { margin-top:auto; background:#111; color:#ccc; text-align:center; padding:14px 10px; }
    @media (max-width:720px) {
      .product-detail {
        flex-direction: column;
        gap: 20px;
      }
      .product-image {
        max-width: 100%;
      }
      .product-info h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="logo">
      <img src="/shirowash/asset/logo-shirowash.png" alt="Logo ShiroWash">
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
    <section class="product-detail" aria-labelledby="product-title">
      <div class="product-image">
        <img src="/shirowash/uploads/<?= $img ?>" alt="<?= $name ?>">
      </div>
      <div class="product-info">
        <h1 id="product-title"><?= $name ?></h1>
        <div class="product-category">Kategori: <?= $category ?></div>
        <?php if ($isBestseller): ?>
          <div class="badge">Best Seller</div>
        <?php endif; ?>
        <p class="product-desc"><?= $description ?></p>

        <!-- Varian Produk -->
        <?php if (!empty($groupedVariants)): ?>
          <div class="product-variants">
            <h3>Pilih Varian</h3>
            <form id="variant-form" action="cart.php" method="POST">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="id" value="<?= $productId ?>">
              <?php foreach ($groupedVariants as $type => $values): ?>
                <div class="variant-group">
                  <label for="variant-<?= $type ?>"><?= ucfirst($type) ?>:</label>
                  <div class="variant-options" id="variant-<?= $type ?>">
                    <?php foreach ($values as $value): ?>
                      <div class="variant-option" data-type="<?= $type ?>" data-value="<?= $value ?>" onclick="selectVariant('<?= $type ?>', '<?= $value ?>')">
                        <?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <input type="hidden" name="variant[<?= $type ?>]" id="selected-<?= $type ?>" value="">
                </div>
                 <p class="product-price" style="font-size:1.4rem; font-weight:700; margin-bottom:20px;">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
              <?php endforeach; ?>
              <button type="submit" class="btn-add-cart">Tambah ke Keranjang</button>
            </form>
          </div>
        <?php else: ?>
          <form action="cart.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= $productId ?>">
            <button type="submit" class="btn-add-cart">Tambah ke Keranjang</button>
            <a class="btn-back" href="index.php">Kembali ke Beranda</a>
          </form>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer>
    &copy; <?= date('Y') ?> ShiroWash AutoCare. All Rights Reserved.
  </footer>

  <script>
    function selectVariant(type, value) {
      // Hapus seleksi sebelumnya
      const options = document.querySelectorAll(`#variant-${type} .variant-option`);
      options.forEach(opt => opt.classList.remove('selected'));
      
      // Pilih opsi baru
      event.target.classList.add('selected');
      
      // Set nilai hidden input
      document.getElementById(`selected-${type}`).value = value;
    }
  </script>
</body>
</html>