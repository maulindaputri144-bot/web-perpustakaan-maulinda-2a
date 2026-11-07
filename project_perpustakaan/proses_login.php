<?php
require_once "config/koneksi.php";
require_once "config/auth.php";

mulaiSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (!periksaTokenCSRF()) {
    die("Permintaan tidak valid. Silakan kembali ke halaman login.");
}

// Mengambil dan memeriksa input.
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if (
    $username === "" ||
    $password === "" ||
    strlen($username) > 50
) {
    header("Location: index.php?pesan=login_gagal");
    exit;
}

// Mencari akun berdasarkan username.
$stmt = $koneksi->prepare(
    "SELECT id_user, nama, username, password
     FROM users
     WHERE username = ?
     LIMIT 1"
);

$stmt->bind_param("s", $username);
$stmt->execute();

$stmt->bind_result(
    $idUser,
    $nama,
    $usernameDatabase,
    $passwordHash
);

$akunDitemukan = $stmt->fetch();
$stmt->close();

// Memeriksa password yang dimasukkan.
if (!$akunDitemukan || !password_verify($password, $passwordHash)) {
    header("Location: index.php?pesan=login_gagal");
    exit;
}

// Login berhasil.
session_regenerate_id(true);

$_SESSION["id_user"] = $idUser;
$_SESSION["nama"] = $nama;
$_SESSION["username"] = $usernameDatabase;

// Membuat cookie jika Remember Me dipilih.
if (isset($_POST["remember_me"]) && $_POST["remember_me"] === "1") {
    buatRememberToken($koneksi, $idUser);
}

header("Location: dashboard.php");
exit;
?>
