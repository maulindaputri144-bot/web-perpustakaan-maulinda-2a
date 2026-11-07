
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa login
mulaiSession();
wajibLogin($koneksi);

// Halaman ini khusus untuk Anggota
if (($_SESSION["role"] ?? "") !== "Anggota") {
    header("Location: dashboard.php");
    exit;
}

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

$idUser = (int) ($_SESSION["id_user"] ?? 0);

if ($idUser < 1) {
    header("Location: logout.php");
    exit;
}

// Mengambil riwayat peminjaman milik anggota yang sedang login
$query = "
    SELECT
        p.id_peminjaman,
        p.tanggal_pengajuan,
        p.tanggal_pinjam,
        p.tanggal_jatuh_tempo,
        p.tanggal_kembali,
        p.status,
        b.judul_buku,
        b.penulis,
        b.sampul
    FROM peminjaman p
    INNER JOIN buku b
        ON p.id_buku = b.id_buku
    WHERE p.id_user = ?
    ORDER BY p.tanggal_pengajuan DESC
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $idUser);
$stmt->execute();

$resultRiwayat = $stmt->get_result();

$namaPengguna = htmlspecialchars(
    $_SESSION["nama"] ?? "Anggota",
    ENT_QUOTES,
    "UTF-8"
);

function amanRiwayat($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? "-"),
        ENT_QUOTES,
        "UTF-8"
    );
}

function tanggalRiwayat($tanggal)
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

    <title>Riwayat Peminjaman - Mini Digital Library</title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/dashboard.css?v=1"
    >

    <style>
        .history-main {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 35px 24px;
        }

        .history-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #456b54;
            text-decoration: none;
            font-weight: 600;
        }

        .history-heading {
            margin-bottom: 25px;
        }

        .history-heading h1 {
            margin: 8px 0;
            color: #294d3c;
        }

        .history-heading p {
            color: #718078;
            line-height: 1.6;
        }

        .history-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        }

        .history-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .history-table th,
        .history-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #edf0ec;
            font-size: 13px;
        }

        .history-table th {
            color: #456b54;
            background: #f3f7f1;
            font-weight: 700;
        }

        .history-table td {
            color: #46564d;
        }

        .history-status {
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

        .history-empty {
            text-align: center;
            padding: 35px;
            color: #718078;
        }

        @media (max-width: 600px) {
            .history-main {
                padding: 25px 14px;
            }

            .history-wrapper {
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
                <?= amanRiwayat(strtoupper(substr($namaPengguna, 0, 1))); ?>
            </div>

            <div class="user-info">
                <strong><?= $namaPengguna; ?></strong>
                <span>Anggota</span>
            </div>
        </div>

    </header>

    <main class="history-main">

        <a href="<?= $baseUrl ?>/dashboard.php" class="history-back">
            ← Kembali ke Dashboard
        </a>

        <section class="history-heading">

            <span class="section-label">AKTIVITAS ANGGOTA</span>

            <h1>Riwayat Peminjaman</h1>

            <p>
                Lihat daftar pengajuan dan riwayat peminjaman
                buku yang pernah kamu lakukan.
            </p>

        </section>

        <section class="history-wrapper">

            <table class="history-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($resultRiwayat->num_rows > 0): ?>

                        <?php $nomor = 1; ?>

                        <?php while ($data = $resultRiwayat->fetch_assoc()): ?>

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
                                    <strong><?= amanRiwayat($data["judul_buku"]); ?></strong>
                                </td>

                                <td>
                                    <?= amanRiwayat($data["penulis"]); ?>
                                </td>

                                <td>
                                    <?= tanggalRiwayat($data["tanggal_pengajuan"]); ?>
                                </td>

                                <td>
                                    <?= tanggalRiwayat($data["tanggal_pinjam"]); ?>
                                </td>

                                <td>
                                    <?= tanggalRiwayat($data["tanggal_jatuh_tempo"]); ?>
                                </td>

                                <td>
                                    <?= tanggalRiwayat($data["tanggal_kembali"]); ?>
                                </td>

                                <td>
                                    <span class="history-status <?= $kelas; ?>">
                                        <?= amanRiwayat($data["status"]); ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="history-empty">
                                Kamu belum memiliki riwayat peminjaman buku.
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

        <a href="<?= $baseUrl ?>/dashboard.php" class="history-back">
            Dashboard
        </a>

    </footer>

</div>

</body>
</html>

<?php
$stmt->close();
?>
