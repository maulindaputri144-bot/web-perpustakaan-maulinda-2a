
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

// Memulai session dan memeriksa hak akses Admin
mulaiSession();
wajibAdmin($koneksi);

// Fungsi untuk menampilkan pesan kesalahan
function tampilkanError($pesan)
{
    $pesanAman = htmlspecialchars($pesan, ENT_QUOTES, "UTF-8");

    echo "<!DOCTYPE html>";
    echo "<html lang='id'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Proses Tambah Buku</title>";
    echo "</head>";
    echo "<body>";
    echo "<h2>Tambah Buku Gagal</h2>";
    echo "<p>{$pesanAman}</p>";
    echo "<a href='/web-perpustakaan-maulinda-2a/project_perpustakaan/tambah_buku.php'>Kembali ke Form Tambah Buku</a>";
    echo "</body>";
    echo "</html>";

    exit;
}

// Hanya menerima permintaan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    tampilkanError("Akses tidak diperbolehkan.");
}

// Memeriksa ukuran seluruh permintaan POST
$batasPost = 3 * 1024 * 1024;

if (
    isset($_SERVER["CONTENT_LENGTH"]) &&
    (int) $_SERVER["CONTENT_LENGTH"] > $batasPost
) {
    tampilkanError(
        "Ukuran permintaan terlalu besar. Ukuran sampul maksimal 2 MB."
    );
}

// Memeriksa token CSRF
if (!periksaTokenCSRF()) {
    tampilkanError(
        "Permintaan tidak valid. Silakan muat ulang halaman dan coba kembali."
    );
}

// Mengambil dan membersihkan data formulir
$judul = trim($_POST["judul_buku"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$penulis = trim($_POST["penulis"] ?? "");
$tahun = $_POST["tahun_terbit"] ?? "";
$stokInput = $_POST["stok"] ?? "";

// Memeriksa input wajib
if (
    $judul === "" ||
    $kategori === "" ||
    $penulis === "" ||
    $tahun === "" ||
    $stokInput === ""
) {
    tampilkanError("Semua kolom wajib diisi.");
}

// Memeriksa panjang input
if (
    mb_strlen($judul) > 200 ||
    mb_strlen($kategori) > 100 ||
    mb_strlen($penulis) > 100
) {
    tampilkanError("Panjang data melebihi batas yang diperbolehkan.");
}

// Memeriksa tahun terbit
$tahunValid = filter_var($tahun, FILTER_VALIDATE_INT);

if (
    $tahunValid === false ||
    $tahunValid < 1901 ||
    $tahunValid > 2155
) {
    tampilkanError(
        "Tahun terbit harus berupa angka antara 1901 sampai 2155."
    );
}

// Memeriksa jumlah stok
$stokValid = filter_var($stokInput, FILTER_VALIDATE_INT);

if ($stokValid === false || $stokValid < 0) {
    tampilkanError(
        "Jumlah stok harus berupa bilangan bulat dan tidak boleh negatif."
    );
}

// Memeriksa ID pengguna yang sedang login
$idUser = filter_var(
    $_SESSION["id_user"] ?? null,
    FILTER_VALIDATE_INT
);

if ($idUser === false || $idUser < 1) {
    tampilkanError(
        "Data pengguna tidak valid. Silakan login kembali."
    );
}

// Memeriksa apakah file sampul dikirim
if (!isset($_FILES["sampul"])) {
    tampilkanError("Sampul buku wajib diunggah.");
}

$file = $_FILES["sampul"];

// Memeriksa kesalahan upload
switch ($file["error"]) {
    case UPLOAD_ERR_OK:
        break;

    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        tampilkanError("Ukuran sampul maksimal adalah 2 MB.");
        break;

    case UPLOAD_ERR_PARTIAL:
        tampilkanError(
            "Upload sampul tidak selesai. Silakan coba kembali."
        );
        break;

    case UPLOAD_ERR_NO_FILE:
        tampilkanError("Sampul buku wajib diunggah.");
        break;

    default:
        tampilkanError(
            "Terjadi kesalahan saat mengunggah sampul buku."
        );
        break;
}

// Memastikan file benar-benar diunggah
if (
    !isset($file["tmp_name"]) ||
    !is_uploaded_file($file["tmp_name"])
) {
    tampilkanError("File yang dikirim tidak valid.");
}

// Memeriksa ukuran file maksimal 2 MB
$batasUkuran = 2 * 1024 * 1024;

if ($file["size"] > $batasUkuran) {
    tampilkanError("Ukuran sampul maksimal adalah 2 MB.");
}

// Memeriksa ekstensi file
$ekstensi = strtolower(
    pathinfo($file["name"], PATHINFO_EXTENSION)
);

$daftarEkstensi = ["jpg", "jpeg", "png"];

if (!in_array($ekstensi, $daftarEkstensi, true)) {
    tampilkanError("Format sampul harus JPG atau PNG.");
}

// Memeriksa MIME menggunakan Fileinfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);

if ($finfo === false) {
    tampilkanError("Tidak dapat memeriksa tipe file.");
}

$mime = finfo_file($finfo, $file["tmp_name"]);
finfo_close($finfo);

if ($mime === false) {
    tampilkanError("Jenis file sampul tidak dapat diperiksa.");
}

$daftarMime = [
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png"
];

if (
    !isset($daftarMime[$ekstensi]) ||
    $mime !== $daftarMime[$ekstensi]
) {
    tampilkanError(
        "Isi file tidak sesuai dengan format gambar yang diperbolehkan."
    );
}

// Memastikan file benar-benar merupakan gambar
$informasiGambar = getimagesize($file["tmp_name"]);

if (
    $informasiGambar === false ||
    !in_array(
        $informasiGambar["mime"],
        ["image/jpeg", "image/png"],
        true
    )
) {
    tampilkanError(
        "File yang diunggah bukan gambar JPG atau PNG yang valid."
    );
}

// Menyiapkan folder uploads
$folderUpload = __DIR__ . "/uploads/";

if (
    !is_dir($folderUpload) ||
    !is_writable($folderUpload)
) {
    tampilkanError(
        "Folder uploads tidak ditemukan atau tidak dapat ditulisi."
    );
}

// Membuat nama file unik
$ekstensiSimpan = ($ekstensi === "jpeg") ? "jpg" : $ekstensi;

try {
    $namaSampul = bin2hex(random_bytes(16)) . "." . $ekstensiSimpan;
} catch (Throwable $e) {
    error_log("Gagal membuat nama file: " . $e->getMessage());
    tampilkanError("Gagal membuat nama file sampul.");
}

$lokasiSampul = $folderUpload . $namaSampul;

// Memindahkan sampul ke folder uploads
if (!move_uploaded_file($file["tmp_name"], $lokasiSampul)) {
    tampilkanError(
        "Gagal menyimpan sampul ke folder uploads."
    );
}

// Menyimpan data buku menggunakan prepared statement
$sql = "INSERT INTO buku
        (judul_buku, kategori, penulis, tahun_terbit, stok, sampul, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $koneksi->prepare($sql);

    $stmt->bind_param(
        "sssiisi",
        $judul,
        $kategori,
        $penulis,
        $tahunValid,
        $stokValid,
        $namaSampul,
        $idUser
    );

    $stmt->execute();
    $stmt->close();

} catch (Throwable $e) {

    // Menghapus sampul jika penyimpanan database gagal
    if (is_file($lokasiSampul)) {
        unlink($lokasiSampul);
    }

    error_log(
        "Kesalahan tambah buku: " . $e->getMessage()
    );

    tampilkanError(
        "Data buku gagal disimpan. Silakan periksa database dan coba kembali."
    );
}

// Jika berhasil, kembali ke halaman daftar buku
header(
    "Location: /web-perpustakaan-maulinda-2a/project_perpustakaan/daftar_buku.php?pesan=tambah_berhasil"
);

exit;

?>
