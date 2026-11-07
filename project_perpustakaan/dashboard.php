
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai dan memeriksa session login
mulaiSession();
wajibLogin($koneksi);

// Memeriksa peran pengguna
$isAdmin = ($_SESSION["role"] ?? "") === "Admin";

// Mengambil statistik buku dari database
$queryStatistik = "
    SELECT
        COUNT(*) AS total_buku,
        COALESCE(SUM(stok), 0) AS total_stok,
        COALESCE(SUM(CASE WHEN stok > 0 THEN 1 ELSE 0 END), 0)
            AS buku_tersedia,
        COALESCE(SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END), 0)
            AS buku_dipinjam
    FROM buku
";

$resultStatistik = $koneksi->query($queryStatistik);

if (!$resultStatistik) {
    die("Gagal mengambil statistik perpustakaan.");
}

$statistik = $resultStatistik->fetch_assoc();

$namaPengguna = htmlspecialchars(
    $_SESSION["nama"] ?? "Pengguna",
    ENT_QUOTES,
    "UTF-8"
);

$peranPengguna = htmlspecialchars(
    $_SESSION["role"] ?? "Anggota",
    ENT_QUOTES,
    "UTF-8"
);

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Mini Digital Library</title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/dashboard.css?v=1"
    >
</head>

<body>

    <div class="dashboard-wrapper">

        <!-- Header -->
        <header class="dashboard-header">

            <a href="<?= $baseUrl ?>/dashboard.php" class="brand">
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
        <main class="dashboard-main">

            <!-- Sapaan -->
            <section class="welcome-section">

                <div class="welcome-text">
                    <span class="section-label">
                        MINI DIGITAL LIBRARY
                    </span>

                    <h1>
                        Halo, <?= $namaPengguna ?>!
                    </h1>

                    <p>
                        Selamat datang kembali. Siap menemukan
                        bacaan menarik hari ini?
                    </p>
                </div>

                <div class="welcome-illustration">
                    <span class="illustration-star">✦</span>
                    <span class="illustration-book">📚</span>
                    <span class="illustration-leaf">🌿</span>
                </div>

            </section>

            <!-- Statistik -->
            <section class="statistics-section">

                <div class="section-heading">
                    <div>
                        <span class="section-label">RINGKASAN</span>
                        <h2>Statistik Perpustakaan</h2>
                    </div>

                    <span class="live-label">
                        <span></span> Data terbaru
                    </span>
                </div>

                <div class="statistics-grid">

                    <article class="stat-card">
                        <div class="stat-icon icon-book">▤</div>

                        <div class="stat-information">
                            <p>Total Buku</p>
                            <h3>
                                <?= (int) $statistik["total_buku"] ?>
                            </h3>
                            <span>Jumlah judul buku</span>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon icon-stock">▥</div>

                        <div class="stat-information">
                            <p>Total Stok</p>
                            <h3>
                                <?= (int) $statistik["total_stok"] ?>
                            </h3>
                            <span>Seluruh eksemplar buku</span>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon icon-available">✓</div>

                        <div class="stat-information">
                            <p>Buku Tersedia</p>
                            <h3>
                                <?= (int) $statistik["buku_tersedia"] ?>
                            </h3>
                            <span>Judul dengan stok tersedia</span>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon icon-unavailable">↗</div>

                        <div class="stat-information">
                            <p>Buku Dipinjam</p>
                            <h3>
                                <?= (int) $statistik["buku_dipinjam"] ?>
                            </h3>
                            <span>Judul dengan stok 0</span>
                        </div>
                    </article>

                </div>

            </section>

            <!-- Menu utama -->
            <section class="menu-section">

                <div class="section-heading">
                    <div>
                        <span class="section-label">JELAJAHI</span>
                        <h2>Menu Utama</h2>
                    </div>
                </div>

                <div class="menu-grid">

                    <!-- Menu Data Buku -->
                    <a
                        href="<?= $baseUrl ?>/daftar_buku.php"
                        class="menu-card"
                    >
                        <div class="menu-icon">📖</div>

                        <div class="menu-description">
                            <h3>Data Buku</h3>

                            <p>
                                Lihat dan telusuri koleksi buku
                                yang tersedia di perpustakaan.
                            </p>

                            <span class="menu-link">
                                Lihat koleksi <span>→</span>
                            </span>
                        </div>
                    </a>

                    <?php if ($isAdmin): ?>

                        <!-- Menu Tambah Buku -->
                        <a
                            href="<?= $baseUrl ?>/tambah_buku.php"
                            class="menu-card"
                        >
                            <div class="menu-icon add-icon">＋</div>

                            <div class="menu-description">
                                <h3>Tambah Buku</h3>

                                <p>
                                    Tambahkan koleksi buku baru
                                    ke dalam perpustakaan.
                                </p>

                                <span class="menu-link">
                                    Tambah koleksi <span>→</span>
                                </span>
                            </div>
                        </a>

                        <!-- Menu Data Peminjaman Admin -->
                        <a
                            href="<?= $baseUrl ?>/peminjaman_admin.php"
                            class="menu-card"
                        >
                            <div class="menu-icon">📋</div>

                            <div class="menu-description">
                                <h3>Data Peminjaman</h3>

                                <p>
                                    Kelola pengajuan peminjaman,
                                    persetujuan, dan pengembalian buku.
                                </p>

                                <span class="menu-link">
                                    Kelola peminjaman <span>→</span>
                                </span>
                            </div>
                        </a>

                    <?php else: ?>

                        <!-- Menu Riwayat Peminjaman Anggota -->
                        <a
                            href="<?= $baseUrl ?>/riwayat_peminjaman.php"
                            class="menu-card"
                        >
                            <div class="menu-icon">📋</div>

                            <div class="menu-description">
                                <h3>Riwayat Peminjaman</h3>

                                <p>
                                    Lihat status pengajuan, buku yang
                                    sedang dipinjam, dan riwayat pengembalian.
                                </p>

                                <span class="menu-link">
                                    Lihat riwayat <span>→</span>
                                </span>
                            </div>
                        </a>

                    <?php endif; ?>

                </div>

            </section>

            <!-- Informasi -->
            <section class="information-section">
                <span class="information-icon">✦</span>

                <div>
                    <strong>Selamat membaca!</strong>

                    <p>
                        Temukan pengetahuan baru melalui setiap
                        halaman buku yang kamu baca.
                    </p>
                </div>
            </section>

        </main>

        <!-- Footer -->
        <footer class="dashboard-footer">

            <span>
                © <?= date("Y") ?> Mini Digital Library
            </span>

            <form
                action="<?= $baseUrl ?>/logout.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        buatTokenCSRF(),
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                >

                <button type="submit" class="logout-button">
                    <span>↪</span> Keluar
                </button>

            </form>

        </footer>

    </div>

</body>
</html>
