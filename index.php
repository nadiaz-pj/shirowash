<?php
session_start();
include 'include/db.php';  

$stmt = $pdo->query("SELECT * FROM products ORDER BY id");
$products = $stmt->fetchAll();
$bestsellers = array_filter($products, fn($p) => $p['bestseller']);
$others = array_filter($products, fn($p) => !$p['bestseller']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ShiroWash AutoCare</title>

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

    /* HERO */
    .hero {
      background: rgba(0,0,0,0.55);
      color: #fff;
      text-align: center;
      padding: 80px 20px;
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      gap:20px;
    }
    .hero h1 { font-size: 3rem; line-height:1.05; font-weight:700; max-width:900px; }
    .hero p  { color: #ddd; max-width:700px; font-size:1.05rem; }
    .hero .cta { display:inline-block; background:#007bff; color:#fff; padding:14px 28px; border-radius:10px; text-decoration:none; font-weight:700; box-shadow:0 6px 18px rgba(0,123,255,0.35); }
    .hero .cta:hover { background:#0056b3; }

    /* Main container */
    .container { width:100%; max-width:1200px; margin: 30px auto; padding: 0 18px 60px; }

    /* Section titles */
    .section-title { display:flex; align-items:center; justify-content:space-between; margin:18px 0; gap:12px; }
    .section-title h2 { font-size:1.4rem; color:#111; }
    .section-sub { color:#666; font-size:0.95rem; }

    /* Grid */
    .products-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap:18px;
    }
    .product-card {
      background:#fff; border-radius:12px; padding:16px; display:flex; flex-direction:column; align-items:center;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08); transition:transform .18s ease;
    }
    .product-card:hover { transform: translateY(-6px); }
    .product-card img { width:100%; max-width:220px; height:160px; object-fit:cover; border-radius:8px; margin-bottom:12px; }
    .product-card h3 { font-size:1.05rem; margin-bottom:8px; text-align:center; }
    .product-card p { color:#555; font-size:0.95rem; text-align:center; margin-bottom:12px; flex-grow:1; }

    .btn-detail {
      display:inline-block; padding:10px 14px; border-radius:8px; background:#007bff; color:#fff; text-decoration:none; font-weight:700;
      transition: background .15s, box-shadow .15s;
    }
    .btn-detail:hover { background:#0056b3; box-shadow: 0 6px 16px rgba(0,86,179,0.16); }

    /* Badge */
    .badge {
      display:inline-block; background:#ffbf00; color:#000; padding:6px 10px; border-radius:999px; font-weight:700; font-size:0.85rem;
    }

    footer { margin-top:auto; background:#111; color:#ccc; text-align:center; padding:14px 10px; }
    @media (max-width:720px) {
      .hero h1 { font-size:2rem; }
      .product-card img { height:140px; }
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

  <header class="hero" role="banner" aria-labelledby="hero-title">
    <h1 id="hero-title">Perawatan Premium untuk Kendaraan Anda</h1>
    <p>Solusi lengkap detailing dan perawatan mobil terpercaya </p>
      <p>Bersih, aman, dan membuat mobilmu kembali kinclong.</p>
    <a class="cta" href="products.php" role="button">Lihat Semua Produk</a>
  </header>

  <main class="container" role="main">
    <!-- Best Seller -->
    <section aria-labelledby="best-title">
      <div class="section-title">
        <h2 id="best-title">Best Seller</h2>
        <div class="section-sub">Produk paling populer dan paling banyak dibeli pelanggan kami.</div>
      </div>

      <?php if (!empty($bestsellers)): ?>
        <div class="products-grid">
          <?php foreach ($bestsellers as $p):
            $img = htmlspecialchars($p['img'], ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8');
          ?>
            <article class="product-card" aria-labelledby="prod-<?= (int)$p['id'] ?>">
              <img src="/shirowash/asset/<?= $img ?>" alt="<?= $name ?>">
              <h3 id="prod-<?= (int)$p['id'] ?>"><?= $name ?></h3>
              <p><?= $description ?></p>
              <div style="width:100%;display:flex;gap:10px;align-items:center;justify-content:center;">
                <span class="badge">Best Seller</span>
                <a class="btn-detail" href="detail.php">Detail Produk</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Tidak ada produk best seller untuk saat ini.</p>
      <?php endif; ?>
    </section>

    <!-- Spacer -->
    <hr style="margin:28px 0; border:none; height:1px; background:linear-gradient(90deg,#eee,#ddd,#eee)">

    <!-- Other Products -->
    <section aria-labelledby="other-title">
      <div class="section-title">
        <h2 id="other-title">Produk Lainnya</h2>
        <div class="section-sub">Telusuri kategori dan temukan produk yang sesuai kebutuhan Anda.</div>
      </div>

      <?php if (!empty($others)): ?>
        <div class="products-grid">
          <?php foreach ($others as $p):
            $img = htmlspecialchars($p['img'], ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8');
          ?>
            <article class="product-card" aria-labelledby="prod-<?= (int)$p['id'] ?>">
              <img src="uploads/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>"onerror="this.src='asset/no-image.png';">
              <h3 id="prod-<?= (int)$p['id'] ?>"><?= $name ?></h3>
              <p><?= $description ?></p>
              <a class="btn-detail" href="detail.php?id=<?= (int)$p['id'] ?>">Detail Produk</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Tidak ada produk lain saat ini.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer>
    &copy; <?= date('Y') ?> ShiroWash AutoCare. All Rights Reserved.
  </footer>
</body>
</html>
