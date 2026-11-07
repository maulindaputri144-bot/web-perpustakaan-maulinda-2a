

<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/auth.php";

mulaiSession();
pulihkanLogin($koneksi);

if (isset($_SESSION["id_user"])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = $_GET["pesan"] ?? "";
$pesanTampilan = "";
$jenisPesan = "";

switch ($pesan) {
    case "logout":
        $pesanTampilan = "Anda berhasil logout.";
        $jenisPesan = "pesan-sukses";
        break;

    case "akses_ditolak":
        $pesanTampilan = "Anda tidak memiliki izin untuk mengakses halaman tersebut.";
        $jenisPesan = "pesan-error";
        break;

    case "login_dulu":
        $pesanTampilan = "Silakan login terlebih dahulu.";
        $jenisPesan = "pesan-info";
        break;

    case "login_gagal":
        $pesanTampilan = "Username atau password salah.";
        $jenisPesan = "pesan-error";
        break;
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Mini Digital Library</title>

    <link rel="stylesheet" href="assets/css/launching.css?v=1">
    <link rel="stylesheet" href="assets/css/login.css?v=4">
</head>

<script>
    // Jika launching belum ditampilkan, arahkan ke launching.php.
    if (sessionStorage.getItem("launching_selesai") !== "true") {
        window.location.replace("launching.php");
    }
</script>


<body>

<main class="launch-screen login-screen">

    <!-- Dekorasi latar launching -->
    <div class="background-circle circle-one"></div>
    <div class="background-circle circle-two"></div>

    <div class="background-dot dot-one"></div>
    <div class="background-dot dot-two"></div>
    <div class="background-dot dot-three"></div>

    <!-- Header -->
    <header class="launch-header">
        <div class="brand-mark">📚</div>
        <span>Mini Digital Library</span>
    </header>

    <!-- Ilustrasi dan kartu login -->
    <section class="launch-content login-content">

        <div class="book-scene">

            <div class="scene-glow"></div>

            <div class="floating-star star-one">✦</div>
            <div class="floating-star star-two">✧</div>
            <div class="floating-star star-three">✦</div>

            <div class="plant">
                <div class="plant-stem"></div>
                <div class="plant-leaf leaf-one"></div>
                <div class="plant-leaf leaf-two"></div>
                <div class="plant-leaf leaf-three"></div>
                <div class="plant-leaf leaf-four"></div>
                <div class="plant-leaf leaf-five"></div>
                <div class="plant-pot"></div>
            </div>

            <div class="book-stack">
                <div class="stack-book stack-one"></div>
                <div class="stack-book stack-two"></div>
                <div class="stack-book stack-three"></div>
            </div>

            <div class="open-book">
                <div class="open-page page-left">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="book-center"></div>

                <div class="open-page page-right">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

        </div>

        <!-- Kartu login -->
        <div class="login-card">

            <div class="login-icon">📚</div>

            <span class="sapaan-login">WELCOME BACK!</span>

            <h2>Masuk ke Akunmu</h2>

            <p class="deskripsi-login">
                Silakan login untuk melanjutkan aktivitas membaca.
            </p>

            <?php if ($pesanTampilan !== ""): ?>
                <div class="pesan <?= htmlspecialchars($jenisPesan, ENT_QUOTES, "UTF-8"); ?>">
                    <?= htmlspecialchars($pesanTampilan, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(buatTokenCSRF(), ENT_QUOTES, "UTF-8"); ?>"
                >

                <div class="form-group">
                    <label for="username">Username</label>

                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <div class="auth-checkbox">
                    <label>
                        <input
                            type="checkbox"
                            name="remember_me"
                            value="1"
                        >
                        <span>Remember Me</span>
                    </label>
                </div>

                <button type="submit" class="auth-tombol">
                    Login
                    <span class="panah-login">→</span>
                </button>

            </form>

            <div class="pemisah">
                <span>atau</span>
            </div>

            <div class="auth-footer">
                Belum memiliki akun?
                <a href="registrasi.php">Daftar di sini</a>
            </div>

            <div class="login-dekorasi-bawah">
                Made for book lovers ♡
            </div>

        </div>

    </section>

    <!-- Footer -->
    <footer class="launch-footer">
        <span>READ · LEARN · GROW</span>
        <span>© <?= date("Y"); ?> Mini Digital Library</span>
    </footer>

</main>

</body>
</html>
