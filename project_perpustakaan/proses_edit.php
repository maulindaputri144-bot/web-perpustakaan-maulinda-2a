
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

mulaiSession();

// Hanya Admin yang boleh memproses perubahan buku
wajibAdmin($koneksi);

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

// Hanya menerima permintaan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $baseUrl . "/daftar_buku.php");
    exit;
}

// Memeriksa ukuran seluruh permintaan POST
$batasPost = 3 * 1024 * 1024;

if (
    isset($_SERVER["CONTENT_LENGTH"]) &&
    (int) $_SERVER["CONTENT_LENGTH"] > $batasPost
) {
    die("Ukuran permintaan terlalu besar. Ukuran sampul maksimal 2 MB.");
}

// Memeriksa token CSRF
if (!periksaTokenCSRF()) {
    die("Permintaan tidak valid. Silakan kembali dan coba lagi.");
}

// Memeriksa ID buku
$idBuku = filter_input(INPUT_POST, "id_buku", FILTER_VALIDATE_INT);

if (!$idBuku || $idBuku < 1) {
    die("ID buku tidak valid.");
}

// Mengambil data buku lama
$stmt = $koneksi->prepare(
    "SELECT sampul FROM buku WHERE id_buku = ?"
);

$stmt->bind_param("i", $idBuku);
$stmt->execute();

$hasil = $stmt->get_result();
$bukuLama = $hasil->fetch_assoc();
$stmt->close();

if (!$bukuLama) {
    die("Data buku tidak ditemukan.");
}

// Mengambil data formulir
$judul = trim($_POST["judul_buku"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$penulis = trim($_POST["penulis"] ?? "");

$tahun = filter_var(
    $_POST["tahun_terbit"] ?? "",
    FILTER_VALIDATE_INT
);

$stok = filter_var(
    $_POST["stok"] ?? "",
    FILTER_VALIDATE_INT
);

// Validasi data wajib
if (
    $judul === "" ||
    $kategori === "" ||
    $penulis === "" ||
    !$tahun ||
    $stok === false ||
    $stok === null
) {
    die("Semua data wajib diisi dengan benar.");
}

// Validasi panjang data
if (
    mb_strlen($judul) > 200 ||
    mb_strlen($kategori) > 100 ||
    mb_strlen($penulis) > 100
) {
    die("Panjang salah satu data melebihi batas.");
}

// Validasi tahun terbit
if ($tahun < 1901 || $tahun > 2155) {
    die("Tahun terbit harus antara 1901 sampai 2155.");
}

// Validasi stok
if ($stok < 0) {
    die("Stok tidak boleh kurang dari 0.");
}

// Sampul lama tetap digunakan jika tidak ada sampul baru
$namaSampul = $bukuLama["sampul"];
$fileBaruBerhasil = false;
$pathSampulBaru = null;

$folderUpload = __DIR__ . "/uploads/";

try {

    // Memeriksa folder uploads
    if (
        !is_dir($folderUpload) ||
        !is_writable($folderUpload)
    ) {
        throw new Exception(
            "Folder uploads tidak tersedia atau tidak dapat ditulisi."
        );
    }

    // Memeriksa apakah ada sampul baru
    if (
        isset($_FILES["sampul"]) &&
        $_FILES["sampul"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES["sampul"];

        // Memeriksa kesalahan upload
        if ($file["error"] !== UPLOAD_ERR_OK) {
            if (
                $file["error"] === UPLOAD_ERR_INI_SIZE ||
                $file["error"] === UPLOAD_ERR_FORM_SIZE
            ) {
                throw new Exception("Ukuran sampul maksimal 2 MB.");
            }

            throw new Exception(
                "Terjadi kesalahan saat mengunggah sampul."
            );
        }

        // Memeriksa file upload
        if (
            !isset(
                $file["tmp_name"],
                $file["name"],
                $file["size"]
            ) ||
            !is_uploaded_file($file["tmp_name"])
        ) {
            throw new Exception("File yang diunggah tidak valid.");
        }

        // Memeriksa ukuran file maksimal 2 MB
        $ukuranFile = filesize($file["tmp_name"]);

        if (
            $ukuranFile === false ||
            $ukuranFile > 2 * 1024 * 1024
        ) {
            throw new Exception("Ukuran sampul maksimal 2 MB.");
        }

        // Memeriksa ekstensi file
        $ekstensi = strtolower(
            pathinfo($file["name"], PATHINFO_EXTENSION)
        );

        if (!in_array($ekstensi, ["jpg", "jpeg", "png"], true)) {
            throw new Exception(
                "Sampul harus berformat JPG atau PNG."
            );
        }

        // Memeriksa MIME file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            throw new Exception(
                "Gagal memeriksa jenis file sampul."
            );
        }

        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        if ($mime === false) {
            throw new Exception(
                "Jenis file sampul tidak dapat diperiksa."
            );
        }

        // Memastikan MIME sesuai dengan ekstensi
        $mimeDiizinkan = [
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png"
        ];

        if ($mime !== $mimeDiizinkan[$ekstensi]) {
            throw new Exception(
                "Isi file tidak sesuai dengan format JPG atau PNG."
            );
        }

        // Memeriksa apakah file benar-benar gambar
        $infoGambar = getimagesize($file["tmp_name"]);

        if (
            $infoGambar === false ||
            !in_array(
                $infoGambar["mime"],
                ["image/jpeg", "image/png"],
                true
            )
        ) {
            throw new Exception(
                "File yang diunggah bukan gambar yang valid."
            );
        }

        // Membuat nama file unik
        $namaSampul = bin2hex(random_bytes(16)) . "." . $ekstensi;
        $pathSampulBaru = $folderUpload . $namaSampul;

        // Menyimpan sampul baru
        if (
            !move_uploaded_file(
                $file["tmp_name"],
                $pathSampulBaru
            )
        ) {
            throw new Exception(
                "Sampul gagal disimpan ke folder uploads."
            );
        }

        $fileBaruBerhasil = true;
    }

    // Memperbarui data buku
    // status_ketersediaan dihitung otomatis oleh MySQL
    $stmt = $koneksi->prepare(
        "UPDATE buku
         SET judul_buku = ?,
             kategori = ?,
             penulis = ?,
             tahun_terbit = ?,
             stok = ?,
             sampul = ?
         WHERE id_buku = ?"
    );

    $stmt->bind_param(
        "sssiisi",
        $judul,
        $kategori,
        $penulis,
        $tahun,
        $stok,
        $namaSampul,
        $idBuku
    );

    $stmt->execute();
    $stmt->close();

    // Menghapus sampul lama setelah database berhasil diperbarui
    if ($fileBaruBerhasil) {

        $namaSampulLama = basename($bukuLama["sampul"]);
        $pathSampulLama = $folderUpload . $namaSampulLama;

        if (
            is_file($pathSampulLama) &&
            $namaSampulLama !== $namaSampul
        ) {
            if (!unlink($pathSampulLama)) {
                error_log(
                    "Sampul lama gagal dihapus: " . $pathSampulLama
                );
            }
        }
    }

    // Kembali ke daftar buku
    header(
        "Location: " . $baseUrl .
        "/daftar_buku.php?pesan=edit_berhasil"
    );
    exit;

} catch (Throwable $e) {

    // Menghapus sampul baru jika proses database gagal
    if (
        $fileBaruBerhasil &&
        $pathSampulBaru !== null &&
        is_file($pathSampulBaru)
    ) {
        if (!unlink($pathSampulBaru)) {
            error_log(
                "Sampul baru gagal dibersihkan: " . $pathSampulBaru
            );
        }
    }

    error_log(
        "Gagal mengedit buku: " . $e->getMessage()
    );

    http_response_code(400);
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Buku Gagal</title>
    </head>
    <body>

        <h2>Perubahan Buku Gagal</h2>

        <p>
            <?= htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>

        <a href="<?= $baseUrl ?>/edit_buku.php?id=<?= (int) $idBuku ?>">
            Kembali ke halaman Edit Buku
        </a>

    </body>
    </html>

    <?php
    exit;
}
?>
