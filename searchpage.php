<?php
session_start();
require 'db.php';

// get search keywords
$model = trim($_GET['model'] ?? '');
$year = trim($_GET['year'] ?? '');
$cars = [];

// build the query
$sql = "SELECT c.*, s.username FROM cars c 
        LEFT JOIN sellers s ON c.seller_id = s.id 
        WHERE 1=1";
$params = [];

if ($model !== '') {
    $sql .= " AND c.model LIKE ?";
    $params[] = "%$model%";
}

if ($year !== '') {
    $sql .= " AND c.year = ?";
    $params[] = $year;
}

$sql .= " ORDER BY c.added_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Cars - CarTrader</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-nav {
            background: white;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        .top-nav .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .nav-links { display: flex; gap: 32px; }
        .nav-links a { color: #555; text-decoration: none; font-size: 14px; font-weight: 500; }
        .nav-links a:hover { color: #667eea; }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 40px 20px;
            flex: 1;
            width: 100%;
        }
        .search-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .search-form {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .form-group { flex: 1; min-width: 180px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
        }
        input:focus { outline: none; border-color: #667eea; }
        .btn-search {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-search:hover { opacity: 0.9; }
        .clear-link {
            display: inline-block;
            margin-left: 15px;
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
        }
        .clear-link:hover {
            text-decoration: underline;
        }
        .results-count {
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .car-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #eee;
            transition: transform 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .car-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .car-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea30, #764ba230);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        .car-info { padding: 20px; }
        .car-model {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a1a2e;
        }
        .car-detail {
            font-size: 14px;
            color: #666;
            margin-bottom: 6px;
        }
        .car-price {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            margin-top: 12px;
        }
        .no-results {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 24px;
            color: #999;
        }
        footer {
            background: #1a1a2e;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            margin-top: 40px;
            flex-shrink: 0;
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

<div class="container">
    <div class="search-card">
        <form method="GET" action="searchpage.php" class="search-form">
            <div class="form-group">
                <label>🔍 Model</label>
                <input type="text" name="model" placeholder="e.g., Tesla, BMW, Camry" 
                       value="<?= htmlspecialchars($model) ?>">
            </div>
            <div class="form-group">
                <label>📅 Year</label>
                <input type="number" name="year" placeholder="e.g., 2022" 
                       value="<?= htmlspecialchars($year) ?>">
            </div>
            <button type="submit" class="btn-search">Search Cars</button>
            <?php if ($model !== '' || $year !== ''): ?>
                <a href="searchpage.php" class="clear-link">Clear filters</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="results-count">
        Found <strong><?= count($cars) ?></strong> car(s)
        <?php if ($model !== '' || $year !== ''): ?>
            <span style="color: #667eea;">(filtered)</span>
        <?php endif; ?>
    </div>

    <?php if (empty($cars)): ?>
        <div class="no-results">
            <div style="font-size: 48px; margin-bottom: 16px;">🚫</div>
            <h3>No cars found</h3>
            <p style="margin-top: 8px;">
                <?php if ($model !== '' || $year !== ''): ?>
                    Try different model name or year, or <a href="searchpage.php" style="color:#667eea;">view all cars</a>
                <?php else: ?>
                    No cars have been listed yet. Please check back later.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="cars-grid">
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <?php if (!empty($car['image_path']) && file_exists($car['image_path'])): ?>
                        <img src="<?= htmlspecialchars($car['image_path']) ?>" class="car-img" 
                             alt="<?= htmlspecialchars($car['model']) ?>" style="object-fit: cover;">
                    <?php else: ?>
                        <div class="car-img">🚗</div>
                    <?php endif; ?>
                    <div class="car-info">
                        <div class="car-model">
                            <?= htmlspecialchars($car['model']) ?> 
                            <span style="font-size: 14px; color:#999;">(<?= $car['year'] ?>)</span>
                        </div>
                        <?php if ($car['colour']): ?>
                            <div class="car-detail">🎨 Colour: <?= htmlspecialchars($car['colour']) ?></div>
                        <?php endif; ?>
                        <?php if ($car['location']): ?>
                            <div class="car-detail">📍 Location: <?= htmlspecialchars($car['location']) ?></div>
                        <?php endif; ?>
                        <div class="car-price">
                            ¥<?= number_format((float)$car['price'], 0, '.', ',') ?>
                        </div>
                        <div class="car-detail" style="margin-top: 8px; font-size: 12px;">
                            Listed by: <?= htmlspecialchars($car['username'] ?? 'Seller') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>&copy; 2026 CarTrader — Your trusted car marketplace</footer>

</body>
</html>