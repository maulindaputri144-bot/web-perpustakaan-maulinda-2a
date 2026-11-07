
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa login
mulaiSession();
wajibLogin($koneksi);

// Memeriksa peran pengguna
$isAdmin = ($_SESSION["role"] ?? "") === "Admin";

// URL dasar project
$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan/";

// =====================================
// NOTIFIKASI PENGHAPUSAN BUKU
// =====================================

$pesan = $_GET["pesan"] ?? "";

$notifikasi = [
    "hapus_berhasil" => [
        "kelas" => "notification-success",
        "judul" => "Buku berhasil dihapus!",
        "deskripsi" => "Data buku berhasil dihapus dari perpustakaan."
    ],

    "hapus_sampul_gagal" => [
        "kelas" => "notification-warning",
        "judul" => "Buku berhasil dihapus, tetapi sampul gagal dihapus.",
        "deskripsi" => "Data buku sudah terhapus dari database, tetapi file sampul masih perlu diperiksa."
    ]
];

$pesanNotifikasi = $notifikasi[$pesan] ?? null;

// =====================================
// PENCARIAN DAN FILTER
// =====================================

// Mengambil kata kunci dan filter dari URL
$kataKunci = trim($_GET["cari"] ?? "");
$kategoriFilter = trim($_GET["kategori"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

// Mengambil daftar kategori dari database
$queryKategori = $koneksi->query(
    "SELECT DISTINCT kategori FROM buku ORDER BY kategori ASC"
);

$daftarKategori = [];

if ($queryKategori) {
    while ($rowKategori = $queryKategori->fetch_assoc()) {
        $daftarKategori[] = $rowKategori["kategori"];
    }
}

// Kondisi pencarian dan filter
$kondisi = "
    WHERE (? = '' OR
        judul_buku LIKE ? OR
        penulis LIKE ? OR
        kategori LIKE ?)
    AND (? = '' OR kategori = ?)
    AND (? = '' OR
        (? = 'Tersedia' AND stok > 0) OR
        (? = 'Dipinjam' AND stok = 0))
";

// Menyiapkan nilai pencarian
$pencarian = "%" . $kataKunci . "%";

// =====================================
// MENGHITUNG JUMLAH BUKU
// =====================================

$sqlJumlah = "SELECT COUNT(*) AS total FROM buku " . $kondisi;

$stmtJumlah = $koneksi->prepare($sqlJumlah);

$stmtJumlah->bind_param(
    "sssssssss",
    $kataKunci,
    $pencarian,
    $pencarian,
    $pencarian,
    $kategoriFilter,
    $kategoriFilter,
    $statusFilter,
    $statusFilter,
    $statusFilter
);

$stmtJumlah->execute();

$hasilJumlah = $stmtJumlah->get_result()->fetch_assoc();

$totalBuku = (int) $hasilJumlah["total"];

$stmtJumlah->close();

// =====================================
// PAGINATION
// =====================================

$bukuPerHalaman = 5;

$totalHalaman = max(
    1,
    (int) ceil($totalBuku / $bukuPerHalaman)
);

// Memeriksa nomor halaman
$halaman = filter_var(
    $_GET["halaman"] ?? 1,
    FILTER_VALIDATE_INT
);

if ($halaman === false || $halaman === null || $halaman < 1) {
    $halaman = 1;
}

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

// Menghitung posisi awal data
$offset = ($halaman - 1) * $bukuPerHalaman;

// =====================================
// MENGAMBIL DATA BUKU
// =====================================

$sqlBuku = "
    SELECT *
    FROM buku
    $kondisi
    ORDER BY id_buku DESC
    LIMIT ? OFFSET ?
";

$stmtBuku = $koneksi->prepare($sqlBuku);

$stmtBuku->bind_param(
    "sssssssssii",
    $kataKunci,
    $pencarian,
    $pencarian,
    $pencarian,
    $kategoriFilter,
    $kategoriFilter,
    $statusFilter,
    $statusFilter,
    $statusFilter,
    $bukuPerHalaman,
    $offset
);

$stmtBuku->execute();

$hasilBuku = $stmtBuku->get_result();

// =====================================
// URL PAGINATION
// =====================================

function urlHalaman($nomorHalaman)
{
    $parameter = $_GET;
    $parameter["halaman"] = $nomorHalaman;

    return "daftar_buku.php?" . http_build_query($parameter);
}

// Token CSRF untuk penghapusan
$tokenCSRF = $isAdmin ? buatTokenCSRF() : "";

// =====================================
// INFORMASI PENGGUNA
// =====================================

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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Buku | Mini Digital Library</title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>assets/css/dashboard.css?v=1"
    >

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>assets/css/daftar_buku.css?v=2"
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
    <main class="data-main">

        <!-- Judul halaman -->
        <section class="data-heading">

            <div>
                <span class="section-label">KOLEKSI PERPUSTAKAAN</span>

                <h1>Data Buku</h1>

                <p>
                    Temukan dan jelajahi berbagai koleksi bacaan
                    di Mini Digital Library.
                </p>
            </div>

            <div class="data-decoration">
                <span>📚</span>
                <span>✦</span>
            </div>

        </section>

        <!-- Navigasi -->
        <nav class="data-navigation">

            <a href="<?= $baseUrl ?>dashboard.php" class="nav-back">
                <span>←</span> Dashboard
            </a>

            <?php if ($isAdmin): ?>

                <a
                    href="<?= $baseUrl ?>tambah_buku.php"
                    class="add-book-button"
                >
                    <span>＋</span> Tambah Buku
                </a>

            <?php endif; ?>

        </nav>

        <!-- Panel koleksi -->
        <section class="book-panel">

            <!-- NOTIFIKASI -->
            <?php if ($pesanNotifikasi): ?>

                <div
                    class="notification <?= $pesanNotifikasi["kelas"]; ?>"
                    role="alert"
                >

                    <span class="notification-icon">
                        <?= $pesan === "hapus_berhasil" ? "✓" : "⚠"; ?>
                    </span>

                    <div class="notification-content">

                        <strong>
                            <?= htmlspecialchars(
                                $pesanNotifikasi["judul"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                        </strong>

                        <p>
                            <?= htmlspecialchars(
                                $pesanNotifikasi["deskripsi"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>

            <div class="panel-heading">

                <div>
                    <span class="section-label">DAFTAR KOLEKSI</span>
                    <h2>Jelajahi Buku</h2>
                </div>

                <div class="total-badge">
                    <?= $totalBuku ?> buku ditemukan
                </div>

            </div>

            <!-- Form pencarian dan filter -->
            <form
                method="GET"
                action="<?= $baseUrl ?>daftar_buku.php"
                class="filter-form"
            >

                <div class="search-field">
                    <span class="search-icon">⌕</span>

                    <input
                        type="text"
                        name="cari"
                        placeholder="Cari judul, penulis, atau kategori..."
                        value="<?= htmlspecialchars(
                            $kataKunci,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >
                </div>

                <select name="kategori">

                    <option value="">Semua Kategori</option>

                    <?php foreach ($daftarKategori as $kategori): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $kategori,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            <?= $kategoriFilter === $kategori ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars(
                                $kategori,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <select name="status">

                    <option value="">Semua Status</option>

                    <option
                        value="Tersedia"
                        <?= $statusFilter === "Tersedia" ? "selected" : "" ?>
                    >
                        Tersedia
                    </option>

                    <option
                        value="Dipinjam"
                        <?= $statusFilter === "Dipinjam" ? "selected" : "" ?>
                    >
                        Dipinjam
                    </option>

                </select>

                <button type="submit" class="search-button">
                    Cari
                </button>

                <a
                    href="<?= $baseUrl ?>daftar_buku.php"
                    class="reset-button"
                >
                    Reset
                </a>

            </form>

            <!-- Tabel buku -->
            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Sampul</th>
                            <th>Judul Buku</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th>Tahun Terbit</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($hasilBuku->num_rows > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($buku = $hasilBuku->fetch_assoc()): ?>

                                <?php

                                // Status ditentukan berdasarkan stok
                                if ((int) $buku["stok"] > 0) {
                                    $status = "Tersedia";
                                    $kelasStatus = "status-tersedia";
                                } else {
                                    $status = "Dipinjam";
                                    $kelasStatus = "status-dipinjam";
                                }

                                $idBuku = (int) $buku["id_buku"];

                                ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td>

                                        <?php if (!empty($buku["sampul"])): ?>

                                            <img
                                                src="<?= $baseUrl ?>uploads/<?= rawurlencode(
                                                    basename($buku["sampul"])
                                                ) ?>"
                                                alt="Sampul Buku"
                                                class="sampul"
                                            >

                                        <?php else: ?>

                                            <div class="no-cover">
                                                Tidak ada sampul
                                            </div>

                                        <?php endif; ?>

                                    </td>

                                    <td class="book-title">
                                        <?= htmlspecialchars(
                                            $buku["judul_buku"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $buku["kategori"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $buku["penulis"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $buku["tahun_terbit"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td class="stock-number">
                                        <?= (int) $buku["stok"] ?>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= $kelasStatus ?>">
                                            <?= $status ?>
                                        </span>
                                    </td>

                                    <td>

                                        <div class="aksi">

                                            <a
                                                href="<?= $baseUrl ?>detail_buku.php?id=<?= $idBuku ?>"
                                                class="btn-detail"
                                            >
                                                Detail
                                            </a>

                                            <?php if ($isAdmin): ?>

                                                <a
                                                    href="<?= $baseUrl ?>edit_buku.php?id=<?= $idBuku ?>"
                                                    class="btn-edit"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    action="<?= $baseUrl ?>hapus_buku.php"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus buku ini?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id_buku"
                                                        value="<?= $idBuku ?>"
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

                                                    <button
                                                        type="submit"
                                                        class="btn-hapus"
                                                    >
                                                        Hapus
                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="9" class="empty-data">

                                    <div class="empty-icon">📖</div>

                                    <strong>Buku tidak ditemukan</strong>

                                    <p>
                                        Coba gunakan kata kunci atau filter
                                        pencarian yang berbeda.
                                    </p>

                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            <?php if ($totalHalaman > 1): ?>

                <div class="pagination">

                    <a
                        href="<?= htmlspecialchars(
                            urlHalaman(max(1, $halaman - 1)),
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        class="<?= $halaman <= 1 ? "disabled" : "" ?>"
                    >
                        &laquo; Sebelumnya
                    </a>

                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                        <a
                            href="<?= htmlspecialchars(
                                urlHalaman($i),
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            class="<?= $halaman === $i ? "aktif" : "" ?>"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                    <a
                        href="<?= htmlspecialchars(
                            urlHalaman(min($totalHalaman, $halaman + 1)),
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        class="<?= $halaman >= $totalHalaman ? "disabled" : "" ?>"
                    >
                        Berikutnya &raquo;
                    </a>

                </div>

            <?php endif; ?>

            <div class="page-information">
                Menampilkan
                <?= $hasilBuku->num_rows ?>
                dari
                <?= $totalBuku ?>
                buku
                <?php if ($totalBuku > 0): ?>
                    · Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                <?php endif; ?>
            </div>

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

<?php
$stmtBuku->close();
?>
