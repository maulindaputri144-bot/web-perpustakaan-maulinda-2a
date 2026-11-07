
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mini Digital Library</title>

    <link rel="stylesheet" href="assets/css/launching.css?v=1">
</head>

<body>

    <main class="launch-screen">

        <div class="background-circle circle-one"></div>
        <div class="background-circle circle-two"></div>
        <div class="background-dot dot-one"></div>
        <div class="background-dot dot-two"></div>
        <div class="background-dot dot-three"></div>

        <header class="launch-header">
            <div class="brand-mark">📚</div>
            <span>Mini Digital Library</span>
        </header>

        <section class="launch-content">

            <div class="eyebrow">
                <span class="eyebrow-line"></span>
                A LITTLE SPACE FOR BIG IDEAS
                <span class="eyebrow-line"></span>
            </div>

            <h1>
                Buka Buku,<br>
                <span>Buka Dunia.</span>
            </h1>

            <p class="launch-description">
                Setiap halaman menyimpan cerita.
                Setiap bacaan membuka kesempatan untuk
                menemukan sesuatu yang baru.
            </p>

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

            <div class="launch-bottom">
                <div class="loading-track">
                    <div class="loading-progress"></div>
                </div>

                <p>Menyiapkan ruang bacamu...</p>
            </div>

        </section>

        <footer class="launch-footer">
            <span>READ · LEARN · GROW</span>
            <span>© <span id="tahun"></span> Mini Digital Library</span>
        </footer>

    </main>

    
<script>
    document.getElementById("tahun").textContent =
        new Date().getFullYear();

    // Menandai bahwa launching sudah ditampilkan.
    sessionStorage.setItem("launching_selesai", "true");

    // Setelah 4 detik, lanjut ke halaman login.
    window.setTimeout(function () {
        window.location.href = "index.php";
    }, 4000);
</script>


</body>
</html>
