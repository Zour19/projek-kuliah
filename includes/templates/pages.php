<?php
require_once __DIR__ . '/page-home.php';
?>

<?php if ($page === 'blogs'): ?>
      <section class="page-title">
        <h1>Blogs</h1>
        <p>Kumpulan artikel inspirasi, tips menyimpan bunga, dan cerita dari dunia florist.</p>
      </section>
      <section class="blog-grid">
        <?php foreach ($blogPosts as $post): ?>
          <article class="blog-card">
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
            <div class="card-body">
              <h3><?= htmlspecialchars($post['title']) ?></h3>
              <p><?= htmlspecialchars($post['text']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php elseif ($page === 'create-account'): ?>
      <section class="page-title">
        <h1>Create Account</h1>
        <p>Sudah punya akun? <a class="text-link" href="?page=login">Sign in here.</a></p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <h2>Create Account</h2>
          <p>Daftar sekarang untuk menikmati layanan pengiriman bunga cepat dan personalisasi ucapan gratis.</p>
          <form id="create-account-form">
            <div class="field"><label for="account-username">Username</label><input id="account-username" name="username" type="text" placeholder="Username" required></div>
            <div class="field"><label for="account-email">Email</label><input id="account-email" name="email" type="email" placeholder="Email" required></div>
            <div class="field"><label for="account-password">Password</label><input id="account-password" name="password" type="password" placeholder="Password" required></div>
            <button class="wide-button primary" type="submit">Create Account</button>
            <div id="create-account-message" style="margin-top:18px;"></div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'login'): ?>
      <section class="page-title">
        <h1>Login</h1>
        <p>Don't have an account? <a class="text-link" href="?page=create-account">Sign up here.</a></p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <h2>Login</h2>
          <p>Masuk untuk melihat status pesanan Anda dan simpan detail checkout lebih cepat.</p>
          <form id="customer-login-form">
            <div class="field"><label for="login-identifier">Email atau Username</label><input id="login-identifier" name="identifier" type="text" placeholder="Email atau Username" required></div>
            <div class="field"><label for="login-password">Password</label><input id="login-password" name="password" type="password" placeholder="Password" required></div>
            <div class="form-actions">
              <button class="wide-button primary" type="submit">Sign in</button>
              <a class="text-link" href="?page=reset-password">Forgot your password?</a>
            </div>
            <div id="login-message" style="margin-top:24px;"></div>
            <div style="margin-top:24px;"><a class="button-secondary" href="?page=home" style="display:inline-flex;">Return to store</a></div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'shop'): ?>
      <section class="page-title">
        <h1>Shop</h1>
        <p>Skenario belanja ringan: pilih produk, tambahkan ke keranjang, lalu lanjutkan ke checkout.</p>
      </section>
      <section class="catalog-grid shop-grid" id="shop-items">
        <?php foreach (get_all_products() as $item): ?>
          <article class="product-card">
            <?php $imageUrl = resolveProductImage($item, $item['category_slug'] ?? null); ?>
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
              <button class="wide-button primary add-to-cart" type="button" data-product='<?= json_encode(['id'=>(int)$item['id'],'name'=>$item['name'],'price'=>(int)$item['price']], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>'>Tambah ke Keranjang</button>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php elseif ($page === 'reset-password'): ?>
      <section class="page-title">
        <h1>Reset Your Password</h1>
        <p>We will send you an email to reset your password.</p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <form>
            <div class="field"><label for="reset-email">Email</label><input id="reset-email" type="email" placeholder="Email"></div>
            <div class="form-actions">
              <button class="wide-button primary" type="button">Submit</button>
              <a class="wide-button secondary" href="?page=login">Cancel</a>
            </div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'contact-us'): ?>
      <section class="page-title">
        <h1>Contact Us</h1>
        <p>Butuh bantuan atau ingin konsultasi rangkaian bunga terbaik? Hubungi kami atau kunjungi toko.</p>
      </section>
      <div class="content-panel"><div class="panel-body"><p>Alamat: Jl. Sulaiman No.12A 10, Sukabumi Utara, Jakarta Barat</p><p>Telp/WA: +6282122490002</p><p>Email: admin@matahariflorist.com</p></div></div>
    <?php elseif ($page === 'our-story'): ?>
      <section class="page-title">
        <h1>Our Story</h1>
        <p>Kisah singkat Matahari Florist dan bagaimana kami melayani setiap momen dengan kehangatan dan kualitas bunga terbaik.</p>
      </section>
      <div class="content-panel"><div class="panel-body"><h2>From local garden to your hands</h2><p>Kami memulai perjalanan karena cinta akan bunga segar yang bisa memeriahkan suasana hati. Setiap rangkaian dirakit oleh tim florist berpengalaman dengan detail personal yang membuat hadiah Anda tetap berkesan.</p><h2>Committed to quality</h2><p>Kualitas dan kesegaran selalu menjadi prioritas. Bunga kami dipilih setiap hari, disimpan dengan suhu ideal, dan dikirimkan dengan kemasan elegan agar sampai tujuan dalam kondisi prima.</p></div></div>
    <?php elseif ($page === 'cart'): ?>
      <section class="page-title"><h1>Keranjang Pesanan</h1><p>Review item Anda sebelum lanjut ke proses pembayaran. Pastikan alamat dan jumlah sudah sesuai.</p></section>
      <section class="summary-panel">
        <div class="panel-body">
          <table>
            <thead><tr><th>Nama Produk</th><th>Qty</th><th>Harga</th></tr></thead>
            <tbody id="cart-items">
              <tr><td colspan="3" style="text-align:center;color:var(--muted);">Keranjang masih kosong. Tambahkan produk dari halaman Shop.</td></tr>
            </tbody>
            <tfoot>
              <tr class="total-row"><td>Total</td><td></td><td id="cart-total">Rp 0</td></tr>
            </tfoot>
          </table>
          <div class="form-actions" style="margin-top:24px; gap:12px; display:flex; flex-wrap:wrap;">
            <a class="wide-button secondary" href="?page=shop">Kembali Belanja</a>
            <button id="checkout-button" class="wide-button primary" type="button">Checkout</button>
          </div>
          <div id="cart-empty-message" class="content-panel" style="display:none;margin-top:20px;">
            <div class="panel-body"><p>Keranjang kosong. Ayo pilih produk di halaman Shop.</p></div>
          </div>
        </div>
      </section>
    <?php elseif ($page === 'checkout'): ?>
      <section class="page-title"><h1>Proses Pembayaran</h1><p>Isi data pengiriman lalu lanjutkan pembayaran melalui WhatsApp.</p></section>
      <div class="content-panel">
        <div class="panel-body">
          <div id="checkout-summary">
            <h2>Ringkasan Pesanan</h2>
            <table>
              <thead><tr><th>Nama Produk</th><th>Qty</th><th>Harga</th></tr></thead>
              <tbody id="checkout-items">
                <tr><td colspan="3" style="text-align:center;color:var(--muted);">Tidak ada item di keranjang.</td></tr>
              </tbody>
              <tfoot><tr class="total-row"><td>Total</td><td></td><td id="checkout-total">Rp 0</td></tr></tfoot>
            </table>
          </div>
          <h2>Payment details</h2>
          <form id="checkout-form">
            <div class="field"><label for="card-name">Nama Pemesan</label><input id="card-name" type="text" placeholder="Nama lengkap"></div>
            <div class="field"><label for="expiry">Tanggal Pesan</label><input id="expiry" type="text" placeholder="MM/YY"></div>
            <button class="wide-button primary" type="button" id="pay-now-button">Pay Now</button>
          </form>
          <div id="checkout-message" class="admin-message" style="display:none;margin-top:18px;"></div>
        </div>
      </div>
    <?php elseif ($page === 'admin-login'): ?>
      <section class="page-title"><h1>Admin Login</h1><p>Gunakan akun admin untuk menambah produk buket dan katalog.</p></section>
      <section class="form-panel"><div class="panel-body"><?php if ($adminError): ?><div class="admin-message admin-error"><?= htmlspecialchars($adminError) ?></div><?php endif; ?><form method="post"><div class="field"><label for="admin-username">Username</label><input id="admin-username" name="username" type="text" required></div><div class="field"><label for="admin-password">Password</label><input id="admin-password" name="password" type="password" required></div><button class="wide-button primary" type="submit">Login Admin</button></form></div></section>
    <?php elseif ($page === 'admin-dashboard'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Admin Dashboard</h1><p>Kelola produk, lihat kategori, dan tambahkan item baru dari panel admin.</p></section>
      <div class="content-panel">
        <div class="panel-body">
          <h2>Admin Panel</h2>
          <p>Gunakan tombol di bawah untuk menambahkan produk, melihat daftar produk, atau mengelola asset.</p>
          <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:24px;">
            <a class="wide-button primary" href="?page=add-product">Tambah Produk Baru</a>
            <a class="wide-button secondary" href="?page=admin-products">Lihat Semua Produk</a>
            <a class="wide-button secondary" href="?page=admin-assets">Asset Sorter</a>
          </div>
          <div style="margin-top:32px;">
            <h3>Kategori Produk</h3>
            <ul>
              <?php foreach ($categories as $cat): ?>
                <li><a href="?page=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php elseif ($page === 'add-product'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Tambah Produk</h1><p>Tambah buket atau produk baru ke katalog dengan mudah.</p></section>
      <section class="form-panel">
        <div class="panel-body">
          <?php if ($adminError): ?>
            <div class="admin-message admin-error"><?= htmlspecialchars($adminError) ?></div>
          <?php endif; ?>
          <?php if ($adminSuccess): ?>
            <div class="admin-message admin-success"><?= htmlspecialchars($adminSuccess) ?></div>
          <?php endif; ?>
          <form method="post" enctype="multipart/form-data">
            <div class="field">
              <label for="product-name">Nama Produk</label>
              <input id="product-name" name="name" type="text" required>
            </div>
            <div class="field">
              <label for="product-price">Harga</label>
              <input id="product-price" name="price" type="number" min="1" required>
            </div>
            <div class="field">
              <label for="product-category">Kategori</label>
              <select id="product-category" name="category" required>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="product-image-upload">Upload Gambar</label>
              <input id="product-image-upload" name="image_upload" type="file" accept="image/*">
              <p class="hint">Gunakan upload atau masukkan path gambar di bawah.</p>
            </div>
            <div class="field">
              <label for="product-image">Atau masukkan path gambar</label>
              <input id="product-image" name="image" type="text" placeholder="assets/images/bouquets/filename.jpg">
            </div>
            <button class="wide-button primary" type="submit">Simpan Produk</button>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'admin-assets'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="section-admin-assets">
        <h2>Asset Sorter</h2>
        <p>Tarik dan lepas foto di bawah, atau pilih beberapa file sekaligus. File akan disortir berdasarkan nama file ke folder kategori yang sesuai.</p>

        <?php if (!empty($adminError)): ?>
            <div class="alert alert-danger"><?= htmlentities($adminError) ?></div>
        <?php endif; ?>

        <form id="asset-upload-form" action="?page=admin-assets" method="post" enctype="multipart/form-data" class="upload-form">
            <div id="upload-drop-area" class="upload-drop-area">
                <p>Tarik & lepas gambar di sini</p>
                <p>atau</p>
                <button type="button" class="button-secondary" id="browse-files">Pilih Foto</button>
                <input type="file" name="asset_upload[]" id="asset-upload" accept="image/*" multiple hidden>
            </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <button type="submit" class="button-primary">Proses Foto</button>
            <button type="button" id="scan-unsorted" class="button-secondary">Scan Unsorted</button>
          </div>
        </form>
        <div id="asset-sort-results" class="asset-sort-results" style="display:none;"></div>
    </section>

    <?php elseif ($page === 'admin-products'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Daftar Produk</h1><p>Semua produk yang terdaftar di database.</p></section>
      <section class="content-panel">
        <div class="panel-body">
          <?php if ($adminError): ?><div class="admin-message admin-error"><?= htmlspecialchars($adminError) ?></div><?php endif; ?>
          <?php if ($adminSuccess): ?><div class="admin-message admin-success"><?= htmlspecialchars($adminSuccess) ?></div><?php endif; ?>
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Gambar</th>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Nama</th>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Kategori</th>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Harga</th>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Tanggal</th>
                <th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($allProducts)): ?>
                <tr><td colspan="6" style="padding:18px;text-align:center;color:var(--muted);">Belum ada produk.</td></tr>
              <?php else: ?>
                <?php foreach ($allProducts as $item): ?>
                  <?php $itemImageUrl = resolveProductImage($item, $item['category_slug'] ?? null); ?>
                  <tr>
                    <td style="padding:12px;"><img src="<?= htmlspecialchars($itemImageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:12px;"></td>
                    <td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['name']) ?></td>
                    <td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['category_name']) ?></td>
                    <td style="padding:12px;vertical-align:middle;"><?= formatPrice((int)$item['price']) ?></td>
                    <td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['created_at']) ?></td>
                    <td style="padding:12px;vertical-align:middle;">
                      <form method="post" onsubmit="return confirm('Hapus produk ini?');" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= (int)$item['id'] ?>">
                        <button class="wide-button secondary" type="submit" style="width:auto;padding:10px 14px;">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php elseif ($category): ?>
      <section class="page-title"><h1><?= htmlspecialchars($category['name']) ?></h1><p>Koleksi <?= htmlspecialchars($category['name']) ?> terpilih dengan desain premium dan harga terbaik.</p></section>
      <section class="catalog-grid">
        <?php foreach ($products as $item): ?>
          <article class="product-card">
            <?php $imageUrl = resolveProductImage($item, $category['slug'] ?? null); ?>
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
              <button class="wide-button primary add-to-cart" type="button" data-product='<?= json_encode(['id'=> (int)$item['id'], 'name'=> $item['name'], 'price'=> (int)$item['price']], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>'>Tambah ke Keranjang</button>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (empty($products)): ?><p>Tidak ada produk di kategori ini.</p><?php endif; ?>
      </section>
    <?php endif; ?>
