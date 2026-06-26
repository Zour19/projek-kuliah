# Matahari Florist

Sebuah toko florist sederhana berbasis PHP dengan tampilan web profesional dan dukungan backend SQLite / fallback JSON.

## Fitur

- Halaman katalog produk kategori: Bouquets, Bloom Boxes, Flowers, Standing Flowers, Accessories.
- Pendaftaran akun pelanggan via API JSON.
- Halaman admin untuk login, menambah produk, melihat produk, dan melakukan sort asset.
- Asset sorter otomatis memindahkan gambar ke folder kategori berdasarkan nama file.
- SQLite sebagai storage utama dengan fallback JSON sebagai cadangan.

## Persyaratan

- PHP 8.0 atau lebih baru
- Ekstensi `sqlite3` diaktifkan
- Server lokal PHP built-in atau web server lain

## Instalasi lokal

1. Salin `.env.example` menjadi `.env`.
2. Jalankan server lokal dari direktori proyek:

```powershell
php -S localhost:8000
```

3. Buka di browser:

```text
http://localhost:8000
```



## Struktur penting

- `index.php` — entry point utama tampilan web
- `api.php` — API untuk membuat akun pelanggan
- `asset_sorter.php` — endpoint untuk scan dan upload asset
- `db.php` — adaptor database dengan SQLite dan fallback JSON
- `includes/helpers.php` — helper penyimpanan dan asset
- `assets/css/style.css` — gaya CSS eksternal
- `assets/js/main.js` — skrip frontend

## Catatan

- `.env` tidak disertakan ke repositori karena disimpan di `.gitignore`.
- Jika SQLite tidak terpasang, aplikasi akan tetap berjalan dengan fallback JSON di folder `data/`.

