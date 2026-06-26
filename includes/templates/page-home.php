<?php if ($page === 'home'): ?>
      <section class="hero hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-copy hero-copy--centered">
          <div class="hero-subtitle">Featured Collection</div>
          <h2>Basket & Bouquet for every special moment</h2>
          <p>Temukan rangkaian bunga segar yang dirangkai khusus untuk momen romantis, ulang tahun, syukuran, atau kejutan sederhana untuk orang terkasih.</p>
          <div class="hero-actions">
            <a class="button-primary" href="?page=bouquet">Shop Bouquets</a>
            <a class="button-secondary" href="?page=bloom-box">Browse Bloom Boxes</a>
          </div>
        </div>
      </section>
      <section class="page-title">
        <h1>Highlight collection</h1>
        <p>Rangkaian terbaik kami yang paling sering dipesan, dikemas rapi, dan siap untuk dikirim.</p>
      </section>
      <section class="catalog-grid">
        <?php foreach ($featuredProducts as $item): ?>
          <article class="product-card">
            <?php $imageUrl = resolveProductImage($item, 'bouquet'); ?>
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
              <button class="wide-button primary add-to-cart" type="button" data-product='<?= json_encode(['id'=> (int)$item['id'], 'name'=> $item['name'], 'price'=> (int)$item['price']], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>'>Tambah ke Keranjang</button>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
<?php endif; ?>
