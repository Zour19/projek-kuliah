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
          <a class="icon-button" href="?page=create-account" aria-label="Account">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.31 0-6 1.79-6 4v1h12v-1c0-2.21-2.69-4-6-4Z" fill="currentColor"/>
            </svg>
          </a>
        <?php endif; ?>
        <a class="icon-button" href="?page=cart" aria-label="Cart">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 6h14l-1.5 7.2a2 2 0 0 1-2 1.55H9.5A2 2 0 0 1 7.5 13.6L6.5 4H3V2h4.5a1 1 0 0 1 1 1Zm2 14a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 9 20Zm9 0a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 18 20Z" fill="currentColor"/>
          </svg>
          <span class="cart-badge" id="cart-count">0</span>
        </a>
      </div>
    </header>
    <nav class="nav">
      <?= navItem('bouquet', 'Bouquets', $page) ?>
      <?= navItem('bloom-box', 'Bloom Boxes', $page) ?>
      <?= navItem('flowers', 'Flowers', $page) ?>
      <?= navItem('standing-flowers', 'Standing Flowers', $page) ?>
      <?= navItem('accessories', 'Accessories', $page) ?>
    </nav>
