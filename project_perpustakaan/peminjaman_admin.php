
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa hak akses Admin
mulaiSession();
wajibAdmin($koneksi);

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

// Memproses persetujuan, penolakan, atau pengembalian
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!periksaTokenCSRF()) {
        die("Permintaan tidak valid. Silakan coba kembali.");
    }

    $idPeminjaman = filter_input(
        INPUT_POST,
        "id_peminjaman",
        FILTER_VALIDATE_INT
    );

    $aksi = $_POST["aksi"] ?? "";

    if (!$idPeminjaman || $idPeminjaman < 1) {
        header("Location: peminjaman_admin.php?pesan=data_tidak_valid");
        exit;
    }

    if (!in_array($aksi, ["setujui", "tolak", "kembalikan"], true)) {
        header("Location: peminjaman_admin.php?pesan=aksi_tidak_valid");
        exit;
    }

    $koneksi->begin_transaction();

    try {

        // Mengunci data peminjaman agar tidak diproses dua kali
        $query = "
            SELECT id_peminjaman, id_buku, status
            FROM peminjaman
            WHERE id_peminjaman = ?
            FOR UPDATE
        ";

        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $idPeminjaman);
        $stmt->execute();

        $result = $stmt->get_result();
        $peminjaman = $result->fetch_assoc();

        $stmt->close();

        if (!$peminjaman) {
            throw new Exception("data_tidak_ditemukan");
        }

        $idBuku = (int) $peminjaman["id_buku"];
        $status = $peminjaman["status"];

        // =====================================
        // PROSES PERSETUJUAN PEMINJAMAN
        // =====================================
        if ($aksi === "setujui") {

            if ($status !== "Menunggu") {
                throw new Exception("status_tidak_valid");
            }

            $queryBuku = "
                SELECT stok
                FROM buku
                WHERE id_buku = ?
                FOR UPDATE
            ";

            $stmtBuku = $koneksi->prepare($queryBuku);
            $stmtBuku->bind_param("i", $idBuku);
            $stmtBuku->execute();

            $resultBuku = $stmtBuku->get_result();
            $buku = $resultBuku->fetch_assoc();

            $stmtBuku->close();

            if (!$buku) {
                throw new Exception("buku_tidak_ditemukan");
            }

            if ((int) $buku["stok"] < 1) {
                throw new Exception("stok_habis");
            }

            // Mengurangi stok satu buku
            $queryStok = "
                UPDATE buku
                SET stok = stok - 1
                WHERE id_buku = ?
                  AND stok > 0
            ";

            $stmtStok = $koneksi->prepare($queryStok);
            $stmtStok->bind_param("i", $idBuku);
            $stmtStok->execute();

            if ($stmtStok->affected_rows !== 1) {
                $stmtStok->close();
                throw new Exception("stok_habis");
            }

            $stmtStok->close();

            // Masa peminjaman selama 7 hari
            $queryUpdate = "
                UPDATE peminjaman
                SET
                    status = 'Dipinjam',
                    tanggal_pinjam = CURDATE(),
                    tanggal_jatuh_tempo =
                        DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                WHERE id_peminjaman = ?
                  AND status = 'Menunggu'
            ";

            $stmtUpdate = $koneksi->prepare($queryUpdate);
            $stmtUpdate->bind_param("i", $idPeminjaman);
            $stmtUpdate->execute();

            if ($stmtUpdate->affected_rows !== 1) {
                $stmtUpdate->close();
                throw new Exception("status_tidak_valid");
            }

            $stmtUpdate->close();

            $koneksi->commit();

            header("Location: peminjaman_admin.php?pesan=disetujui");
            exit;
        }

        // =====================================
        // PROSES PENOLAKAN PEMINJAMAN
        // =====================================
        if ($aksi === "tolak") {

            if ($status !== "Menunggu") {
                throw new Exception("status_tidak_valid");
            }

            $queryTolak = "
                UPDATE peminjaman
                SET status = 'Ditolak'
                WHERE id_peminjaman = ?
                  AND status = 'Menunggu'
            ";

            $stmtTolak = $koneksi->prepare($queryTolak);
            $stmtTolak->bind_param("i", $idPeminjaman);
            $stmtTolak->execute();

            if ($stmtTolak->affected_rows !== 1) {
                $stmtTolak->close();
                throw new Exception("status_tidak_valid");
            }

            $stmtTolak->close();

            $koneksi->commit();

            header("Location: peminjaman_admin.php?pesan=ditolak");
            exit;
        }

        // =====================================
        // PROSES KONFIRMASI PENGEMBALIAN
        // =====================================
        if ($aksi === "kembalikan") {

            if ($status !== "Dipinjam") {
                throw new Exception("status_tidak_valid");
            }

            // Mengunci data buku
            $queryBuku = "
                SELECT id_buku
                FROM buku
                WHERE id_buku = ?
                FOR UPDATE
            ";

            $stmtBuku = $koneksi->prepare($queryBuku);
            $stmtBuku->bind_param("i", $idBuku);
            $stmtBuku->execute();

            $resultBuku = $stmtBuku->get_result();
            $buku = $resultBuku->fetch_assoc();

            $stmtBuku->close();

            if (!$buku) {
                throw new Exception("buku_tidak_ditemukan");
            }

            // Mengubah status dan mencatat tanggal kembali
            $queryKembali = "
                UPDATE peminjaman
                SET
                    status = 'Dikembalikan',
                    tanggal_kembali = CURDATE()
                WHERE id_peminjaman = ?
                  AND status = 'Dipinjam'
            ";

            $stmtKembali = $koneksi->prepare($queryKembali);
            $stmtKembali->bind_param("i", $idPeminjaman);
            $stmtKembali->execute();

            if ($stmtKembali->affected_rows !== 1) {
                $stmtKembali->close();
                throw new Exception("status_tidak_valid");
            }

            $stmtKembali->close();

            // Menambah stok kembali sebanyak satu buku
            $queryStok = "
                UPDATE buku
                SET stok = stok + 1
                WHERE id_buku = ?
            ";

            $stmtStok = $koneksi->prepare($queryStok);
            $stmtStok->bind_param("i", $idBuku);
            $stmtStok->execute();

            if ($stmtStok->affected_rows !== 1) {
                $stmtStok->close();
                throw new Exception("proses_gagal");
            }

            $stmtStok->close();

            $koneksi->commit();

            header("Location: peminjaman_admin.php?pesan=dikembalikan");
            exit;
        }

    } catch (Exception $e) {

        $koneksi->rollback();

        $kodePesan = $e->getMessage();

        $pesanDiizinkan = [
            "data_tidak_ditemukan",
            "status_tidak_valid",
            "buku_tidak_ditemukan",
            "stok_habis",
            "proses_gagal"
        ];

        if (!in_array($kodePesan, $pesanDiizinkan, true)) {
            $kodePesan = "proses_gagal";
        }

        header(
            "Location: peminjaman_admin.php?pesan=" . $kodePesan
        );
        exit;
    }
}

// =====================================
// NOTIFIKASI
// =====================================
$pesanNotifikasi = "";
$jenisNotifikasi = "";

switch ($_GET["pesan"] ?? "") {

    case "disetujui":
        $pesanNotifikasi = "Pengajuan berhasil disetujui. Masa peminjaman adalah 7 hari.";
        $jenisNotifikasi = "success";
        break;

    case "ditolak":
        $pesanNotifikasi = "Pengajuan peminjaman berhasil ditolak.";
        $jenisNotifikasi = "success";
        break;

    case "dikembalikan":
        $pesanNotifikasi = "Pengembalian berhasil dikonfirmasi. Stok buku telah bertambah satu.";
        $jenisNotifikasi = "success";
        break;

    case "stok_habis":
        $pesanNotifikasi = "Stok buku sudah habis. Pengajuan tidak dapat disetujui.";
        $jenisNotifikasi = "warning";
        break;

    case "status_tidak_valid":
        $pesanNotifikasi = "Peminjaman ini sudah diproses atau statusnya tidak sesuai.";
        $jenisNotifikasi = "warning";
        break;

    case "data_tidak_ditemukan":
    case "buku_tidak_ditemukan":
        $pesanNotifikasi = "Data peminjaman atau buku tidak ditemukan.";
        $jenisNotifikasi = "error";
        break;

    case "data_tidak_valid":
    case "aksi_tidak_valid":
    case "proses_gagal":
        $pesanNotifikasi = "Terjadi kesalahan. Silakan coba kembali.";
        $jenisNotifikasi = "error";
        break;
}

// =====================================
// MENGAMBIL DATA PEMINJAMAN
// =====================================
$queryDaftar = "
    SELECT
        p.id_peminjaman,
        p.tanggal_pengajuan,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.tanggal_kembali,
        p.status,
        u.nama,
        b.judul_buku,
        b.penulis
    FROM peminjaman p
    INNER JOIN users u
        ON p.id_user = u.id_user
    INNER JOIN buku b
        ON p.id_buku = b.id_buku
    ORDER BY
        CASE
            WHEN p.status = 'Menunggu' THEN 1
            WHEN p.status = 'Dipinjam' THEN 2
            ELSE 3
        END,
        p.tanggal_pengajuan DESC
";

$resultDaftar = $koneksi->query($queryDaftar);

if (!$resultDaftar) {
    die("Gagal mengambil data peminjaman.");
}

$tokenCSRF = buatTokenCSRF();

$namaPengguna = htmlspecialchars(
    $_SESSION["nama"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);

function aman($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? "-"),
        ENT_QUOTES,
        "UTF-8"
    );
}

function formatTanggal($tanggal)
{
    if (!$tanggal) {
        return "-";
    }

    return date("d-m-Y", strtotime($tanggal));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Peminjaman - Mini Digital Library</title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/dashboard.css?v=1"
    >

    <style>
        .loan-main {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 35px 24px;
        }

        .loan-heading {
            margin-bottom: 25px;
        }

        .loan-heading h1 {
            margin: 8px 0;
            color: #294d3c;
        }

        .loan-heading p {
            color: #718078;
            line-height: 1.6;
        }

        .loan-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #456b54;
            text-decoration: none;
            font-weight: 600;
        }

        .loan-notification {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: 12px;
            line-height: 1.6;
        }

        .notification-success {
            background: #e8f5e9;
            color: #276738;
        }

        .notification-warning {
            background: #fff6df;
            color: #856404;
        }

        .notification-error {
            background: #fdeaea;
            color: #a83232;
        }

        .loan-table-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        }

        .loan-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        .loan-table th,
        .loan-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #edf0ec;
            font-size: 13px;
        }

        .loan-table th {
            color: #456b54;
            background: #f3f7f1;
            font-weight: 700;
        }

        .loan-table td {
            color: #46564d;
        }

        .loan-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-menunggu {
            background: #fff3d7;
            color: #926700;
        }

        .status-dipinjam {
            background: #e3f0ff;
            color: #285d91;
        }

        .status-ditolak {
            background: #fdeaea;
            color: #a83232;
        }

        .status-dikembalikan {
            background: #e5f5e8;
            color: #276738;
        }

        .loan-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .loan-actions form {
            margin: 0;
        }

        .loan-action-button {
            border: none;
            border-radius: 8px;
            padding: 8px 11px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .button-approve {
            background: #e1f2e5;
            color: #28663b;
        }

        .button-reject {
            background: #fde8e8;
            color: #a83232;
        }

        .button-return {
            background: #e5f5e8;
            color: #276738;
        }

        .loan-empty {
            text-align: center;
            padding: 35px;
            color: #718078;
        }

        @media (max-width: 600px) {
            .loan-main {
                padding: 25px 14px;
            }

            .loan-table-wrapper {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-wrapper">

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
                <?= aman(strtoupper(substr($namaPengguna, 0, 1))); ?>
            </div>

            <div class="user-info">
                <strong><?= $namaPengguna; ?></strong>
                <span>Admin</span>
            </div>
        </div>

    </header>

    <main class="loan-main">

        <a href="<?= $baseUrl ?>/dashboard.php" class="loan-back">
            ← Kembali ke Dashboard
        </a>

        <section class="loan-heading">

            <span class="section-label">MANAJEMEN PERPUSTAKAAN</span>

            <h1>Data Peminjaman</h1>

            <p>
                Kelola pengajuan, persetujuan, dan riwayat
                peminjaman buku anggota.
            </p>

        </section>

        <?php if ($pesanNotifikasi !== ""): ?>

            <div class="loan-notification notification-<?= aman($jenisNotifikasi); ?>">
                <?= aman($pesanNotifikasi); ?>
            </div>

        <?php endif; ?>

        <section class="loan-table-wrapper">

            <table class="loan-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Anggota</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($resultDaftar->num_rows > 0): ?>

                        <?php $nomor = 1; ?>

                        <?php while ($data = $resultDaftar->fetch_assoc()): ?>

                            <?php
                            $kelasStatus = [
                                "Menunggu" => "status-menunggu",
                                "Dipinjam" => "status-dipinjam",
                                "Ditolak" => "status-ditolak",
                                "Dikembalikan" => "status-dikembalikan"
                            ];

                            $kelas = $kelasStatus[$data["status"]] ?? "";
                            ?>

                            <tr>

                                <td><?= $nomor++; ?></td>

                                <td>
                                    <strong><?= aman($data["nama"]); ?></strong>
                                </td>

                                <td>
                                    <?= aman($data["judul_buku"]); ?>
                                    <br>
                                    <small><?= aman($data["penulis"]); ?></small>
                                </td>

                                <td>
                                    <?= formatTanggal($data["tanggal_pengajuan"]); ?>
                                </td>

                                <td>
                                    <?= formatTanggal($data["tanggal_pinjam"]); ?>
                                </td>

                                <td>
                                    <?= formatTanggal($data["tanggal_jatuh_tempo"]); ?>
                                </td>

                                <td>
                                    <?= formatTanggal($data["tanggal_kembali"]); ?>
                                </td>

                                <td>
                                    <span class="loan-status <?= $kelas; ?>">
                                        <?= aman($data["status"]); ?>
                                    </span>
                                </td>

                                <td>

                                    <?php if ($data["status"] === "Menunggu"): ?>

                                        <div class="loan-actions">

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= aman($tokenCSRF); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_peminjaman"
                                                    value="<?= (int) $data["id_peminjaman"]; ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="setujui"
                                                >

                                                <button
                                                    type="submit"
                                                    class="loan-action-button button-approve"
                                                    onclick="return confirm('Setujui pengajuan peminjaman ini?')"
                                                >
                                                    ✓ Setujui
                                                </button>

                                            </form>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= aman($tokenCSRF); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_peminjaman"
                                                    value="<?= (int) $data["id_peminjaman"]; ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="tolak"
                                                >

                                                <button
                                                    type="submit"
                                                    class="loan-action-button button-reject"
                                                    onclick="return confirm('Tolak pengajuan peminjaman ini?')"
                                                >
                                                    ✕ Tolak
                                                </button>

                                            </form>

                                        </div>

                                    <?php elseif ($data["status"] === "Dipinjam"): ?>

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= aman($tokenCSRF); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id_peminjaman"
                                                value="<?= (int) $data["id_peminjaman"]; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="aksi"
                                                value="kembalikan"
                                            >

                                            <button
                                                type="submit"
                                                class="loan-action-button button-return"
                                                onclick="return confirm('Konfirmasi bahwa buku ini sudah dikembalikan? Stok buku akan bertambah satu.')"
                                            >
                                                ↩ Konfirmasi Pengembalian
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span>-</span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="loan-empty">
                                Belum ada data pengajuan peminjaman.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </section>

    </main>

    <footer class="dashboard-footer">

        <span>
            © <?= date("Y"); ?> Mini Digital Library
        </span>

        <a href="<?= $baseUrl ?>/dashboard.php" class="loan-back">
            Dashboard
        </a>

    </footer>

</div>

</body>
</html>
