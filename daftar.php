<?php
include ("include/db.php");
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password_user']), PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (name, email, password_user) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $password);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php");
        exit();
    } else {
        echo "Gagal mendaftar: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar</title>
  <style>
    body {
      background: url('../uploads/bg-hero.jpg') no-repeat center center/cover;
      font-family: Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    form {
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      width: 320px;
      text-align: center;
    }
    h2 { margin-bottom: 20px; }
    input {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #007bff;
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover { background: #0056b3; }
    p { margin-top: 10px; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <form method="POST" novalidate>
    <img src="/SHIROWASH_AutoCare/assets/uploads/logo-shirowash.png" alt="ShiroWash Logo" width="100">
    <h2>Daftar Akun</h2>

    <input type="text" name="name" placeholder="Nama Lengkap" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <input type="password" name="password_user" placeholder="Password" required>

    <button type="submit">Daftar</button>

   <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <p>Sudah punya akun? <a href="login.php">Login</a></p>
  </form>
</body>
</html>
