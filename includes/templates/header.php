<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(pageTitle($page)) ?> — Matahari Florist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="page">
    <header class="site-header">
      <a class="brand" href="?page=home">Matahari Florist</a>
      <div class="header-actions">
        <?php $customerUser = $_SESSION['customer_user'] ?? null; ?>
        <?php if ($customerUser): ?>
          <div class="header-user" id="user-state">
            <span class="header-user__name">Halo, <?= htmlspecialchars((string) ($customerUser['username'] ?? 'Customer')) ?></span>
            <button class="text-link" type="button" id="logout-button">Logout</button>
          </div>
        <?php else: ?>
          <a class="icon-button" href="?page=create-account" aria-label="Account">👤</a>
        <?php endif; ?>
        <a class="icon-button" href="?page=cart" aria-label="Cart">🛒<span class="cart-badge" id="cart-count">0</span></a>
      </div>
    </header>
    <nav class="nav">
      <?= navItem('bouquet', 'Bouquets', $page) ?>
      <?= navItem('bloom-box', 'Bloom Boxes', $page) ?>
      <?= navItem('flowers', 'Flowers', $page) ?>
      <?= navItem('standing-flowers', 'Standing Flowers', $page) ?>
      <?= navItem('accessories', 'Accessories', $page) ?>
    </nav>
