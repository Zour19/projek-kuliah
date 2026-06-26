# 📦 Admin Panel - Dokumentasi

## Cara Mengakses Admin Panel

1. **Buka halaman login admin:**
   - URL: `http://localhost:8000/pages/admin-login.php`
   
2. **Login dengan password:**
   - Password default: `admin123` (ubah di production!)

3. **Kelola Produk:**
   - Tambah produk baru
   - Edit produk yang sudah ada
   - Hapus produk
   - Upload gambar produk

## Fitur Admin Panel

### ✅ Fitur yang Tersedia

1. **Lihat Daftar Produk**
   - Tampilkan semua produk dalam bentuk tabel
   - Lihat nama, kategori, harga, dan stok

2. **Tambah Produk Baru**
   - Klik tombol "Tambah Produk Baru"
   - Isi form dengan detail produk:
     - Nama produk (wajib)
     - Kategori (wajib)
     - Deskripsi
     - Harga (wajib)
     - Stok
     - Gambar (wajib)
     - Jadikan produk unggulan (opsional)

3. **Upload Gambar**
   - Upload dengan klik area upload
   - Atau drag & drop gambar
   - Format yang didukung: JPEG, PNG, GIF, WebP
   - Ukuran maksimal: 5MB

4. **Edit Produk**
   - Klik tombol "Edit" pada produk
   - Ubah data produk
   - Simpan perubahan

5. **Hapus Produk**
   - Klik tombol "Hapus" pada produk
   - Konfirmasi penghapusan

## API Endpoints

### GET Requests
- `admin-api.php?action=get_products` - Ambil semua produk
- `admin-api.php?action=get_product&id=1` - Ambil satu produk

### POST Requests
- `admin-api.php?action=create_product` - Buat produk baru
- `admin-api.php?action=update_product` - Update produk
- `admin-api.php?action=delete_product` - Hapus produk
- `admin-api.php?action=upload_image` - Upload gambar

## Struktur Data Produk

```json
{
  "id": 1,
  "category_id": 1,
  "name": "Romantic Rose Bouquet",
  "slug": "romantic-rose-bouquet",
  "description": "A romantic rose bouquet with soft pastel petals.",
  "price": 250000,
  "image": "assets/images/products/product-xxx.jpeg",
  "stock": 50,
  "is_featured": true
}
```

## Kategori Produk

1. **Bouquets** (1)
2. **Standing Flowers** (2)
3. **Bloom Box** (3)
4. **Flowers** (4)
5. **Accessories** (5)

## File Penyimpanan

Semua produk disimpan dalam file JSON:
- File: `data/products.json`
- Format: JSON Array

Gambar produk disimpan di:
- Folder: `assets/images/products/`
- Format: JPG, PNG, GIF, WebP
- Nama file: `product-{timestamp}-{random}.ext`

## Security Notes

⚠️ **PENTING UNTUK PRODUCTION:**

1. Ubah password admin di `pages/admin-login.php`
   - Gunakan password yang kuat
   - Simpan di environment variable atau database

2. Tambahkan autentikasi yang lebih kuat
   - Gunakan session yang aman
   - Implementasi CSRF protection
   - Validasi input yang ketat

3. Backup data regular
   - Backup file `data/products.json`
   - Backup folder `assets/images/products/`

4. Limit upload file
   - Batasi ukuran file
   - Validasi tipe file
   - Scan virus sebelum simpan

## Troubleshooting

### Gambar tidak terupload
- Pastikan folder `assets/images/products/` ada
- Cek permission folder (harus writable)
- Pastikan ukuran file < 5MB

### Produk tidak muncul
- Cek file `data/products.json` ada
- Cek format JSON valid
- Reload halaman browser

### Session habis
- Login kembali di `pages/admin-login.php`
- Hapus cookies browser jika perlu

## Tips

✨ **Tips untuk penggunaan optimal:**

1. Buat slug unik untuk setiap produk
2. Upload gambar berkualitas tinggi
3. Isi deskripsi produk yang detail
4. Tandai produk unggulan untuk homepage
5. Update stok secara teratur
6. Backup data secara berkala

---

Dibuat untuk **Matahari Florist** - Toko Bunga Online
