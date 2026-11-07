
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session
mulaiSession();

// Hanya Admin yang boleh menghapus buku
wajibAdmin($koneksi);

// URL dasar project
$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

// Penghapusan hanya boleh melalui POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $baseUrl . "/daftar_buku.php");
    exit;
}

// Memeriksa token CSRF
if (!periksaTokenCSRF()) {
    die("Permintaan tidak valid. Silakan kembali dan coba lagi.");
}

// Memvalidasi ID buku
$idBuku = filter_input(INPUT_POST, "id_buku", FILTER_VALIDATE_INT);

if ($idBuku === false || $idBuku === null || $idBuku < 1) {
    die("ID buku tidak valid.");
}

// Mengambil nama sampul dari database
$stmt = $koneksi->prepare(
    "SELECT sampul FROM buku WHERE id_buku = ?"
);

if (!$stmt) {
    error_log("Gagal menyiapkan query pengambilan sampul.");
    die("Terjadi kesalahan saat memproses data buku.");
}

$stmt->bind_param("i", $idBuku);
$stmt->execute();

$hasil = $stmt->get_result();
$buku = $hasil->fetch_assoc();

$stmt->close();

// Memeriksa keberadaan buku
if (!$buku) {
    die("Data buku tidak ditemukan.");
}

// Mengamankan nama file sampul
$namaSampul = basename($buku["sampul"] ?? "");

// Menghapus data buku dari database
$stmt = $koneksi->prepare(
    "DELETE FROM buku WHERE id_buku = ?"
);

if (!$stmt) {
    error_log("Gagal menyiapkan query penghapusan buku.");
    die("Terjadi kesalahan saat menghapus buku.");
}

$stmt->bind_param("i", $idBuku);

try {

    $stmt->execute();

    $jumlahTerhapus = $stmt->affected_rows;

    $stmt->close();

    // Memastikan data benar-benar terhapus
    if ($jumlahTerhapus !== 1) {
        die("Data buku gagal dihapus.");
    }

    // Menghapus file sampul setelah data berhasil dihapus
    $pesan = "hapus_berhasil";

    if ($namaSampul !== "") {

        $pathSampul = __DIR__ . "/uploads/" . $namaSampul;

        if (is_file($pathSampul)) {

            if (!unlink($pathSampul)) {

                error_log(
                    "Gagal menghapus sampul: " . $pathSampul
                );

                $pesan = "hapus_sampul_gagal";
            }
        }
    }

    // Kembali ke daftar buku
    header(
        "Location: " . $baseUrl .
        "/daftar_buku.php?pesan=" . $pesan
    );

    exit;

} catch (Throwable $e) {

    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    error_log(
        "Gagal menghapus buku: " . $e->getMessage()
    );

    die("Data buku gagal dihapus. Silakan periksa kembali.");
}

?>
