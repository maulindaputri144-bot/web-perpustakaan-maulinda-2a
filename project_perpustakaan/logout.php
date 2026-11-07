<?php
require_once "config/koneksi.php";
require_once "config/auth.php";

mulaiSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (!periksaTokenCSRF()) {
    die("Permintaan logout tidak valid.");
}

// Menghapus token Remember Me dari database.
if (!empty($_COOKIE["perpustakaan_remember"])) {
    $bagian = explode(":", $_COOKIE["perpustakaan_remember"]);

    if (
        count($bagian) === 2 &&
        preg_match('/^[a-f0-9]{24}$/', $bagian[0])
    ) {
        $selector = $bagian[0];

        $stmt = $koneksi->prepare(
            "DELETE FROM remember_tokens WHERE selector = ?"
        );

        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
}

// Menghapus cookie Remember Me.
hapusCookieRemember();

// Menghapus seluruh data session.
$_SESSION = [];

// Menghapus cookie session dari browser.
if (ini_get("session.use_cookies")) {
    $parameter = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $parameter["path"],
        "domain" => $parameter["domain"],
        "secure" => $parameter["secure"],
        "httponly" => $parameter["httponly"],
        "samesite" => "Lax"
    ]);
}

session_destroy();

header("Location: index.php?pesan=logout");
exit;
?>
