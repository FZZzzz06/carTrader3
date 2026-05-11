<?php
session_start();
require 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

$my_cars = [];
if ($is_logged_in) {
    // get seller's cars
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE seller_id = ? ORDER BY added_at DESC");
    $stmt->execute([$user_id]);
    $my_cars = $stmt->fetchAll();
}

$total_cars = (int)$pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$my_count = count($my_cars);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Portal — CarTrader</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif; background-color: #f8f9fa;
      color: #333; line-height: 1.6; display: flex; flex-direction: column; min-height: 100vh;
    }
    .top-nav {
      background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
      padding: 16px 40px; display: flex; align-items: center;
      justify-content: space-between; box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    }
    .top-nav .logo {
      font-size: 24px; font-weight: 700; text-decoration: none;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .top-nav .nav-links { display: flex; align-items: center; gap: 24px; }
    .top-nav .nav-links a { color: #555; text-decoration: none; font-size: 14px; font-weight: 500; }
    .top-nav .nav-links a:hover { color: #667eea; }
    .btn-logout {
      background: white; color: #e74c3c; border: 2px solid #e74c3c;
      padding: 6px 16px; border-radius: 10px; font-size: 13px;
      font-weight: 600; text-decoration: none;
    }
    .btn-logout:hover { background: #e74c3c; color: white !important; }
    main { flex: 1; padding: 40px 20px; max-width: 1200px; width: 100%; margin: 0 auto; }
    .page-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 30px; flex-wrap: wrap; gap: 12px;
    }
    .page-header h1 { font-size: 32px; color: #1a1a2e; }
    .btn {
      padding: 12px 24px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white; border: none; border-radius: 12px;
      font-size: 14px; font-weight: 600; cursor: pointer;
      text-decoration: none; display: inline-block;
    }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-outline {
      background: white; color: #667eea; border: 2px solid #667eea;
    }
    .welcome-banner {
      background: linear-gradient(135deg, #667eea15, #764ba215);
      padding: 20px 30px; border-radius: 20px; margin-bottom: 30px;
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 15px;
    }
    .welcome-text h2 { font-size: 22px; color: #667eea; }
    .welcome-text p { color: #666; font-size: 14px; margin-top: 5px; }
    .info-cards {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px; margin-bottom: 40px;
    }
    .info-card {
      background: white; border: 1px solid #e8e8e8; border-radius: 16px;
      padding: 24px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .info-card .number {
      font-size: 36px; font-weight: bold;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 8px;
    }
    .info-card .label { font-size: 14px; color: #888; }
    .action-buttons {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px; margin-bottom: 40px;
    }
    .action-card {
      background: white; border: 1px solid #e8e8e8; border-radius: 16px;
      padding: 30px 20px; text-align: center; text-decoration: none;
      transition: all 0.3s; display: block;
    }
    .action-card:hover {
      transform: translateY(-5px); box-shadow: 0 10px 30px rgba(102,126,234,0.2);
      border-color: #667eea;
    }
    .action-icon { font-size: 48px; margin-bottom: 16px; }
    .action-card h3 { font-size: 18px; color: #333; margin-bottom: 8px; }
    .action-card p { font-size: 13px; color: #888; }
    .my-cars-section {
      background: white; border-radius: 24px;
      padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .my-cars-section h2 { font-size: 24px; margin-bottom: 20px; color: #1a1a2e; }
    .my-cars-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;
    }
    .car-card {
      background: #f9f9f9; border-radius: 16px;
      overflow: hidden; border: 1px solid #eee; transition: transform 0.2s;
    }
    .car-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
    .car-img { width: 100%; height: 160px; object-fit: cover; background: #ddd; }
    .car-img-placeholder {
      width: 100%; height: 160px;
      background: linear-gradient(135deg, #667eea30, #764ba230);
      display: flex; align-items: center; justify-content: center;
      font-size: 40px;
    }
    .car-info { padding: 16px; }
    .car-model { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
    .car-text { font-size: 13px; color: #666; margin-bottom: 4px; }
    .empty-cars { text-align: center; padding: 40px; color: #999; font-size: 15px; }
    footer {
      background: #1a1a2e; color: rgba(255,255,255,0.7);
      text-align: center; padding: 20px; font-size: 13px;
    }
  </style>
</head>
<body>

  <nav class="top-nav">
    <a href="homepage.html" class="logo">CarTrader</a>
    <div class="nav-links">
      <a href="homepage.html">Home</a>
      <a href="searchpage.php">Search</a>
      <?php if ($is_logged_in): ?>
        <a href="add_car.php" class="btn" style="padding:8px 18px;">+ Add Car</a>
        <a href="logout.php" class="btn-logout">Logout</a>
      <?php else: ?>
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <main>
    <div class="page-header">
      <h1>Seller Portal</h1>
      <?php if (!$is_logged_in): ?>
        <div>
          <a href="register.php" class="btn btn-outline" style="margin-right:10px;">Register</a>
          <a href="login.php" class="btn">Login</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($is_logged_in): ?>
      <div class="welcome-banner">
        <div class="welcome-text">
          <h2>Welcome back, <?= htmlspecialchars($username) ?>!</h2>
          <p>Manage your car listings and track performance</p>
        </div>
        <a href="logout.php" class="btn" style="background:white;color:#e74c3c;border:2px solid #e74c3c;">Logout</a>
      </div>
    <?php endif; ?>

    <div class="info-cards">
      <div class="info-card">
        <div class="number"><?= $is_logged_in ? $my_count : '—' ?></div>
        <div class="label">My Listings</div>
      </div>
      <div class="info-card">
        <div class="number"><?= $total_cars ?></div>
        <div class="label">Total Cars on Site</div>
      </div>
    </div>

    <div class="action-buttons">
      <a href="add_car.php" class="action-card">
        <div class="action-icon">➕</div>
        <h3>Add New Car</h3>
        <p>List a new vehicle</p>
      </a>
      <a href="searchpage.php" class="action-card">
        <div class="action-icon">🔍</div>
        <h3>Browse All Cars</h3>
        <p>View marketplace</p>
      </a>
      <?php if (!$is_logged_in): ?>
      <a href="register.php" class="action-card">
        <div class="action-icon">📝</div>
        <h3>Register</h3>
        <p>Create a seller account</p>
      </a>
      <a href="login.php" class="action-card">
        <div class="action-icon">🔐</div>
        <h3>Login</h3>
        <p>Access your dashboard</p>
      </a>
      <?php endif; ?>
    </div>

    <?php if ($is_logged_in): ?>
    <div class="my-cars-section">
      <h2>🚗 My Listed Cars</h2>
      <?php if (empty($my_cars)): ?>
        <div class="empty-cars">
          📭 You haven't listed any cars yet.
          <a href="add_car.php" style="color:#667eea;">Add your first car →</a>
        </div>
      <?php else: ?>
        <div class="my-cars-grid">
          <?php foreach ($my_cars as $car): ?>
            <div class="car-card">
              <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                <img src="<?= htmlspecialchars($car['image_path']) ?>"
                     class="car-img" alt="<?= htmlspecialchars($car['model']) ?>">
              <?php else: ?>
                <div class="car-img-placeholder">🚗</div>
              <?php endif; ?>
              <div class="car-info">
                <div class="car-model">
                  <?= htmlspecialchars($car['model']) ?> (<?= (int)$car['year'] ?>)
                </div>
                <?php if ($car['colour']): ?>
                  <div class="car-text">🎨 <?= htmlspecialchars($car['colour']) ?></div>
                <?php endif; ?>
                <?php if ($car['location']): ?>
                  <div class="car-text">📍 <?= htmlspecialchars($car['location']) ?></div>
                <?php endif; ?>
                <div class="car-text">
                  💰 ¥<?= number_format((float)$car['price'], 0, '.', ',') ?>
                </div>
                <div class="car-text" style="color:#aaa;font-size:12px;">
                  Added: <?= date('Y-m-d', strtotime($car['added_at'])) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </main>

  <footer>&copy; 2026 CarTrader — Your trusted car marketplace</footer>
</body>
</html>