
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai dan memeriksa session
mulaiSession();
wajibLogin($koneksi);

// Memeriksa peran pengguna
$isAdmin = ($_SESSION["role"] ?? "") === "Admin";
$idUser = (int) ($_SESSION["id_user"] ?? 0);

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

// Mengamankan informasi pengguna
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

// Memeriksa ID buku dari URL
$idBuku = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$idBuku || $idBuku < 1) {
    header("Location: " . $baseUrl . "/daftar_buku.php");
    exit;
}

// Mengambil detail buku berdasarkan ID
$query = "
    SELECT
        id_buku,
        judul_buku,
        kategori,
        penulis,
        tahun_terbit,
        stok,
        sampul
    FROM buku
    WHERE id_buku = ?
";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    die("Gagal menyiapkan data buku.");
}

$stmt->bind_param("i", $idBuku);
$stmt->execute();

$result = $stmt->get_result();
$buku = $result->fetch_assoc();

$stmt->close();

// Jika buku tidak ditemukan
if (!$buku) {
    http_response_code(404);
    die("Buku tidak ditemukan.");
}

// Menentukan status berdasarkan stok
$status = ((int) $buku["stok"] > 0)
    ? "Tersedia"
    : "Dipinjam";

// Memeriksa pengajuan atau peminjaman aktif Anggota
$statusPeminjaman = null;

if (!$isAdmin && $idUser > 0) {

    $queryPeminjaman = "
        SELECT status
        FROM peminjaman
        WHERE id_user = ?
          AND id_buku = ?
          AND status IN ('Menunggu', 'Dipinjam')
        ORDER BY id_peminjaman DESC
        LIMIT 1
    ";

    $stmtPeminjaman = $koneksi->prepare($queryPeminjaman);

    if (!$stmtPeminjaman) {
        die("Gagal memeriksa data peminjaman.");
    }

    $stmtPeminjaman->bind_param("ii", $idUser, $idBuku);
    $stmtPeminjaman->execute();

    $resultPeminjaman = $stmtPeminjaman->get_result();
    $dataPeminjaman = $resultPeminjaman->fetch_assoc();

    if ($dataPeminjaman) {
        $statusPeminjaman = $dataPeminjaman["status"];
    }

    $stmtPeminjaman->close();
}

// Membuat token CSRF
$tokenCSRF = buatTokenCSRF();

// Menyiapkan notifikasi pengajuan peminjaman
$pesanNotifikasi = "";
$jenisNotifikasi = "";

$pesan = $_GET["pesan"] ?? "";

switch ($pesan) {
    case "pengajuan_berhasil":
        $pesanNotifikasi = "Pengajuan peminjaman berhasil dikirim. Silakan tunggu persetujuan Admin.";
        $jenisNotifikasi = "success";
        break;

    case "pengajuan_sudah_ada":
        $pesanNotifikasi = "Kamu masih memiliki pengajuan atau peminjaman aktif untuk buku ini.";
        $jenisNotifikasi = "warning";
        break;

    case "stok_habis":
        $pesanNotifikasi = "Maaf, stok buku sudah habis.";
        $jenisNotifikasi = "warning";
        break;

    case "buku_tidak_ditemukan":
        $pesanNotifikasi = "Buku tidak ditemukan.";
        $jenisNotifikasi = "error";
        break;

    case "pengajuan_gagal":
        $pesanNotifikasi = "Pengajuan gagal diproses. Silakan coba kembali.";
        $jenisNotifikasi = "error";
        break;
}

// Mengamankan nama file sampul
$namaSampul = basename($buku["sampul"] ?? "");

// Mengamankan data buku
$judulBuku = htmlspecialchars(
    $buku["judul_buku"],
    ENT_QUOTES,
    "UTF-8"
);

$kategori = htmlspecialchars(
    $buku["kategori"],
    ENT_QUOTES,
    "UTF-8"
);

$penulis = htmlspecialchars(
    $buku["penulis"],
    ENT_QUOTES,
    "UTF-8"
);

$tahunTerbit = htmlspecialchars(
    (string) $buku["tahun_terbit"],
    ENT_QUOTES,
    "UTF-8"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Buku - Mini Digital Library</title>

    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/dashboard.css?v=1">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/detail_buku.css?v=1">
</head>

<body>

<div class="dashboard-page">

    <!-- Header -->
    <header class="dashboard-header">

        <a href="<?= $baseUrl ?>/dashboard.php" class="brand">

            <span class="brand-icon">📚</span>

            <span class="brand-text">
                <strong>Mini Digital Library</strong>
                <small>Ruang Baca Digital</small>
            </span>

        </a>

        <div class="user-info">

            <div class="user-avatar">
                <?= htmlspecialchars(
                    strtoupper(substr($_SESSION["nama"] ?? "P", 0, 1)),
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>
            </div>

            <div class="user-details">
                <strong><?= $namaPengguna; ?></strong>
                <small><?= $peranPengguna; ?></small>
            </div>

        </div>

    </header>

    <!-- Konten Utama -->
    <main class="detail-main">
        <?php if ($pesanNotifikasi !== ""): ?>

            <div class="loan-notification notification-<?= $jenisNotifikasi; ?>">
              <?= htmlspecialchars($pesanNotifikasi, ENT_QUOTES, "UTF-8"); ?>
            </div>

        <?php endif; ?>

        <!-- Heading -->
        <section class="detail-heading">

            <div>
                <span class="detail-eyebrow">KOLEKSI PERPUSTAKAAN</span>

                <h1>Detail Buku</h1>

                <p>
                    Kenali informasi lengkap dari koleksi buku
                    yang tersedia di perpustakaan digital.
                </p>
            </div>

            <div class="detail-decoration">
                📖
            </div>

        </section>

        <!-- Navigasi -->
        <nav class="detail-navigation">

            <a href="<?= $baseUrl ?>/dashboard.php">
                Beranda
            </a>

            <span>/</span>

            <a href="<?= $baseUrl ?>/daftar_buku.php">
                Data Buku
            </a>

            <span>/</span>

            <strong>Detail Buku</strong>

        </nav>

        <!-- Detail Buku -->
        <section class="detail-panel">

            <!-- Bagian Sampul -->
            <div class="detail-cover-section">

                <div class="detail-cover">

                    <?php if ($namaSampul !== "" && is_file(__DIR__ . "/uploads/" . $namaSampul)): ?>

                        <img
                            src="<?= $baseUrl ?>/uploads/<?= rawurlencode($namaSampul); ?>"
                            alt="Sampul <?= $judulBuku; ?>"
                        >

                    <?php else: ?>

                        <div class="cover-placeholder">
                            <span>📕</span>
                            <small>Sampul tidak tersedia</small>
                        </div>

                    <?php endif; ?>

                </div>

                <div class="cover-caption">
                    <span class="cover-label">KOLEKSI BUKU</span>

                    <p>
                        Buku bagian dari koleksi
                        Mini Digital Library.
                    </p>
                </div>

            </div>

            <!-- Bagian Informasi -->
            <div class="detail-information">

                <div class="detail-title-area">

                    <span class="detail-category">
                        <?= $kategori; ?>
                    </span>

                    <h2><?= $judulBuku; ?></h2>

                    <p class="detail-author">
                        Ditulis oleh <strong><?= $penulis; ?></strong>
                    </p>

                </div>

                <div class="availability-box">

                    <span class="availability-label">
                        Status Ketersediaan
                    </span>

                    <span class="availability-status <?= $status === "Tersedia" ? "status-available" : "status-unavailable"; ?>">

                        <span class="status-dot"></span>

                        <?= htmlspecialchars($status, ENT_QUOTES, "UTF-8"); ?>

                    </span>

                </div>

                <div class="detail-divider"></div>

                <h3>Informasi Buku</h3>

                <div class="information-list">

                    <div class="information-item">
                        <span>ID Buku</span>
                        <strong><?= (int) $buku["id_buku"]; ?></strong>
                    </div>

                    <div class="information-item">
                        <span>Judul Buku</span>
                        <strong><?= $judulBuku; ?></strong>
                    </div>

                    <div class="information-item">
                        <span>Kategori</span>
                        <strong><?= $kategori; ?></strong>
                    </div>

                    <div class="information-item">
                        <span>Penulis</span>
                        <strong><?= $penulis; ?></strong>
                    </div>

                    <div class="information-item">
                        <span>Tahun Terbit</span>
                        <strong><?= $tahunTerbit; ?></strong>
                    </div>

                    <div class="information-item">
                        <span>Jumlah Stok</span>
                        <strong><?= (int) $buku["stok"]; ?> buku</strong>
                    </div>

                </div>

                <!-- Tombol Aksi -->
                <div class="detail-actions">

                    <?php if ($isAdmin): ?>

                        <a
                            href="<?= $baseUrl ?>/edit_buku.php?id=<?= (int) $buku["id_buku"]; ?>"
                            class="detail-button button-edit"
                        >
                            ✏️ Edit Buku
                        </a>

                    <?php else: ?>

                        <?php if ($statusPeminjaman === "Menunggu"): ?>

                            <p class="loan-information">
                                Pengajuan peminjaman buku ini sedang menunggu persetujuan Admin.
                            </p>

                        <?php elseif ($statusPeminjaman === "Dipinjam"): ?>

                            <p class="loan-information">
                                Kamu sedang meminjam buku ini. Selesaikan pengembalian terlebih dahulu.
                            </p>

                        <?php elseif ((int) $buku["stok"] > 0): ?>

                            <form
                                action="<?= $baseUrl ?>/proses_pengajuan.php"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($tokenCSRF, ENT_QUOTES, "UTF-8"); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_buku"
                                    value="<?= (int) $buku["id_buku"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="detail-button button-loan"
                                >
                                    📚 Ajukan Peminjaman
                                </button>

                            </form>

                        <?php else: ?>

                            <p class="loan-information">
                                Maaf, stok buku sedang habis sehingga belum bisa diajukan.
                            </p>

                        <?php endif; ?>

                    <?php endif; ?>

                    <a
                        href="<?= $baseUrl ?>/daftar_buku.php"
                        class="detail-button button-back"
                    >
                        ← Kembali ke Data Buku
                    </a>

                </div>

            </div>

        </section>

        <!-- Footer -->
        <footer class="detail-footer">
            Mini Digital Library &copy; <?= date("Y"); ?>
            <span>•</span>
            Kelola koleksi, perluas wawasan.
        </footer>

    </main>

</div>

</body>
</html>
