<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "db_perpustakaan";

try {
    $koneksi = new mysqli($host, $username, $password, $database);
    $koneksi->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Koneksi database gagal: " . $e->getMessage());
    die("Koneksi database gagal. Periksa pengaturan database.");
}
?>
