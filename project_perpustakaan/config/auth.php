
<?php

// Memulai session dengan pengaturan keamanan.
function mulaiSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name("perpustakaan_session");

        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => isset($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax"
        ]);

        session_start();
    }
}

// Membuat token CSRF.
function buatTokenCSRF()
{
    mulaiSession();

    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

// Memeriksa token CSRF.
function periksaTokenCSRF()
{
    mulaiSession();

    return isset($_POST["csrf_token"], $_SESSION["csrf_token"]) &&
        hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"]);
}

// Menyimpan cookie Remember Me.
function simpanCookieRemember($selector, $validator, $expires)
{
    $nilaiCookie = $selector . ":" . $validator;

    setcookie("perpustakaan_remember", $nilaiCookie, [
        "expires" => $expires,
        "path" => "/",
        "secure" => isset($_SERVER["HTTPS"]) &&
                    $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

// Menghapus cookie Remember Me.
function hapusCookieRemember()
{
    setcookie("perpustakaan_remember", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => isset($_SERVER["HTTPS"]) &&
                    $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

// Membuat token Remember Me baru dan menyimpannya di database.
function buatRememberToken($koneksi, $idUser)
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $validatorHash = hash("sha256", $validator);

    $expires = time() + (30 * 24 * 60 * 60);
    $tanggalKedaluwarsa = date("Y-m-d H:i:s", $expires);

    $stmt = $koneksi->prepare(
        "INSERT INTO remember_tokens
        (id_user, selector, validator_hash, expires_at)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isss",
        $idUser,
        $selector,
        $validatorHash,
        $tanggalKedaluwarsa
    );

    $stmt->execute();
    $stmt->close();

    simpanCookieRemember($selector, $validator, $expires);
}

// Memulihkan login dari cookie Remember Me.
function pulihkanLogin($koneksi)
{
    mulaiSession();

    if (!empty($_SESSION["id_user"])) {
        return;
    }

    if (empty($_COOKIE["perpustakaan_remember"])) {
        return;
    }

    $bagian = explode(":", $_COOKIE["perpustakaan_remember"]);

    if (count($bagian) !== 2) {
        hapusCookieRemember();
        return;
    }

    $selector = $bagian[0];
    $validator = $bagian[1];

    // Memastikan format token sesuai.
    if (
        !preg_match('/^[a-f0-9]{24}$/', $selector) ||
        !preg_match('/^[a-f0-9]{64}$/', $validator)
    ) {
        hapusCookieRemember();
        return;
    }

    $stmt = $koneksi->prepare(
        "SELECT
            rt.id_user,
            rt.validator_hash,
            rt.expires_at,
            u.nama,
            u.username,
            u.role
         FROM remember_tokens rt
         JOIN users u ON rt.id_user = u.id_user
         WHERE rt.selector = ?
         LIMIT 1"
    );

    $stmt->bind_param("s", $selector);
    $stmt->execute();

    $stmt->bind_result(
        $idUser,
        $validatorHash,
        $expiresAt,
        $nama,
        $username,
        $role
    );

    $ditemukan = $stmt->fetch();
    $stmt->close();

    if (!$ditemukan) {
        hapusCookieRemember();
        return;
    }

    $masihBerlaku = strtotime($expiresAt) > time();

    $validatorCocok = hash_equals(
        $validatorHash,
        hash("sha256", $validator)
    );

    if (!$masihBerlaku || !$validatorCocok) {
        $hapus = $koneksi->prepare(
            "DELETE FROM remember_tokens WHERE selector = ?"
        );

        $hapus->bind_param("s", $selector);
        $hapus->execute();
        $hapus->close();

        hapusCookieRemember();
        return;
    }

    // Token lama digunakan satu kali, kemudian diganti.
    $hapus = $koneksi->prepare(
        "DELETE FROM remember_tokens WHERE selector = ?"
    );

    $hapus->bind_param("s", $selector);
    $hapus->execute();
    $hapus->close();

    session_regenerate_id(true);

    $_SESSION["id_user"] = $idUser;
    $_SESSION["nama"] = $nama;
    $_SESSION["username"] = $username;
    $_SESSION["role"] = $role;

    buatRememberToken($koneksi, $idUser);
}

// Memperbarui peran pengguna berdasarkan database.
function perbaruiPeranSession($koneksi)
{
    mulaiSession();

    if (empty($_SESSION["id_user"])) {
        return false;
    }

    $idUser = (int) $_SESSION["id_user"];

    $stmt = $koneksi->prepare(
        "SELECT role FROM users WHERE id_user = ? LIMIT 1"
    );

    $stmt->bind_param("i", $idUser);
    $stmt->execute();

    $stmt->bind_result($role);

    $ditemukan = $stmt->fetch();
    $stmt->close();

    if (!$ditemukan) {
        unset(
            $_SESSION["id_user"],
            $_SESSION["nama"],
            $_SESSION["username"],
            $_SESSION["role"]
        );

        return false;
    }

    $_SESSION["role"] = $role;

    return true;
}

// Memastikan halaman hanya dapat dibuka setelah login.
function wajibLogin($koneksi)
{
    mulaiSession();
    pulihkanLogin($koneksi);

    if (empty($_SESSION["id_user"])) {
        header("Location: index.php?pesan=login_dulu");
        exit;
    }

    // Memastikan peran pengguna sesuai database.
    if (!perbaruiPeranSession($koneksi)) {
        header("Location: index.php?pesan=login_dulu");
        exit;
    }
}

// Memastikan halaman hanya dapat dibuka oleh Admin.
function wajibAdmin($koneksi)
{
    wajibLogin($koneksi);

    if (($_SESSION["role"] ?? "") !== "Admin") {
        header(
            "Location: /web-perpustakaan-maulinda-2a/project_perpustakaan/dashboard.php?pesan=akses_ditolak"
        );
        exit;
    }
}

?>
