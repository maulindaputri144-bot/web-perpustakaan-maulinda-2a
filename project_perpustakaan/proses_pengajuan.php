
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai dan memeriksa session
mulaiSession();
wajibLogin($koneksi);

// Hanya Anggota yang boleh mengajukan peminjaman
if (($_SESSION["role"] ?? "") !== "Anggota") {
    header("Location: daftar_buku.php");
    exit;
}

// Memastikan metode pengiriman POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: daftar_buku.php");
    exit;
}

// Memeriksa token CSRF
if (!periksaTokenCSRF()) {
    die("Permintaan tidak valid. Silakan kembali dan coba lagi.");
}

// Mengambil ID pengguna dan ID buku
$idUser = (int) ($_SESSION["id_user"] ?? 0);
$idBuku = filter_input(INPUT_POST, "id_buku", FILTER_VALIDATE_INT);

if ($idUser < 1 || !$idBuku || $idBuku < 1) {
    header("Location: daftar_buku.php");
    exit;
}

// Memulai transaksi database
$koneksi->begin_transaction();

try {

    // Mengunci data buku agar stok tidak berubah saat diperiksa
    $queryBuku = "
        SELECT id_buku, stok
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

    // Memastikan stok masih tersedia
    if ((int) $buku["stok"] < 1) {
        throw new Exception("stok_habis");
    }

    // Memeriksa pengajuan atau peminjaman aktif
    $queryCek = "
        SELECT id_peminjaman
        FROM peminjaman
        WHERE id_user = ?
          AND id_buku = ?
          AND status IN ('Menunggu', 'Dipinjam')
        LIMIT 1
    ";

    $stmtCek = $koneksi->prepare($queryCek);
    $stmtCek->bind_param("ii", $idUser, $idBuku);
    $stmtCek->execute();

    $resultCek = $stmtCek->get_result();
    $peminjamanAktif = $resultCek->fetch_assoc();

    $stmtCek->close();

    if ($peminjamanAktif) {
        throw new Exception("pengajuan_sudah_ada");
    }

    // Menyimpan pengajuan dengan status Menunggu
    $queryInsert = "
        INSERT INTO peminjaman (
            id_user,
            id_buku,
            status
        )
        VALUES (?, ?, 'Menunggu')
    ";

    $stmtInsert = $koneksi->prepare($queryInsert);
    $stmtInsert->bind_param("ii", $idUser, $idBuku);
    $stmtInsert->execute();
    $stmtInsert->close();

    // Menyelesaikan transaksi
    $koneksi->commit();

    header(
        "Location: detail_buku.php?id=" . $idBuku . "&pesan=pengajuan_berhasil"
    );
    exit;

} catch (Exception $e) {

    // Membatalkan transaksi jika terjadi kesalahan
    $koneksi->rollback();

    $pesan = $e->getMessage();

    if ($pesan === "buku_tidak_ditemukan") {
        $kodePesan = "buku_tidak_ditemukan";
    } elseif ($pesan === "stok_habis") {
        $kodePesan = "stok_habis";
    } elseif ($pesan === "pengajuan_sudah_ada") {
        $kodePesan = "pengajuan_sudah_ada";
    } else {
        $kodePesan = "pengajuan_gagal";
    }

    header(
        "Location: detail_buku.php?id=" . $idBuku . "&pesan=" . $kodePesan
    );
    exit;
}
