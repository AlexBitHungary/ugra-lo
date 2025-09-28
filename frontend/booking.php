<?php
require '../backend/db.php';

// Ha nincs bejelentkezve, vissza a loginra
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$game_id = $_GET['game_id'] ?? null;
if (!$game_id) {
    die("Hiányzó játék ID!");
}

// Lekérjük a játék adatait
$game = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$game->execute([$game_id]);
$game = $game->fetch();
if (!$game) {
    die("A játék nem található!");
}

$error = '';
$success = '';

// Ha POST-tal jött adat, feldolgozzuk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $note = $_POST['note'] ?? '';

    if (empty($booking_date)) {
        $error = "Kérlek, adj meg egy dátumot!";
    } else {
        // Ellenőrizzük, hogy a dátum a jövőben van-e
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            $error = "Csak jövőbeli dátumot lehet megadni!";
        } else {
            // Duplikált foglalás ellenőrzése
            $check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE game_id = ? AND booking_date = ?");
            $check->execute([$game_id, $booking_date]);
            if ($check->fetchColumn() > 0) {
                $error = "Erre a napra már van foglalás ennél a játéknál!";
            } else {
                // Foglalás mentése
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, game_id, booking_date, note) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $game_id, $booking_date, $note]);
                $success = "Sikeres foglalás!";
            }
        }
    }
}

// Mai dátum formátumban
$min_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás - <?php echo htmlspecialchars($game['name']); ?></title>
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
    <nav>
        <a href="index.php">Vissza</a>
        <a href="user_bookings.php"><span>📅</span> Saját foglalások</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php">Foglalások</a>
            <a href="admin_games.php">Játékok kezelése</a>
            <a href="add_game.php">+Új játék</a>
            <a href="users.php">Felhasználók</a>
        <?php endif; ?>
        <a href="about_us.php">Rólunk</a>
        <a href="../backend/logout.php"><span>🚪</span> Kijelentkezés</a>
    </nav>
    </header>

    <main class="admin-content">
        <h3><?php echo htmlspecialchars($game['name']); ?> foglalása</h3>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" class="data-form">
            <div class="form-group">
                <label for="booking_date">Dátum*</label>
                <input type="date" id="booking_date" name="booking_date" required
                    min="<?php echo $min_date; ?>">
            </div>

            <div class="form-group">
                <label for="note">Megjegyzés</label>
                <textarea id="note" name="note" rows="4"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Foglalás</button>
            </div>
        </form>
    </main>

    <script>
        // Ügyféloldali ellenőrzés - csak a form elküldésekor
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('booking_date');
            const today = new Date().toISOString().split('T')[0];

            dateInput.setAttribute('min', today);

            // Form elküldésekor ellenőrzés
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (dateInput.value < today) {
                    e.preventDefault();
                    alert('Csak jövőbeli dátumot lehet megadni!');
                    dateInput.value = today;
                    dateInput.focus();
                }
            });
        });
    </script>
</body>

</html>