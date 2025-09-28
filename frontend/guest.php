<?php
require '../backend/db.php';

// Árfolyam lekérése EUR -> HUF
$exchangeApiUrl = "https://v6.exchangerate-api.com/v6/4a6ae1726d915471cc92a2b3/latest/EUR";
$exchangeDataJson = file_get_contents($exchangeApiUrl);
$exchangeData = json_decode($exchangeDataJson, true);

$eurToHufRate = 0;
if ($exchangeData && $exchangeData['result'] === 'success' && isset($exchangeData['conversion_rates']['HUF'])) {
    $eurToHufRate = $exchangeData['conversion_rates']['HUF'];
} else {
    // Ha nem sikerül lekérni, alapértelmezett árfolyam
    $eurToHufRate = 370; // pl. 1 EUR = 370 HUF
}

$games = $pdo->query("SELECT * FROM games")->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Főoldal - Ugráló</title>
    <link rel="stylesheet" href="../frontend/styles.css">
</head>

<body>
    <div class="background">
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
    </div>
    <header>
        <div class="logo-container">
            <img src="../frontend/img/logo.png" class="logo" alt="Ugráló Logó">
        </div>
        <div class="header-content">
            <h1>UGRÁ-LÓ</h1>
            <h2>Üdvözlünk, kedves látogató!</h2>
            <h2 class="guest_alert">A foglaláshoz bejelentkezés vagy regisztráció szükséges!</h2>
            <nav>
                <a href="login.php"><span>🚪</span> Bejelentkezés</a>
                <a href="register.php">Regisztráció</a>
                <a href="about_us_guest.php">Rólunk</a>
            </nav>
        </div>
    </header>

    <main>
        <h3>Bérelhető játékok</h3>
        <div class="games-container">
            <?php foreach ($games as $game): ?>
                <?php
                $priceHuf = $game['price'];
                $priceEur = $priceHuf / $eurToHufRate;
                ?>
                <div class="game-card">
                    <div class="game-image">
                        <?php if ($game['image_url']): ?>
                            <img src="<?= htmlspecialchars($game['image_url']) ?>" alt="<?= htmlspecialchars($game['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">Nincs kép</div>
                        <?php endif; ?>
                    </div>
                    <div class="game-details">
                        <h4><?php echo htmlspecialchars($game['name']); ?></h4>
                        <p class="description"><?php echo htmlspecialchars($game['description']); ?></p>
                        <p class="price"><?php echo number_format($priceHuf, 0, ',', ' '); ?> Ft</p>
                        <p class="price-eur">(Kb. <?php echo number_format($priceEur, 2, ',', ' '); ?> €)</p>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <!-- Süti figyelmeztetés -->
    <div id="cookie-consent" style="display: none;">
        <p>Weboldalunk sütiket használ a jobb felhasználói élmény érdekében.
            <a href="javascript:void(0)" onclick="showCookieInfo()">További információ</a>
        </p>
        <div id="cookie-consent-buttons">
            <button class="btn-accept" onclick="acceptCookies()">Elfogadom</button>
            <button class="btn-reject" onclick="rejectCookies()">Elutasítom</button>
        </div>
    </div>

    <script>
        // Süti kezelés
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${value};${expires};path=/`;
        }

        function showCookieConsent() {
            if (!getCookie('cookie_consent')) {
                document.getElementById('cookie-consent').style.display = 'flex';
            }
        }

        function acceptCookies() {
            setCookie('cookie_consent', 'accepted', 365);
            document.getElementById('cookie-consent').style.display = 'none';
            // Itt lehet további sütiket beállítani, ha szükséges
        }

        function rejectCookies() {
            setCookie('cookie_consent', 'rejected', 30);
            document.getElementById('cookie-consent').style.display = 'none';
            // Itt lehet tilalmi sütiket beállítani, ha szükséges
        }

        function showCookieInfo() {
            alert('Süti (cookie) tájékoztató:\n\nA sütik kis szöveges fájlok, amelyeket a weboldal helyez el a böngészőjében, hogy emlékezzen a beállításaira és preferenciáira.\n\nAz általunk használt sütik:\n- Munkamenet kezelés: bejelentkezési adatok tárolása\n- Preferenciák: nyelvi beállítások, téma választás\n- Analitika: oldalletöltések és forgalom elemzése');
        }

        // Oldal betöltésekor ellenőrizzük a süti állapotát
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(showCookieConsent, 1000);
        });
    </script>

</body>

</html>