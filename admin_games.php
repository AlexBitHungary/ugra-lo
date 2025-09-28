<?php
require 'db.php';

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

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$games = $pdo->query("SELECT * FROM games")->fetchAll();

?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Játékok kezelése - UGRÁ-LÓ</title>
    <link rel="stylesheet" href="styles.css">
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
        <nav>
            <a href="index.php">Vissza</a>
            <a href="user_bookings.php"><span>📅</span> Saját foglalások</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Foglalások</a>
                <a href="admin_games.php" class="active">Játékok kezelése</a>
                <a href="add_game.php">+Új játék</a>
                <a href="users.php">Felhasználók</a>
                <a href="admin_register.php">Admin kezelés/regisztráció</a>
            <?php endif; ?>
            <a href="about_us.php">Rólunk</a>
            <a href="logout.php"><span>🚪</span> Kijelentkezés</a>
        </nav>
    </header>

    <main>
        <div id="response-message-container">
            <!-- Ide kerülnek az AJAX válaszok üzenetei -->
        </div>

        <div class="games-container">
            <?php foreach ($games as $game): ?>
                <?php
                $priceHuf = $game['price'];
                $priceEur = $priceHuf / $eurToHufRate;
                ?>
                <div class="game-card admin-card" id="game-card-<?= $game['id'] ?>">
                    <div class="game-image">
                        <?php if ($game['image_url']): ?>
                            <img src="<?= htmlspecialchars($game['image_url']) ?>" alt="<?= htmlspecialchars($game['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">Nincs kép</div>
                        <?php endif; ?>
                    </div>
                    <div class="game-details">
                        <h4><?php echo htmlspecialchars($game['name']); ?></h4>
                        <p class="price"><?php echo number_format($priceHuf, 0, ',', ' '); ?> Ft</p>
                        <p class="price-eur">(Kb. <?php echo number_format($priceEur, 2, ',', ' '); ?> €)</p>
                        <div class="admin-actions">
                            <a href="edit_game.php?id=<?= $game['id'] ?>" class="btn btn-sm btn-edit">Szerkesztés</a>
                            <button type="button" class="btn btn-sm btn-delete delete-game-btn"
                                data-game-id="<?= $game['id'] ?>">Törlés</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <script>
        // Üzenet megjelenítő funkció
        function displayMessage(type, message) {
            const container = document.getElementById('response-message-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            container.innerHTML = ''; // Töröljük az előző üzeneteket
            container.appendChild(alertDiv);

            // Üzenet eltüntetése 5 másodperc után
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Játék törlése AJAX-szal
        document.querySelectorAll('.delete-game-btn').forEach(button => {
            button.addEventListener('click', function() {
                const gameId = this.dataset.gameId;
                if (!confirm('Biztos törölni akarod ezt a játékot?')) {
                    return;
                }

                // Betöltési állapot jelzése
                const originalText = this.textContent;
                this.textContent = "Törlés...";
                this.disabled = true;

                fetch(`api/games.php/${gameId}`, { // RESTful URL: /api/games/{id}
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Hálózati hiba történt');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            displayMessage('success', data.success);
                            // Eltávolítjuk a kártyát a DOM-ból
                            const gameCard = document.getElementById(`game-card-${gameId}`);
                            if (gameCard) {
                                gameCard.remove();
                            }
                        } else {
                            displayMessage('error', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Hiba:', error);
                        displayMessage('error', 'Hiba történt a játék törlésekor.');
                    })
                    .finally(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                    });
            });
        });
    </script>
</body>

</html>