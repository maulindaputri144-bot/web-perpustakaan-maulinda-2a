
# 📚 MINI DIGITAL LIBRARY

**Aplikasi Perpustakaan Digital Berbasis Web**

## 1. Deskripsi Project

Mini Digital Library merupakan aplikasi sistem informasi perpustakaan digital berbasis web yang dikembangkan menggunakan PHP Native dan MySQL. Aplikasi ini dibuat untuk membantu proses pengelolaan data buku, pengguna, peminjaman, dan pengembalian buku secara digital.

Aplikasi ini memiliki dua jenis pengguna, yaitu Admin dan Anggota. Setiap pengguna memiliki hak akses yang berbeda sesuai dengan perannya.

## 2. Tujuan Project

Tujuan pengembangan Mini Digital Library adalah:

- Mempermudah pengelolaan data buku perpustakaan.

- Memudahkan pengguna dalam mencari dan melihat informasi buku.

- Memfasilitasi proses pengajuan peminjaman dan pengembalian buku.

- Menerapkan sistem autentikasi dan pembagian hak akses pengguna.

- Menerapkan pengelolaan database menggunakan MySQL.

- Mengembangkan sistem perpustakaan digital yang mudah digunakan.

## 3. Fitur Aplikasi

### A. Fitur Autentikasi dan Pengguna

1. **Login:** Digunakan oleh Admin dan Anggota untuk masuk ke aplikasi.

2. **Registrasi:** Memungkinkan pengguna membuat akun Anggota baru.

3. **Logout:** Mengakhiri sesi pengguna.

4. **Remember Me:** Membantu pengguna tetap masuk tanpa harus login kembali selama token masih berlaku.

5. **Session:** Mengelola status login pengguna.

6. **Hak Akses Pengguna:** Memisahkan akses Admin dan Anggota sesuai perannya.

### B. Fitur Dashboard

1. **Dashboard Admin:** Menampilkan ringkasan informasi dan statistik perpustakaan.

2. **Dashboard Anggota:** Menyediakan akses ke layanan perpustakaan bagi Anggota.

3. **Statistik Buku:** Menampilkan total buku, total stok, buku tersedia, dan buku dengan stok kosong.

### C. Fitur Pengelolaan Buku

1. **Tambah Buku:** Menambahkan data buku baru.

2. **Tampil Buku:** Menampilkan daftar buku yang tersimpan di database.

3. **Edit Buku:** Mengubah informasi buku.

4. **Hapus Buku:** Menghapus data buku.

5. **Upload Sampul Buku:** Mengunggah gambar sampul dengan format JPG, JPEG, dan PNG, maksimal 2 MB.

6. **Detail Buku:** Menampilkan informasi lengkap buku.

7. **Pencarian Buku:** Memudahkan pengguna menemukan buku berdasarkan kata kunci.

8. **Filter Buku:** Menyaring buku berdasarkan kategori dan status ketersediaan.

9. **Pagination:** Membagi daftar buku ke beberapa halaman agar lebih mudah ditampilkan.

### D. Fitur Peminjaman dan Pengembalian

1. **Pengajuan Peminjaman:** Anggota dapat mengajukan peminjaman buku.

2. **Persetujuan Peminjaman:** Admin dapat menyetujui pengajuan Anggota.

3. **Penolakan Peminjaman:** Admin dapat menolak pengajuan peminjaman.

4. **Pengurangan Stok Otomatis:** Stok berkurang satu ketika peminjaman disetujui.

5. **Masa Peminjaman:** Peminjaman berlangsung selama tujuh hari sejak disetujui.

6. **Pengembalian Buku:** Admin dapat mengonfirmasi pengembalian buku.

7. **Penambahan Stok Otomatis:** Stok bertambah kembali setelah pengembalian dikonfirmasi.

8. **Riwayat Peminjaman:** Anggota dapat melihat riwayat pengajuan, peminjaman, penolakan, dan pengembalian buku.

### E. Fitur Pendukung dan Keamanan

- Validasi formulir.

- Perlindungan CSRF pada proses tertentu.

- Prepared statements untuk query database.

- Pengelolaan session pengguna.

- Validasi tipe dan ukuran file gambar.

- Penggunaan nama file gambar secara acak.

- Transaksi database pada proses peminjaman dan pengembalian.

- Pencegahan pengajuan peminjaman aktif atau menunggu yang berulang untuk buku yang sama.

- Notifikasi aktivitas pengguna.

- Status ketersediaan buku dihitung berdasarkan jumlah stok.

## 4. Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| PHP Native | Pengolahan logika aplikasi |
| MySQL | Penyimpanan database |
| HTML | Struktur halaman web |
| CSS | Pengaturan tampilan aplikasi |
| JavaScript | Interaksi pada halaman web |
| XAMPP | Server lokal |
| Visual Studio Code | Editor kode program |
| Git dan GitHub | Pengelolaan versi dan penyimpanan repository |

## 5. Hak Akses Pengguna

| Fitur | Admin | Anggota |
|---|:---:|:---:|
| Login dan Logout | ✓ | ✓ |
| Registrasi | - | ✓ |
| Dashboard | ✓ | ✓ |
| Melihat Daftar Buku | ✓ | ✓ |
| Pencarian dan Filter Buku | ✓ | ✓ |
| Melihat Detail Buku | ✓ | ✓ |
| Tambah Buku | ✓ | - |
| Edit Buku | ✓ | - |
| Hapus Buku | ✓ | - |
| Upload Sampul Buku | ✓ | - |
| Mengajukan Peminjaman | - | ✓ |
| Menyetujui Peminjaman | ✓ | - |
| Menolak Peminjaman | ✓ | - |
| Mengonfirmasi Pengembalian | ✓ | - |
| Melihat Riwayat Peminjaman | - | ✓ |

## 6. Alur Sistem Peminjaman dan Pengembalian

Proses peminjaman dan pengembalian buku dilakukan melalui tahapan berikut:

1. Anggota login ke aplikasi.

2. Anggota memilih buku yang ingin dipinjam.

3. Anggota mengajukan peminjaman buku.

4. Pengajuan masuk ke daftar peminjaman Admin dengan status Menunggu.

5. Admin memeriksa pengajuan dan memberikan persetujuan atau penolakan.

6. Jika disetujui, status berubah menjadi Dipinjam dan stok buku berkurang satu.

7. Masa peminjaman berlangsung selama tujuh hari sejak tanggal persetujuan.

8. Setelah buku dikembalikan, Admin mengonfirmasi pengembalian.

9. Status berubah menjadi Dikembalikan dan stok buku bertambah satu.

10. Anggota dapat melihat seluruh riwayat peminjamannya.

Jika pengajuan ditolak, stok buku tidak mengalami perubahan.

## 7. Database

Nama database: `db_perpustakaan`

Tabel yang digunakan:

| Nama Tabel | Keterangan |

|---|---|

| `users` | Menyimpan data Admin dan Anggota |

| `buku` | Menyimpan informasi data buku |

| `peminjaman` | Menyimpan data pengajuan, peminjaman, dan pengembalian |

| `remember_tokens` | Menyimpan token autentikasi Remember Me |

Status ketersediaan buku ditentukan secara otomatis berdasarkan jumlah stok:

- **Tersedia:** Jika stok buku lebih dari nol.

- **Dipinjam:** Jika stok buku sama dengan nol.

## 8. Struktur Folder Project

```text

project_perpustakaan/

│

├── config/

│   ├── koneksi.php

│   └── auth.php

│

├── assets/

│   └── css/

│

├── uploads/

│

├── screenshots/

│

├── .htaccess

│

├── index.php

├── launching.php

├── dashboard.php

├── daftar_buku.php

├── detail_buku.php

├── tambah_buku.php

├── edit_buku.php

├── hapus_buku.php

├── proses_tambah.php

├── proses_edit.php

├── proses_login.php

├── logout.php

├── registrasi.php

├── proses_registrasi.php

├── proses_pengajuan.php

├── peminjaman_admin.php

├── riwayat_peminjaman.php

│

└── db_perpustakaan1

```

*Catatan: Struktur di atas merupakan gambaran susunan utama project. Sesuaikan dengan nama file dan folder yang benar-benar ada pada project.*

## 9. Cara Menjalankan Aplikasi

Berikut langkah-langkah untuk menjalankan aplikasi Mini Digital Library secara lokal:

1. Instal XAMPP pada komputer.

2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.

3. Salin folder project ke dalam direktori `htdocs`.

4. Buka phpMyAdmin melalui browser.

5. Buat database dengan nama `db_perpustakaan`.

6. Import file `db_perpustakaan.sql` ke dalam database.

7. Sesuaikan konfigurasi database pada file `config/koneksi.php`.

8. Buka browser dan masukkan alamat berikut:

```text

http://localhost/web-perpustakaan-maulinda-2a/project_perpustakaan/

```

9. Halaman launching akan ditampilkan sebelum pengguna diarahkan ke halaman login.

## 10. Dokumentasi Aplikasi

Dokumentasi berikut menampilkan tampilan antarmuka dan bukti penggunaan fitur-fitur pada aplikasi Mini Digital Library.

Screenshot disimpan dalam folder `screenshots`.

### A. Halaman Utama dan Autentikasi

**1. Halaman Launching**

Menampilkan halaman pembuka aplikasi sebelum pengguna diarahkan ke halaman login.

![Halaman Launching](screenshots/launching.png)

**2. Halaman Login**

Menampilkan formulir login yang digunakan oleh Admin dan Anggota.

![Halaman Login](screenshots/login.png)

**3. Halaman Registrasi**

Menampilkan formulir pendaftaran akun Anggota baru.

![Halaman Registrasi](screenshots/registrasi.png)

**4. Fitur Remember Me**

Menampilkan fitur untuk membantu pengguna mempertahankan status login.

![Remember Me](screenshots/remember-me.png)

### Logout

Menampilkan proses pengguna mengakhiri sesi login.

![Logout](screenshots/logout.png)

### B. Dashboard

**5. Dashboard Admin**

Menampilkan statistik perpustakaan dan menu pengelolaan aplikasi.

![Dashboard Admin](screenshots/dashboard-admin.png)

### Statistik Perpustakaan

Menampilkan ringkasan statistik perpustakaan pada dashboard Admin.

![Statistik-Perpustakaan](screenshots/statistik-perpustakaan.png)

**6. Dashboard Anggota**

Menampilkan halaman utama Anggota untuk mengakses layanan perpustakaan.

![Dashboard Anggota](screenshots/dashboard-anggota.png)

### C. Pengelolaan Buku

**7. Halaman Daftar Buku**

Menampilkan seluruh data buku yang tersimpan di dalam sistem.

![Daftar Buku](screenshots/daftar-buku.png)

### Data Buku

Menampilkan data buku yang dikelola dalam aplikasi.

![Data-Buku](screenshots/data-buku.png)

**8. Fitur Pencarian dan Filter Buku**

Menampilkan proses pencarian dan penyaringan buku berdasarkan kategori maupun status ketersediaan.

![Pencarian dan Filter](screenshots/pencarian-filter.png)

**9. Fitur Pagination**

Menampilkan navigasi halaman pada daftar buku.

![Pagination](screenshots/pagination.png)

**10. Form Tambah Buku**

Menampilkan formulir untuk menambahkan data buku baru.

![Tambah Buku](screenshots/tambah-buku.png)

**11. Fitur Upload Sampul Buku**

Menampilkan proses unggah gambar sampul buku.

![Upload Sampul](screenshots/upload-sampul.png)

**12. Form Edit Buku**

Menampilkan proses perubahan informasi buku.

![Edit Buku](screenshots/edit-buku.png)

**13. Fitur Hapus Buku**

Menampilkan proses penghapusan data buku.

![Hapus Buku](screenshots/hapus-buku.png)

**14. Halaman Detail Buku**

Menampilkan informasi lengkap mengenai buku yang dipilih.

![Detail Buku](screenshots/detail-buku.png)

### D. Peminjaman dan Pengembalian

**15. Fitur Pengajuan Peminjaman**

Menampilkan proses Anggota mengajukan peminjaman buku.

![Pengajuan Peminjaman](screenshots/pengajuan-peminjaman.png)

**16. Halaman Data Peminjaman Admin**

Menampilkan daftar pengajuan peminjaman yang dikelola Admin.

![Data Peminjaman](screenshots/datail-peminjaman.png)

**17. Fitur Persetujuan dan Penolakan**

Menampilkan proses Admin menyetujui atau menolak pengajuan peminjaman.

![Persetujuan dan Penolakan](screenshots/persetujuan-penolakan.png)

### Persetujuan Peminjaman

Menampilkan proses Admin menyetujui pengajuan peminjaman.

![Persetujuan-Peminjaman](screenshots/persetujuan-peminjaman.png)

### Penolakan Peminjaman

Menampilkan proses Admin menolak pengajuan peminjaman.

![Penolakan-Peminjaman](screenshots/penolakan-peminjaman.png)

**18. Fitur Pengembalian Buku**

Menampilkan proses Admin mengonfirmasi pengembalian buku.

![Pengembalian Buku](screenshots/pengembalian.png)

### Konfirmasi Pengembalian

Menampilkan proses Admin mengonfirmasi pengembalian buku.

![Konfirmasi-Pengembalian](screenshots/konfimasi-pengembalian.png)

**19. Riwayat Peminjaman Anggota**

Menampilkan riwayat pengajuan, peminjaman, penolakan, dan pengembalian buku.

![Riwayat Peminjaman](screenshots/riwayat-peminjaman-anggota.png)

### F. Database

**22. Database Perpustakaan**

Menampilkan struktur tabel database melalui phpMyAdmin.

![Database Perpustakaan](screenshots/struktur-database.png)

## 11. Pengembang

**Nama Pengembang:** Maulinda Putri Tufah

**Mata Kuliah:** [Pemrograman Web]

**Program Studi:** [Sistem Informasi]

**Tahun:** 2026

## 12. Link

**Akses Project:** http://localhost/web-perpustakaan-maulinda-2a/project_perpustakaan/

**GitHub:** https://github.com/maulindaputri144-bot/web-perpustakaan-maulinda-2a

**YouTube (demo project):** https://youtu.be/I8Oik_01WBE?si=UxZnbt4uIk8XRTr_

