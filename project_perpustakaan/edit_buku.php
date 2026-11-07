
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa hak akses Admin
mulaiSession();
wajibAdmin($koneksi);

// URL dasar project
$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan/";

// Mengambil ID buku dari URL
$idBuku = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($idBuku === false || $idBuku === null || $idBuku < 1) {
    die("ID buku tidak valid.");
}

// Mengambil data buku berdasarkan ID
$sql = "SELECT * FROM buku WHERE id_buku = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $idBuku);
$stmt->execute();

$result = $stmt->get_result();
$buku = $result->fetch_assoc();

$stmt->close();

// Memeriksa apakah buku ditemukan
if (!$buku) {
    die("Data buku tidak ditemukan.");
}

// Membuat token keamanan
$tokenCSRF = buatTokenCSRF();

// Memeriksa keberadaan sampul lama
$lokasiSampul = __DIR__ . "/uploads/" . $buku["sampul"];
$sampulAda = is_file($lokasiSampul);

// Mengamankan data pengguna
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

$sampul = rawurlencode($buku["sampul"]);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Buku - Mini Digital Library</title>

    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/dashboard.css?v=1">
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/edit_buku.css?v=1">
</head>

<body>

<div class="dashboard-page">

    <!-- Header -->
    <header class="dashboard-header">

        <a href="<?= $baseUrl ?>dashboard.php" class="brand">
            <span class="brand-icon">📚</span>

            <span class="brand-text">
                <strong>Mini Digital Library</strong>
                <small>Ruang Baca Digital</small>
            </span>
        </a>

        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($namaPengguna, 0, 1)); ?>
            </div>

            <div class="user-details">
                <strong><?= $namaPengguna; ?></strong>
                <small><?= $peranPengguna; ?></small>
            </div>
        </div>

    </header>

    <!-- Konten Utama -->
    <main class="edit-main">

        <!-- Judul Halaman -->
        <section class="edit-heading">

            <div class="edit-heading-text">
                <span class="edit-eyebrow">PENGELOLAAN KOLEKSI</span>

                <h1>Edit Buku</h1>

                <p>
                    Perbarui informasi buku agar koleksi perpustakaan
                    tetap lengkap dan terorganisasi.
                </p>
            </div>

            <div class="edit-decoration">
                <span>✏️</span>
            </div>

        </section>

        <!-- Navigasi -->
        <nav class="edit-navigation">

            <a href="<?= $baseUrl ?>dashboard.php">
                Beranda
            </a>

            <span>/</span>

            <a href="<?= $baseUrl ?>daftar_buku.php">
                Data Buku
            </a>

            <span>/</span>

            <strong>Edit Buku</strong>

        </nav>

        <!-- Form Edit -->
        <section class="edit-panel">

            <div class="edit-panel-heading">

                <div>
                    <span class="panel-eyebrow">PERBARUI DATA</span>

                    <h2>Informasi Buku</h2>

                    <p>
                        Silakan periksa dan ubah informasi buku di bawah ini.
                    </p>
                </div>

                <span class="edit-panel-icon">📖</span>

            </div>

            <form
                action="<?= $baseUrl ?>proses_edit.php"
                method="POST"
                enctype="multipart/form-data"
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

                <div class="edit-form-grid">

                    <!-- Judul Buku -->
                    <div class="edit-form-group">

                        <label for="judul_buku">Judul Buku</label>

                        <input
                            type="text"
                            id="judul_buku"
                            name="judul_buku"
                            maxlength="200"
                            value="<?= $judulBuku; ?>"
                            placeholder="Masukkan judul buku"
                            required
                        >

                    </div>

                    <!-- Kategori -->
                    <div class="edit-form-group">

                        <label for="kategori">Kategori</label>

                        <input
                            type="text"
                            id="kategori"
                            name="kategori"
                            maxlength="100"
                            value="<?= $kategori; ?>"
                            placeholder="Contoh: Pendidikan"
                            required
                        >

                    </div>

                    <!-- Penulis -->
                    <div class="edit-form-group">

                        <label for="penulis">Penulis</label>

                        <input
                            type="text"
                            id="penulis"
                            name="penulis"
                            maxlength="100"
                            value="<?= $penulis; ?>"
                            placeholder="Masukkan nama penulis"
                            required
                        >

                    </div>

                    <!-- Tahun Terbit -->
                    <div class="edit-form-group">

                        <label for="tahun_terbit">Tahun Terbit</label>

                        <input
                            type="number"
                            id="tahun_terbit"
                            name="tahun_terbit"
                            min="1901"
                            max="2155"
                            value="<?= $tahunTerbit; ?>"
                            placeholder="Contoh: 2024"
                            required
                        >

                    </div>

                    <!-- Stok -->
                    <div class="edit-form-group">

                        <label for="stok">Jumlah Stok</label>

                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            min="0"
                            step="1"
                            value="<?= (int) $buku["stok"]; ?>"
                            required
                        >

                        <small class="edit-hint">
                            Status ketersediaan diperbarui otomatis berdasarkan jumlah stok.
                        </small>

                    </div>

                    <!-- Sampul Lama -->
                    <div class="edit-form-group sampul-group">

                        <label>Sampul Buku Saat Ini</label>

                        <?php if ($sampulAda): ?>

                            <div class="sampul-preview">

                                <img
                                    src="<?= $baseUrl ?>uploads/<?= $sampul; ?>"
                                    alt="Sampul buku <?= $judulBuku; ?>"
                                    class="sampul-lama"
                                >

                                <div class="sampul-caption">
                                    <strong>Sampul tersimpan</strong>
                                    <small>Sampul buku yang sedang digunakan.</small>
                                </div>

                            </div>

                        <?php else: ?>

                            <div class="sampul-tidak-ada">
                                <span>📕</span>
                                <p>Sampul lama tidak ditemukan.</p>
                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- Ganti Sampul -->
                    <div class="edit-form-group sampul-baru-group">

                        <label for="sampul">Ganti Sampul Buku</label>

                        <input
                            type="file"
                            id="sampul"
                            name="sampul"
                            accept=".jpg,.jpeg,.png"
                        >

                        <small class="edit-hint">
                            Kosongkan jika ingin tetap menggunakan sampul lama.
                            Format JPG atau PNG, maksimal 2 MB.
                        </small>

                    </div>

                </div>

                <!-- Informasi -->
                <div class="edit-note">
                    <span>💡</span>

                    <p>
                        Pastikan informasi buku sudah benar sebelum menyimpan
                        perubahan. Sampul lama akan tetap digunakan jika
                        kamu tidak memilih file baru.
                    </p>
                </div>

                <!-- Tombol -->
                <div class="edit-form-actions">

                    <a
                        href="<?= $baseUrl ?>daftar_buku.php"
                        class="edit-button button-cancel"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="edit-button button-save"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </section>

        <footer class="edit-footer">
            Mini Digital Library &copy; <?= date("Y"); ?>
            <span>•</span>
            Kelola koleksi, perluas wawasan.
        </footer>

    </main>

</div>

</body>
</html>
