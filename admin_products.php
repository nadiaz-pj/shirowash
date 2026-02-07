<?php
session_start();
include 'include/db.php';

// konfigurasi upload
$uploadDir = __DIR__ . '/uploads/';
$maxFileSize = 2 * 1024 * 1024; // 2MB
$allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];

// -------------------- HANDLE ADD PRODUCT --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name  = trim($_POST['name'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validasi input dasar
    if ($name === '' || $price < 0 || $stock < 0) {
        $flash = 'Data produk tidak valid.';
    } else {
        // Pastikan file ada di $_FILES
        if (!isset($_FILES['img'])) {
            $flash = 'File gambar tidak ditemukan. Pastikan form menggunakan enctype="multipart/form-data".';
        } else {
            $file = $_FILES['img'];

            // Cek error upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $flash = 'Terjadi kesalahan saat upload gambar. Error code: ' . $file['error'];
            } elseif ($file['size'] > $maxFileSize) {
                $flash = 'Ukuran file terlalu besar (maks 2MB).';
            } else {
                // Cek mime type server-side
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowedMime)) {
                    $flash = 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WEBP.';
                } else {
                    // Buat uploads folder jika belum ada
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Nama file aman & unik
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $base = pathinfo($file['name'], PATHINFO_FILENAME);
                    $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base);
                    $newName = time() . '_' . $safeBase . '.' . $ext;
                    $targetPath = $uploadDir . $newName;

                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $flash = 'Gagal menyimpan file di server.';
                    } else {
                        // Insert ke DB (simpan nama file, bukan path penuh)
                        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category, description, img) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $price, $stock, $category, $description, $newName]);

                        // Redirect supaya form tidak di-resubmit
                        header('Location: admin_products.php');
                        exit;
                    }
                }
            }
        }
    }
}

// -------------------- HANDLE UPDATE STOK --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = (int)$_POST['id'];
    $stock = (int)$_POST['stock'];

    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$stock, $id]);
    header('Location: admin_products.php');
    exit;
}

// -------------------- HANDLE DELETE PRODUCT --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['id'];

    // Ambil info file gambar dulu
    $stmt = $pdo->prepare("SELECT img FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        // Hapus record dari DB
        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmtDel->execute([$id]);

        // Hapus file fisik jika ada
        if (!empty($row['img'])) {
            $filePath = $uploadDir . $row['img'];
            if (is_file($filePath)) {
                @unlink($filePath); // gunakan @ untuk menekan warning jika gagal
            }
        }
    }
    header('Location: admin_products.php');
    exit;
}

// Ambil semua produk
$products = $pdo->query("SELECT * FROM products")->fetchAll();
// Ambil kategori
$categories = $pdo->query("SELECT category FROM products ORDER BY name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9f9f9; margin: 0; }
        nav { background: rgba(0,0,0,0.85); color: #fff; padding: 12px 40px; display: flex; justify-content: space-between; }
        nav a { color: #fff; text-decoration: none; margin: 0 10px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 18px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        form { margin: 20px 0; }
        input, select, textarea { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 6px; }
        button { padding: 8px 16px; background: #28a745; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        img.prod-thumb { border-radius: 6px; }
        .actions form { display: inline-block; margin-right: 6px; }
    </style>
    <script>
        function confirmDelete() {
            return confirm('Yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.');
        }
    </script>
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
        <h1>Kelola Produk</h1>

        <?php if (!empty($flash)): ?>
            <div style="background:#ffeeba;padding:10px;border-radius:6px;margin-bottom:12px;"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>
        
        <h2>Tambah Produk Baru</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_product" value="1">
            <input type="text" name="name" placeholder="Nama Produk" required>
            <input type="number" name="price" placeholder="Harga" required>
            <input type="number" name="stock" placeholder="Stok" required min="0">
            <select name="category" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['category']) ?>">
                        <?= htmlspecialchars($cat['category']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            <input type="text" name="variant" placeholder="variasi" required >
            <textarea name="description" placeholder="Deskripsi Produk" required style="display:block;width:50%;min-height:60px;"></textarea>
            <input type="file" name="img" accept="image/*" required>
            <button type="submit">Tambah</button>
        </form>

        <h2>Daftar Produk</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo (int)$product['id']; ?></td>
                    <td>
                        <?php if (!empty($product['img']) && is_file($uploadDir . $product['img'])): ?>
                            <img src="uploads/<?php echo rawurlencode($product['img']); ?>" width="60" class="prod-thumb" alt="">
                        <?php else: ?>
                            <span style="color:#999;">(no image)</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>Rp <?php echo number_format((int)$product['price'], 0, ',', '.'); ?></td>
                    <td><?php echo (int)$product['stock']; ?></td>
                    <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                    <td class="actions">
                        <!-- Form update stok -->
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="update_stock" value="1">
                            <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                            <input type="number" name="stock" value="<?php echo (int)$product['stock']; ?>" min="0" required style="width:80px;">
                            <button type="submit">Update</button>
                        </form>

                        <!-- Form delete terpisah -->
                        <form method="POST" onsubmit="return confirmDelete();" style="display:inline-block;">
                            <input type="hidden" name="delete_product" value="1">
                            <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                            <button type="submit" class="btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
