<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullName'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $agree = isset($_POST['agree']);

    // validation
    if (!$full_name || !$email || !$username || !$password) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,30}$/', $username)) {
        $error = 'Username must be 6–30 alphanumeric characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!$agree) {
        $error = 'You must agree to the Terms.';
    } else {
        // check if username exists
        $stmt = $pdo->prepare("SELECT id FROM sellers WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists. Please choose another.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO sellers (full_name, address, phone, email, username, password)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$full_name, $address, $phone, $email, $username, $hashed]);
            $success = 'Registration successful! Please login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Registration — CarTrader</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #333; line-height: 1.6; min-height: 100vh;
    }
    .top-nav {
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      padding: 16px 40px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .top-nav .logo {
      font-size: 24px; font-weight: 700; text-decoration: none;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .top-nav .nav-links { display: flex; gap: 32px; }
    .top-nav .nav-links a { color: #555; text-decoration: none; font-size: 14px; font-weight: 500; }
    .top-nav .nav-links a:hover { color: #667eea; }
    main { display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
    .container {
      display: flex; width: 100%; max-width: 1000px;
      background: white; border-radius: 24px; overflow: hidden;
      box-shadow: 0 25px 80px rgba(0,0,0,0.25);
    }
    .left-panel {
      flex: 1;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 50px 40px; display: flex; flex-direction: column;
      justify-content: center; color: white;
    }
    .left-panel h2 { font-size: 32px; font-weight: 700; margin-bottom: 16px; }
    .left-panel p { font-size: 16px; opacity: 0.9; margin-bottom: 40px; }
    .features { display: flex; flex-direction: column; gap: 20px; }
    .feature { display: flex; align-items: center; gap: 14px; }
    .feature-icon {
      width: 44px; height: 44px; background: rgba(255,255,255,0.2);
      border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .right-panel { flex: 1; padding: 50px; }
    .form-header { margin-bottom: 32px; }
    .form-header h1 { font-size: 28px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
    .alert {
      padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;
    }
    .alert-error { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; font-weight: 600; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: #444; }
    .req { color: #e74c3c; }
    input[type=text], input[type=email], input[type=password], input[type=tel] {
      width: 100%; padding: 14px 16px; border: 2px solid #e8e8e8;
      border-radius: 12px; font-size: 15px; font-family: inherit;
      transition: all 0.3s; background: #fafafa;
    }
    input:focus { outline: none; border-color: #667eea; background: white; }
    .btn {
      width: 100%; padding: 16px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white; border: none; border-radius: 12px;
      font-size: 15px; font-weight: 600; cursor: pointer;
    }
    .btn:hover { transform: translateY(-2px); }
    .tip { text-align: center; font-size: 14px; color: #888; margin-top: 24px; }
    .tip a { color: #667eea; text-decoration: none; }
    @media (max-width: 768px) {
      .container { flex-direction: column; }
      .top-nav { padding: 16px 20px; }
    }
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
    <div class="container">
      <div class="left-panel">
        <h2>Join CarTrader Today</h2>
        <p>Start selling your vehicles to thousands of potential buyers.</p>
        <div class="features">
          <div class="feature"><div class="feature-icon">🚗</div><span>List unlimited vehicles</span></div>
          <div class="feature"><div class="feature-icon">📊</div><span>Track your listings</span></div>
          <div class="feature"><div class="feature-icon">🔒</div><span>Secure transactions</span></div>
        </div>
      </div>

      <div class="right-panel">
        <div class="form-header">
          <h1>Create Account</h1>
          <p>Fill in your details to become a seller</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <div class="tip" style="margin-top:12px;">
            <a href="login.php">Click here to Login →</a>
          </div>
        <?php else: ?>

        <form method="post" action="register.php">
          <div class="form-group">
            <label>Full Name <span class="req">*</span></label>
            <input type="text" name="fullName" placeholder="Enter your full name"
                   value="<?= htmlspecialchars($_POST['fullName'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" placeholder="Your address"
                   value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="e.g. 13800138000"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" placeholder="example@mail.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Username <span class="req">*</span></label>
            <input type="text" name="username" placeholder="At least 6 characters"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Password <span class="req">*</span></label>
            <input type="password" name="password" placeholder="At least 6 characters">
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" name="agree" <?= isset($_POST['agree']) ? 'checked' : '' ?>>
              I agree to the Terms
            </label>
          </div>
          <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="tip">Already have an account? <a href="login.php">Sign in</a></div>

        <?php endif; ?>
      </div>
    </div>
  </main>

</body>
</html>
