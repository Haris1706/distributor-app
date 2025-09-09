# Distributor App

Aplikasi manajemen distributor berbasis PHP dan MySQL. Project ini mencakup fitur manajemen barang, kategori, transaksi, dan autentikasi pengguna menggunakan Google OAuth.

**Catatan:** Project ini disusun untuk memenuhi **Tugas Ujian Akhir Semester (UAS)** pada mata kuliah terkait pengembangan aplikasi web.


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
