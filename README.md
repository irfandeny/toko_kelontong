# Sistem Informasi Toko Kelontong

Aplikasi web berbasis Laravel untuk mengelola operasional toko kelontong, mencakup manajemen inventori, supplier, transaksi pembelian dan penjualan.

## Deskripsi

Sistem Informasi Toko Kelontong adalah aplikasi CRUD (Create, Read, Update, Delete) yang dirancang untuk memudahkan pengelolaan toko kelontong. Aplikasi ini dibangun menggunakan framework Laravel dengan Blade templating engine.

## Fitur Utama

### 1. Manajemen Data Barang
- Tambah barang baru dengan informasi lengkap (nama, kategori, harga beli/jual, stok, satuan)
- Tampilkan daftar semua barang dalam format tabel
- Edit data barang (perubahan harga, penambahan stok)
- Hapus barang yang sudah tidak dijual
- Upload foto barang untuk visualisasi produk

### 2. Manajemen Supplier
- Tambah data supplier baru
- Tampilkan list supplier
- Edit informasi supplier
- Hapus data supplier

### 3. Kategori Barang
- Tambah kategori produk (mie instan, minuman, rokok, sembako, dll.)
- Tampilkan daftar kategori
- Edit nama kategori
- Hapus kategori

### 4. Transaksi Pembelian Stok
- Catat pembelian barang dari supplier
- Detail transaksi: barang, jumlah, supplier
- Riwayat pembelian stok
- Edit dan hapus transaksi pembelian

### 5. Sistem Kasir Penjualan
- Input transaksi penjualan
- Riwayat transaksi penjualan
- Update transaksi
- Hapus transaksi

### 6. Fitur Tambahan
- Upload foto barang
- Validasi harga (harus angka)
- Validasi stok (tidak boleh minus)
- Filter berdasarkan kategori
- Pencarian barang

## Teknologi

- **Framework**: Laravel
- **Frontend**: Blade Templates
- **Backend**: PHP 52.5%
- **Database**: MySQL/PostgreSQL (sesuaikan dengan konfigurasi)
- **JavaScript**: 0.1%

## Instalasi

### Persyaratan Sistem
- PHP >= 8.0
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (untuk asset compilation)

### Langkah Instalasi

1. **Clone repositori**
```
git clone https://github.com/irfandeny/toko_kelontong.git
cd toko_kelontong
```

2. **Install dependencies**
```
composer install
npm install
```

3. **Konfigurasi environment**
```
cp .env.example .env
php artisan key:generate
```

4. **Konfigurasi database**
Edit file `.env` dan sesuaikan dengan database Anda:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=toko_kelontong
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Migrasi database**
```
php artisan migrate
```

6. **Jalankan aplikasi**
```
php artisan serve
```

Aplikasi dapat diakses di `http://localhost:8000`

## Kontributor

Proyek ini dikembangkan oleh tim kelompok untuk tugas Pemrograman Web:

- [Irfan Deny - 202310370311377](https://github.com/irfandeny)
- [Keysya Yesanti Safa'at - 202310370311363](https://github.com/keysyayst)
- [Amalia Dinda Aprilliana - 202310370311360](https://github.com/aamaliadiin)
- [Firman Maulana Bagaskara - 202310370311355](https://github.com/Firman2244)
- [Erlangga Rizky Ramadhani  - 202310370311357](https://github.com/YourMajesty186)

## Lisensi

Proyek ini dibuat untuk keperluan akademik (Tugas Pemrograman Web).

## Kontribusi

Kontribusi, issues, dan feature requests sangat diterima! Silakan cek halaman [issues](https://github.com/irfandeny/toko_kelontong/issues).

## Kontak

Untuk pertanyaan atau saran, silakan hubungi melalui GitHub issues atau pull request.
