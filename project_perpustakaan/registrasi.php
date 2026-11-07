
<?php

require_once __DIR__ . "/config/auth.php";

mulaiSession();

$csrfToken = buatTokenCSRF();
$pesan = "";

$daftarPesan = [
    "username_terpakai" => "Username sudah digunakan. Silakan pilih username lain.",
    "data_tidak_valid" => "Data pendaftaran tidak valid. Periksa kembali isianmu.",
    "password_tidak_sama" => "Konfirmasi password tidak sama.",
    "gagal" => "Pendaftaran gagal. Silakan coba kembali."
];

$kodePesan = $_GET["pesan"] ?? "";

if (isset($daftarPesan[$kodePesan])) {
    $pesan = $daftarPesan[$kodePesan];
}

$baseUrl = "/web-perpustakaan-maulinda-2a/project_perpustakaan";

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi | Mini Digital Library</title>

    <!-- Desain utama -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/launching.css?v=1">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/login.css?v=4">

    <!-- Desain khusus registrasi -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/registrasi.css?v=5">
</head>

<body>

    <main class="launch-screen login-screen registration-screen">

        <!-- Dekorasi latar belakang -->
        <div class="background-circle circle-one"></div>
        <div class="background-circle circle-two"></div>

        <div class="background-dot dot-one"></div>
        <div class="background-dot dot-two"></div>
        <div class="background-dot dot-three"></div>

        <!-- Header -->
        <header class="launch-header">
            <div class="brand-mark">M</div>
            <span>Mini Digital Library</span>
        </header>

        <!-- Konten registrasi -->
        <section class="login-content registration-content">

            <!-- Ilustrasi buku dan tanaman -->
            <div class="book-scene registration-scene">

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
                    <div class="open-page page-left"></div>
                    <div class="open-page page-right"></div>
                    <div class="book-center"></div>
                </div>

            </div>

            <!-- Kartu registrasi -->
            <div class="login-card registration-card">

                <div class="login-icon">✦</div>

                <p class="sapaan-login">
                    MULAI PERJALANAN MEMBACA
                </p>

                <h2>Buat Akun</h2>

                <p class="deskripsi-login">
                    Bergabunglah dan temukan berbagai bacaan menarik
                    di Mini Digital Library.
                </p>

                <!-- Pesan pemberitahuan -->
                <?php if ($pesan !== ""): ?>
                    <div class="pesan pesan-error">
                        <?= htmlspecialchars($pesan, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <!-- Form registrasi -->
                <form
                    action="<?= $baseUrl ?>/proses_registrasi.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>"
                    >

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>

                        <div class="input-wrapper">
                            <span class="input-icon">♙</span>

                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                placeholder="Masukkan nama lengkap"
                                autocomplete="name"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>

                        <div class="input-wrapper">
                            <span class="input-icon">＠</span>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Buat username"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>

                        <div class="input-wrapper">
                            <span class="input-icon">♙</span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Buat password"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="konfirmasi_password">
                            Konfirmasi Password
                        </label>

                        <div class="input-wrapper">
                            <span class="input-icon">♙</span>

                            <input
                                type="password"
                                id="konfirmasi_password"
                                name="konfirmasi_password"
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="auth-tombol">
                        Daftar Sekarang
                        <span class="panah-login">→</span>
                    </button>

                </form>

                <div class="pemisah"></div>

                <!-- Kembali ke login -->
                <p class="auth-footer">
                    Sudah memiliki akun?
                    <a href="<?= $baseUrl ?>/index.php">Login di sini</a>
                </p>

            </div>

        </section>

        <!-- Footer -->
        <footer class="launch-footer">
            <span>READ</span> • GROW • DISCOVER
        </footer>

    </main>

</body>
</html>
