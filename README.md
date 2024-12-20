# Toko Monel 💍

![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![MongoDB](https://img.shields.io/badge/MongoDB-Latest-green)
![License](https://img.shields.io/badge/License-MIT-yellow)

Aplikasi web modern untuk penjualan perhiasan monel dengan fitur manajemen produk, keranjang belanja, dan sistem pemesanan yang terintegrasi.

## ✨ Fitur Utama

- 🛍️ Katalog produk dengan pencarian dan filter
- 🛒 Keranjang belanja real-time
- 💳 Sistem pembayaran terintegrasi
- 📱 Responsive design untuk mobile dan desktop
- 🔐 Autentikasi menggunakan email
- 📊 Dashboard admin dengan analitik
- 🎨 UI/UX modern dan intuitif
- 📦 Manajemen stok produk
- 🔍 Pencarian produk
- 📸 Upload gambar produk
- 📱 Tampilan responsif
- 💰 Konfirmasi pembayaran
- 📊 Laporan penjualan

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MongoDB 4.4+
- MongoDB PHP Driver
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Safari, Edge)
- Composer untuk manajemen dependensi

## 🚀 Cara Instalasi

### Menggunakan Composer

```bash
# Clone repository
git clone https://github.com/NoHeart6/toko-monel.git

# Masuk ke direktori project
cd toko_monel

# Install dependensi
composer install

# Setup environment
cp .env.example .env
```

### Setup Manual

1. Clone atau download repository ini
2. Pastikan MongoDB sudah terinstall dan berjalan
3. Install MongoDB PHP Driver sesuai versi PHP
4. Letakkan folder project di direktori web server
5. Buat database MongoDB baru dengan nama `toko_monel`

## ⚙️ Konfigurasi

### Database

1. Buka file `config/database.php`
2. Sesuaikan konfigurasi MongoDB:
```php
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$database = $mongoClient->toko_monel;
```

### Environment Variables

Salin `.env.example` ke `.env` dan sesuaikan:
```env
APP_NAME=TokoMonel
APP_ENV=local
MONGO_URI=mongodb://localhost:27017
DB_NAME=toko_monel
```

## 🏃‍♂️ Menjalankan Aplikasi

```bash
# Development server
php -S localhost:3000

# Setup database dan data awal
php setup.php

# Akses aplikasi
http://localhost:3000
```

## 👥 Akun Default

### Admin
- Email: admin@tokomonel.com
- Password: admin123
- Fitur:
  - Manajemen produk
  - Manajemen stok
  - Konfirmasi pembayaran
  - Lihat laporan penjualan
  - Dashboard analytics

### Demo User
- Email: demo@tokomonel.com
- Password: demo123
- Fitur:
  - Lihat katalog produk
  - Tambah ke keranjang
  - Checkout dan pembayaran
  - Riwayat pesanan

## 📁 Struktur Project

```
toko_monel/
├── admin/              # Panel administrasi
├── api/               # REST API endpoints
├── assets/            # Asset statis (CSS, JS, images)
├── config/            # Konfigurasi aplikasi
├── database/          # Migration & seeder
├── docs/              # Dokumentasi
├── includes/          # Helper & utilities
├── src/               # Source code utama
├── tests/             # Unit & integration tests
├── uploads/           # File upload
├── vendor/            # Dependencies
├── .env.example       # Contoh environment
├── composer.json      # Dependency manifest
└── index.php          # Entry point
```

## 🔧 Development

### Menambah Data Dummy

```php
// includes/product.php
public function seedProducts() {
    $products = [
        [
            'name' => 'Cincin Monel Premium',
            'price' => 150000,
            // ...
        ]
    ];
    // ...
}
```

### Testing

```bash
# Menjalankan unit test
./vendor/bin/phpunit

# Test specific feature
./vendor/bin/phpunit --filter ProductTest
```

## 🐛 Troubleshooting

### Masalah Umum

1. **Gambar Tidak Muncul**
   - Periksa permission folder `uploads`: `chmod 755 uploads`
   - Validasi path gambar di database
   - Pastikan extension `fileinfo` PHP aktif

2. **Error Koneksi MongoDB**
   - Verifikasi service MongoDB: `systemctl status mongodb`
   - Cek log MongoDB: `tail -f /var/log/mongodb/mongod.log`
   - Validasi kredensial di `.env`

3. **Error Login**
   - Pastikan email dan password sesuai
   - Cek koneksi database
   - Periksa tabel users di MongoDB

4. **Error Upload Gambar**
   - Periksa permission folder uploads
   - Pastikan ukuran file tidak melebihi batas
   - Validasi tipe file yang diizinkan

## 🤝 Kontribusi

Kami sangat menghargai kontribusi! Silakan ikuti langkah berikut:

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Menambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## 📝 Changelog

### [1.0.0] - 2024-01-20
- Rilis versi pertama
- Fitur dasar e-commerce
- Integrasi pembayaran

### [1.1.0] - 2024-02-01
- Tambah dashboard analytics
- Optimasi performa
- Perbaikan bug minor

## 📜 Lisensi

Project ini dilisensikan di bawah MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## 📞 Dukungan

- Email: support@tokomonel.com
- Website: https://tokomonel.com
- Discord: [Join Server](https://discord.gg/tokomonel)

---
Dibuat dengan ❤️ oleh Tim Toko Monel