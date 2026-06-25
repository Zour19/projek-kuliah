document.addEventListener('DOMContentLoaded', function () {
  const brand = document.querySelector('.brand');
  if (brand) {
    brand.addEventListener('click', () => {
      window.location.href = '?page=home';
    });
  }

  const createAccountForm = document.getElementById('create-account-form');
  const accountMessage = document.getElementById('create-account-message');
  if (createAccountForm && accountMessage) {
    createAccountForm.addEventListener('submit', function (event) {
      event.preventDefault();
      accountMessage.textContent = '';

      const payload = {
        username: document.getElementById('account-username').value.trim(),
        email: document.getElementById('account-email').value.trim(),
        password: document.getElementById('account-password').value,
      };

      fetch('api.php?action=create_account', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
        .then((response) => response.json())
        .then((json) => {
          accountMessage.textContent = json.message || 'Terjadi kesalahan.';
          accountMessage.style.color = json.success ? '#1f5f32' : '#7d2222';
        })
        .catch(() => {
          accountMessage.textContent = 'Server tidak merespons. Coba lagi nanti.';
          accountMessage.style.color = '#7d2222';
        });
    });
  }

  const cartCount = document.getElementById('cart-count');
  const cartItemsContainer = document.getElementById('cart-items');
  const cartTotal = document.getElementById('cart-total');
  const checkoutItems = document.getElementById('checkout-items');
  const checkoutTotal = document.getElementById('checkout-total');
  const checkoutButton = document.getElementById('checkout-button');
  const payNowButton = document.getElementById('pay-now-button');
  const checkoutMessage = document.getElementById('checkout-message');

  function readCart() {
    const stored = localStorage.getItem('matahari_cart');
    return stored ? JSON.parse(stored) : [];
  }

  function writeCart(items) {
    localStorage.setItem('matahari_cart', JSON.stringify(items));
    renderCart();
  }

  function renderCart() {
    const items = readCart();
    if (cartCount) {
      cartCount.textContent = items.reduce((sum, item) => sum + item.qty, 0);
    }

    if (!cartItemsContainer || !cartTotal) {
      return;
    }

    if (!items.length) {
      cartItemsContainer.innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--muted);">Keranjang masih kosong. Tambahkan produk dari halaman Shop.</td></tr>';
      cartTotal.textContent = 'Rp 0';
      return;
    }

    const rows = items.map((item) => `
      <tr>
        <td>${item.name}</td>
        <td>${item.qty}</td>
        <td>Rp ${item.price.toLocaleString('id-ID')}</td>
      </tr>
    `).join('');

    const totalValue = items.reduce((sum, item) => sum + item.price * item.qty, 0);
    cartItemsContainer.innerHTML = rows;
    cartTotal.textContent = `Rp ${totalValue.toLocaleString('id-ID')}`;
  }

  function renderCheckout() {
    const items = readCart();
    if (!checkoutItems || !checkoutTotal) {
      return;
    }

    if (!items.length) {
      checkoutItems.innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--muted);">Tidak ada item di keranjang.</td></tr>';
      checkoutTotal.textContent = 'Rp 0';
      return;
    }

    const rows = items.map((item) => `
      <tr>
        <td>${item.name}</td>
        <td>${item.qty}</td>
        <td>Rp ${item.price.toLocaleString('id-ID')}</td>
      </tr>
    `).join('');

    const totalValue = items.reduce((sum, item) => sum + item.price * item.qty, 0);
    checkoutItems.innerHTML = rows;
    checkoutTotal.textContent = `Rp ${totalValue.toLocaleString('id-ID')}`;
  }

  function addProductToCart(product) {
    const items = readCart();
    const existing = items.find((item) => item.id === product.id);
    if (existing) {
      existing.qty += 1;
    } else {
      items.push({ ...product, qty: 1 });
    }
    writeCart(items);
  }

  document.querySelectorAll('.add-to-cart').forEach((button) => {
    button.addEventListener('click', () => {
      const productData = button.dataset.product;
      if (productData) {
        const product = JSON.parse(productData);
        addProductToCart(product);
      }
    });
  });

  if (checkoutButton) {
    checkoutButton.addEventListener('click', () => {
      window.location.href = '?page=checkout';
    });
  }

  if (payNowButton) {
    payNowButton.addEventListener('click', () => {
      if (checkoutMessage) {
        checkoutMessage.style.display = 'block';
        checkoutMessage.textContent = 'Pembayaran berhasil disimulasikan. Terima kasih atas pesanan Anda!';
        checkoutMessage.style.color = '#1f5f32';
      }
      localStorage.removeItem('matahari_cart');
      renderCart();
      renderCheckout();
    });
  }

  renderCart();
  renderCheckout();

  const dropArea = document.getElementById('upload-drop-area');
  const fileInput = document.getElementById('asset-upload');
  const browseButton = document.getElementById('browse-files');
  const scanBtn = document.getElementById('scan-unsorted');
  const assetMessage = document.getElementById('asset-sort-results');
  const assetForm = document.getElementById('asset-upload-form');

  if (browseButton && fileInput) {
    browseButton.addEventListener('click', () => fileInput.click());
  }

  if (dropArea && fileInput) {
    ['dragenter', 'dragover'].forEach((eventName) => {
      dropArea.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        dropArea.classList.add('highlight');
      });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
      dropArea.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        dropArea.classList.remove('highlight');
      });
    });

    dropArea.addEventListener('drop', (event) => {
      const files = Array.from(event.dataTransfer.files || []);
      if (!files.length) {
        return;
      }

      const dataTransfer = new DataTransfer();
      files.forEach((file) => dataTransfer.items.add(file));
      fileInput.files = dataTransfer.files;
      dropArea.querySelector('p').textContent = `${files.length} file siap diunggah.`;
    });
  }

  if (assetForm) {
    assetForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(assetForm);
      assetMessage.textContent = 'Mengunggah file...';
      assetMessage.style.display = 'block';
      assetMessage.style.color = '#1f1b18';

      fetch('asset_sorter.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((result) => {
          renderAssetResults(result.items || []);
        })
        .catch(() => {
          assetMessage.textContent = 'Unggah file gagal. Coba lagi nanti.';
          assetMessage.style.color = '#7d2222';
        });
    });
  }

  if (scanBtn) {
    scanBtn.addEventListener('click', function () {
      scanBtn.disabled = true;
      scanBtn.textContent = 'Scanning...';
      assetMessage.textContent = 'Membaca folder unsorted...';
      assetMessage.style.display = 'block';
      assetMessage.style.color = '#1f1b18';

      fetch('asset_sorter.php')
        .then((response) => response.json())
        .then((result) => {
          renderAssetResults(result.items || []);
        })
        .catch(() => {
          assetMessage.textContent = 'Scan gagal. Coba lagi nanti.';
          assetMessage.style.color = '#7d2222';
        })
        .finally(() => {
          scanBtn.disabled = false;
          scanBtn.textContent = 'Scan Unsorted';
        });
    });
  }

  function renderAssetResults(items) {
    if (!assetMessage) {
      return;
    }

    if (!items.length) {
      assetMessage.textContent = 'Tidak ada file untuk diproses.';
      assetMessage.style.color = '#6e645b';
      return;
    }

    const list = document.createElement('ul');
    items.forEach((item) => {
      const li = document.createElement('li');
      li.textContent = `${item.name} → ${item.status}${item.target ? ` ke ${item.target}` : ''}`;
      list.appendChild(li);
    });

    assetMessage.innerHTML = '<h3>Hasil Sortir</h3>';
    assetMessage.appendChild(list);
    assetMessage.style.color = '#1f1b18';
  }
});
