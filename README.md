# Distributor App

Aplikasi manajemen distributor berbasis PHP dan MySQL. Project ini mencakup fitur manajemen barang, kategori, transaksi, dan autentikasi pengguna menggunakan Google OAuth.

**Catatan:** Project ini disusun untuk memenuhi **Tugas Ujian Akhir Semester (UAS)** pada mata kuliah terkait pengembangan aplikasi basis data.


---

## Struktur Database

File database tersedia dalam format SQL dan dapat langsung di-*import* melalui phpMyAdmin.

- **Nama file:** `Distributor.sql`
- **Lokasi:** `database/Distributor.sql`
- **Tabel yang tersedia:**
  - `barang`
  - `kategori`
  - `transaksi`
  - `users`
- **Relasi:**
  - `barang.kategori_id` → `kategori.id`
  - `transaksi.id_barang` → `barang.id`

### Cara Import Database

1. Buka **phpMyAdmin**
2. Buat database baru dengan nama: `Distributor`
3. Klik tab **Import**
4. Pilih file `database/Distributor.sql`
5. Klik **Go**

---

## Instalasi Dependency dengan Composer

Project ini menggunakan library Google API Client dan dependensi lain yang dikelola melalui Composer. Folder `vendor/` tidak disertakan dalam repository untuk menghindari ukuran besar, namun file `composer.json` dan `composer.lock` sudah tersedia.

### Cara Install Composer

Jika Composer belum terinstal, silakan unduh dari [getcomposer.org](https://getcomposer.org/download/).

### Cara Generate Folder `vendor/`

Setelah Composer terinstal, buka terminal di folder project dan jalankan:

```bash
composer install

Perintah ini akan otomatis membuat folder `vendor/` dan file `autoload.php` yang dibutuhkan untuk menjalankan project.

## Struktur Folder Utama
project-root/
├── public/
│   ├── ajax/
│   │   ├── qajax-barang.php
│   │   ├── qajax-tambah-barang.php
│   │   └── qajax-ubah-barang.php
│   ├── assets/
│   │   └── img/
│   │       └── logo.png
│   ├── barang/
│   │   ├── hapus.php
│   │   ├── index.php
│   │   ├── kategori-barang.php
│   │   ├── tambah.php
│   │   └── ubah.php
│   ├── img/
│   │   └── (gambar produk)
│   ├── vendor/
│   │   └── google/apiclient/src/AuthHandler/
│   ├── callback.php
│   ├── google-login.php
│   ├── index.php
│   ├── logout.php
│   └── register.php
├── src/
│   ├── config.php
│   └── functions.php
├── database/
│   └── Distributor.sql
├── composer.json
├── composer.lock
└── README.md

## Autentikasi Google OAuth

Project ini menggunakan Google OAuth untuk login pengguna. Pastikan file konfigurasi OAuth seperti `client_secret.json` disimpan secara lokal dan tidak diupload ke GitHub.

## Informasi Pengguna Default

Data pengguna default tersedia di tabel `users`:

- Email: haris20240100038@sibermu.ac.id
- Password: sudah di-hash menggunakan bcrypt
- Picture: URL Google profile picture

## Catatan Tambahan

- Pastikan server lokal (XAMPP, Laragon, dsb.) aktif dan database `Distributor` sudah diimpor.
- Jika terjadi error saat import, pastikan file `.sql` tidak rusak dan phpMyAdmin versi 5.2.1 atau lebih baru digunakan.
- Untuk login, pastikan konfigurasi Google OAuth sudah disiapkan secara lokal.

## Kontributor

- Haris Sanjaya – Pengembang utama
