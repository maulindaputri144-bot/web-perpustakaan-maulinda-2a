
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa hak akses Admin
mulaiSession();
wajibAdmin($koneksi);

// Membuat token CSRF
$tokenCSRF = buatTokenCSRF();

// URL dasar project
$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan/";

// Informasi pengguna
$namaPengguna = htmlspecialchars(
    $_SESSION["nama"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);

$peranPengguna = htmlspecialchars(
    $_SESSION["role"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Buku | Mini Digital Library</title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>assets/css/dashboard.css?v=1"
    >

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>assets/css/tambah_buku.css?v=1"
    >
</head>

<body>

<div class="dashboard-wrapper">

    <!-- Header -->
    <header class="dashboard-header">

        <a href="<?= $baseUrl ?>dashboard.php" class="brand">
            <span class="brand-mark">M</span>

            <span class="brand-name">
                Mini Digital Library
                <small>Ruang untuk Menemukan Cerita</small>
            </span>
        </a>

        <div class="header-user">

            <div class="user-avatar">
                <?= strtoupper(substr($namaPengguna, 0, 1)) ?>
            </div>

            <div class="user-info">
                <strong><?= $namaPengguna ?></strong>
                <span><?= $peranPengguna ?></span>
            </div>

        </div>

    </header>

    <!-- Konten utama -->
    <main class="tambah-main">

        <!-- Judul halaman -->
        <section class="tambah-heading">

            <div>
                <span class="section-label">KOLEKSI PERPUSTAKAAN</span>

                <h1>Tambah Buku Baru</h1>

                <p>
                    Lengkapi informasi buku untuk menambahkan
                    koleksi baru ke Mini Digital Library.
                </p>
            </div>

            <div class="tambah-decoration">
                <span>📚</span>
                <span>✦</span>
            </div>

        </section>

        <!-- Navigasi -->
        <nav class="tambah-navigation">

            <a href="<?= $baseUrl ?>daftar_buku.php" class="nav-back">
                <span>←</span> Kembali ke Data Buku
            </a>

        </nav>

        <!-- Form tambah buku -->
        <section class="form-panel">

            <div class="form-panel-heading">

                <div>
                    <span class="section-label">FORMULIR BUKU</span>
                    <h2>Informasi Buku</h2>
                    <p>Isi semua kolom yang tersedia dengan benar.</p>
                </div>

                <div class="form-icon">📖</div>

            </div>

            <form
                action="<?= $baseUrl ?>proses_tambah.php"
                method="POST"
                enctype="multipart/form-data"
                class="form-tambah"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $tokenCSRF,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                >

                <div class="form-grid">

                    <!-- Judul buku -->
                    <div class="form-group full-width">

                        <label for="judul_buku">
                            Judul Buku <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="judul_buku"
                            name="judul_buku"
                            maxlength="200"
                            placeholder="Masukkan judul buku"
                            required
                        >

                    </div>

                    <!-- Kategori -->
                    <div class="form-group">

                        <label for="kategori">
                            Kategori <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="kategori"
                            name="kategori"
                            maxlength="100"
                            placeholder="Contoh: Pendidikan"
                            required
                        >

                    </div>

                    <!-- Penulis -->
                    <div class="form-group">

                        <label for="penulis">
                            Penulis <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="penulis"
                            name="penulis"
                            maxlength="100"
                            placeholder="Masukkan nama penulis"
                            required
                        >

                    </div>

                    <!-- Tahun terbit -->
                    <div class="form-group">

                        <label for="tahun_terbit">
                            Tahun Terbit <span>*</span>
                        </label>

                        <input
                            type="number"
                            id="tahun_terbit"
                            name="tahun_terbit"
                            min="1901"
                            max="2155"
                            placeholder="Contoh: 2024"
                            required
                        >

                    </div>

                    <!-- Stok -->
                    <div class="form-group">

                        <label for="stok">
                            Jumlah Stok <span>*</span>
                        </label>

                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            min="0"
                            step="1"
                            value="1"
                            required
                        >

                        <small class="petunjuk">
                            Status buku ditentukan otomatis berdasarkan jumlah stok.
                        </small>

                    </div>

                    <!-- Sampul -->
                    <div class="form-group full-width">

                        <label for="sampul">
                            Sampul Buku <span>*</span>
                        </label>

                        <input
                            type="file"
                            id="sampul"
                            name="sampul"
                            accept=".jpg,.jpeg,.png"
                            required
                        >

                        <small class="petunjuk">
                            Format JPG atau PNG dengan ukuran maksimal 2 MB.
                        </small>

                    </div>

                </div>

                <!-- Informasi -->
                <div class="form-note">
                    <span>ℹ</span>

                    <p>
                        Pastikan informasi buku sudah benar sebelum
                        menekan tombol Simpan Buku.
                    </p>
                </div>

                <!-- Tombol -->
                <div class="form-actions">

                    <a
                        href="<?= $baseUrl ?>daftar_buku.php"
                        class="button-cancel"
                    >
                        Batal
                    </a>

                    <button type="submit" class="button-save">
                        <span>＋</span> Simpan Buku
                    </button>

                </div>

            </form>

        </section>

    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">

        <span>
            © <?= date("Y") ?> Mini Digital Library
        </span>

        <a href="<?= $baseUrl ?>dashboard.php" class="footer-link">
            Kembali ke Dashboard ↑
        </a>

    </footer>

</div>

</body>
</html>
