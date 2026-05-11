<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$seller_id = (int)$_SESSION['user_id'];
$username  = $_SESSION['username'];
$success   = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model    = trim($_POST['model']    ?? '');
    $year     = (int)($_POST['year']   ?? 0);
    $price    = (float)($_POST['price'] ?? 0);
    $colour   = trim($_POST['colour']   ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!$model || !$year || $price <= 0) {
        $error = 'Car model, year, and a valid price are required.';
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = 'Please enter a valid year.';
    } else {
        $image_path = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                $error = 'Only JPG / PNG / WEBP images are allowed.';
            } else {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'car_' . uniqid() . '.' . strtolower($ext);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/' . $filename;
                } else {
                    $error = 'Image upload failed. Please try again.';
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare(
                "INSERT INTO cars (seller_id, model, year, price, colour, location, image_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$seller_id, $model, $year, $price, $colour, $location, $image_path]);
            $success = 'Car added successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Car — CarTrader</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh; display: flex; flex-direction: column;
    }
    .top-nav {
      background: #fff; padding: 16px 40px;
      display: flex; justify-content: space-between; align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .top-nav .logo {
      font-size: 24px; font-weight: 700;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent; text-decoration: none;
    }
    .nav-links { display: flex; gap: 32px; align-items: center; }
    .nav-links a { color: #555; text-decoration: none; font-size: 14px; font-weight: 500; }
    .nav-links a:hover { color: #667eea; }
    .nav-links .logout {
      background: #e74c3c; color: white; padding: 6px 14px;
      border-radius: 8px; font-size: 13px;
    }
    .nav-links .logout:hover { background: #c0392b; color: white; }
    .container { max-width: 900px; margin: 40px auto; padding: 0 20px; flex: 1; }
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 28px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.15); }
    .user-bar {
      background: rgba(255,255,255,0.15); border-radius: 12px;
      padding: 10px 18px; color: white; font-size: 14px; margin-bottom: 20px;
    }
    .card { background: #fff; border-radius: 24px; padding: 30px; margin-bottom: 30px; }
    .alert {
      padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;
    }
    .alert-error { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; font-weight: 600; }
    .form-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
    .form-group { flex: 1; min-width: 200px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #333; }
    .req { color: #e74c3c; }
    .form-group input, .form-group input[type=file] {
      width: 100%; padding: 14px 16px; border: 1px solid #e8e8e8;
      border-radius: 12px; font-size: 15px; font-family: inherit;
    }
    .form-group input:focus { outline: none; border-color: #667eea; }
    .form-group input[type=file] { padding: 10px 16px; background: #fafafa; cursor: pointer; }
    .btn-submit {
      width: 100%; padding: 16px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff; border: none; border-radius: 12px;
      font-weight: 600; font-size: 16px; cursor: pointer; margin-top: 10px;
    }
    .btn-submit:hover { opacity: 0.9; }
    .portal-link { display: inline-block; margin-top: 12px; color: #667eea; font-size: 14px; }
    footer {
      background: #1a1a2e; color: #fff;
      text-align: center; padding: 20px; font-size: 13px;
    }
  </style>
</head>
<body>

<div class="top-nav">
  <a href="homepage.html" class="logo">CarTrader</a>
  <div class="nav-links">
    <a href="seller_portal.php">Seller Portal</a>
    <a href="searchpage.php">Search</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <div class="page-header">
    <h1>Add New Car</h1>
  </div>

  <div class="user-bar">
    Logged in as: <strong><?= htmlspecialchars($username) ?></strong>
    &nbsp;|&nbsp; <a href="seller_portal.php" style="color:#fff;">← My Portal</a>
  </div>

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <a href="seller_portal.php" class="portal-link">← View my listings in Seller Portal</a>
    <?php endif; ?>

    <form method="post" action="add_car.php" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group">
          <label>Car Model <span class="req">*</span></label>
          <input type="text" name="model" placeholder="e.g., Tesla Model 3"
                 value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Year <span class="req">*</span></label>
          <input type="number" name="year" placeholder="e.g., 2024" min="1900" max="<?= date('Y') + 1 ?>"
                 value="<?= htmlspecialchars($_POST['year'] ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Price (¥) <span class="req">*</span></label>
          <input type="number" name="price" placeholder="e.g., 299900" min="1" step="0.01"
                 value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Colour</label>
          <input type="text" name="colour" placeholder="e.g., Red, Black"
                 value="<?= htmlspecialchars($_POST['colour'] ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" placeholder="e.g., Beijing, Shanghai"
                 value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Car Image (optional)</label>
          <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp">
        </div>
      </div>

      <button type="submit" class="btn-submit">+ Add Car</button>
    </form>
  </div>
</div>

<footer>&copy; 2026 CarTrader — Your trusted car marketplace</footer>
</body>
</html>
