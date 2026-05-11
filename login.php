<?php
session_start();
require 'db.php';

// already logged in? redirect to portal
if (isset($_SESSION['user_id'])) {
    header('Location: seller_portal.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } else {
        // find user
        $stmt = $pdo->prepare("SELECT id, username, password FROM sellers WHERE username = ?");
        $stmt->execute([$username]);
        $seller = $stmt->fetch();

        if ($seller && password_verify($password, $seller['password'])) {
            $_SESSION['user_id'] = $seller['id'];
            $_SESSION['username'] = $seller['username'];
            header('Location: seller_portal.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Seller Login — CarTrader</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh; display: flex; flex-direction: column;
    }
    .top-nav {
      background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
      padding: 16px 40px; display: flex; justify-content: space-between; align-items: center;
    }
    .top-nav .logo {
      font-size: 24px; font-weight: 700; text-decoration: none;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .top-nav .nav-links { display: flex; gap: 32px; }
    .top-nav .nav-links a { color: #555; text-decoration: none; font-size: 14px; font-weight: 500; }
    .top-nav .nav-links a:hover { color: #667eea; }
    main {
      flex: 1; display: flex; align-items: center;
      justify-content: center; padding: 40px 20px;
    }
    .box {
      background: white; border-radius: 24px; padding: 48px;
      width: 100%; max-width: 420px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .box h1 { font-size: 28px; margin-bottom: 8px; color: #1a1a2e; }
    .box .sub { font-size: 14px; color: #888; margin-bottom: 32px; }
    .alert {
      padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;
      font-size: 14px; background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb;
    }
    .form-group { margin-bottom: 24px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
    input {
      width: 100%; padding: 14px 16px; border: 2px solid #e8e8e8;
      border-radius: 12px; font-size: 15px; font-family: inherit;
    }
    input:focus { outline: none; border-color: #667eea; }
    .btn {
      width: 100%; padding: 14px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white; border: none; border-radius: 12px;
      font-size: 16px; font-weight: 600; cursor: pointer;
    }
    .btn:hover { opacity: 0.9; }
    .tip { text-align: center; font-size: 14px; margin-top: 24px; color: #888; }
    .tip a { color: #667eea; text-decoration: none; }
  </style>
</head>
<body>
  <nav class="top-nav">
    <a href="homepage.html" class="logo">CarTrader</a>
    <div class="nav-links">
      <a href="homepage.html">Home</a>
      <a href="seller_portal.php">Seller Portal</a>
      <a href="searchpage.php">Search</a>
    </div>
  </nav>

  <main>
    <div class="box">
      <h1>Welcome Back</h1>
      <p class="sub">Login to manage your car listings</p>

      <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter your username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn">Login</button>
      </form>

      <div class="tip">Don't have an account? <a href="register.php">Register here</a></div>
    </div>
  </main>
</body>
</html>