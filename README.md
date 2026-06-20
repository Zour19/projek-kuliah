# Matahari Florist (local dev)

Ringkasan singkat proyek toko bunga berbasis PHP.

## Tujuan
- Menyimpan aset dan database lokal pada laptop (tidak dipush ke GitHub).
- Menyediakan langkah mudah untuk menjalankan aplikasi secara lokal.

## Hal yang Diabaikan dari Repo
Jangan commit file sensitif atau data besar. `.gitignore` sudah mengabaikan:

- `.env` (konfigurasi lokal dan kredensial)
- `data/` (database JSON / sqlite dan data sementara)
- `*.db`, `*.sqlite` (file DB SQLite)

Pastikan file konfigurasi lokal tetap tersimpan di laptop.

## Menjalankan aplikasi (lokal)

Prasyarat: PHP 8+ terinstal.

1. Jalankan server development PHP di folder proyek:

```bash
php -S localhost:8000
```

2. Buka browser: `http://localhost:8000`

3. Admin:

- Username: `admin`
- Password: `admin123`

## Jika ingin mengaktifkan SQLite (opsional)
Jika ingin menggunakan SQLite alih-alih JSON fallback, aktifkan `sqlite3` extension pada sistem.

Contoh (Arch/Manjaro):

```bash
sudo pacman -S php-sqlite
```

Contoh (Alpine):

```bash
sudo apk add php8-sqlite3
```

## Menyiapkan Git + Push ke GitHub (tanpa file lokal)

1. Inisialisasi git (jika belum):

```bash
git init
git add .
git commit -m "Initial commit - project skeleton, ignore local data"
```

2. Buat repository di GitHub (via web) lalu tambahkan remote dan push:

```bash
git remote add origin git@github.com:USERNAME/REPO.git
git branch -M main
git push -u origin main
```

Catatan: jangan commit file di `data/` ataupun `.env`.

## Continuous Integration
Ada workflow ringan untuk memastikan file PHP tidak memiliki syntax error sebelum merge.

---
Jika mau, saya bisa membuat repository GitHub untukmu (butuh akses token) atau bantu langkah perintah push.
