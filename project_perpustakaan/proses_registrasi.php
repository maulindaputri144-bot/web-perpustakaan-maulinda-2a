
<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

mulaiSession();

// Registrasi hanya menerima data dari formulir POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registrasi.php");
    exit;
}

// Memeriksa token keamanan formulir.
if (!periksaTokenCSRF()) {
    die("Permintaan tidak valid. Silakan buka kembali halaman registrasi.");
}

// Mengambil data dari formulir.
$nama = trim($_POST["nama"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$konfirmasiPassword = $_POST["konfirmasi_password"] ?? "";

// Memeriksa apakah semua kolom sudah diisi.
if (
    $nama === "" ||
    $username === "" ||
    $password === "" ||
    $konfirmasiPassword === ""
) {
    header("Location: registrasi.php?pesan=data_tidak_valid");
    exit;
}

// Memeriksa panjang nama dan username.
if (
    strlen($nama) > 100 ||
    !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)
) {
    header("Location: registrasi.php?pesan=data_tidak_valid");
    exit;
}

// Memeriksa panjang password.
if (strlen($password) < 8) {
    header("Location: registrasi.php?pesan=data_tidak_valid");
    exit;
}

// Memeriksa kesamaan password dan konfirmasi.
if ($password !== $konfirmasiPassword) {
    header("Location: registrasi.php?pesan=password_tidak_sama");
    exit;
}

// Memeriksa apakah username sudah digunakan.
$stmt = $koneksi->prepare(
    "SELECT id_user FROM users WHERE username = ? LIMIT 1"
);

if (!$stmt) {
    error_log("Gagal menyiapkan pemeriksaan username.");
    header("Location: registrasi.php?pesan=gagal");
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

$usernameAda = $stmt->num_rows > 0;

$stmt->close();

if ($usernameAda) {
    header("Location: registrasi.php?pesan=username_terpakai");
    exit;
}

// Mengubah password menjadi hash sebelum disimpan.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    header("Location: registrasi.php?pesan=gagal");
    exit;
}

// Menyimpan akun baru dengan peran Anggota.
try {

    $stmt = $koneksi->prepare(
        "INSERT INTO users (nama, username, password, role)
         VALUES (?, ?, ?, 'Anggota')"
    );

    if (!$stmt) {
        throw new mysqli_sql_exception(
            "Gagal menyiapkan proses penyimpanan akun."
        );
    }

    $stmt->bind_param(
        "sss",
        $nama,
        $username,
        $passwordHash
    );

    $stmt->execute();
    $stmt->close();

    // Registrasi berhasil, kembali ke halaman login.
    header("Location: index.php?pesan=registrasi_berhasil");
    exit;

} catch (mysqli_sql_exception $e) {

    error_log("Registrasi gagal: " . $e->getMessage());

    header("Location: registrasi.php?pesan=gagal");
    exit;
}

?>
