<?php
include ("include/db.php");
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password_user']);

    $stmt = mysqli_prepare($koneksi, "SELECT id, email, password_user FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);

    if ($user && password_verify($password, $user['password_user'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
  <form method="POST">
    <img src="/shirowash/asset/logo-shirowash.png" alt="ShiroWash Logo" width="100">
    <h2>Login Akun</h2>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password_user" placeholder="Password" required>
    <button type="submit">Login</button>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <p>Belum punya akun? <a href="daftar.php">Daftar</a></p>
  </form>
</body>
</html>
