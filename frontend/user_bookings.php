<?php
require '../backend/db.php';

// Csak bejelentkezett felhasználók láthatják
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Felhasználó foglalásainak lekérése
$bookings = $pdo->prepare("
    SELECT b.id, g.name AS game_name, b.booking_date, b.created_at, b.status, b.note
    FROM bookings b
    JOIN games g ON b.game_id = g.id
    WHERE b.user_id = ? AND b.status != 'Törölve'
    ORDER BY b.booking_date DESC
");
$bookings->execute([$_SESSION['user_id']]);
$bookings = $bookings->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saját foglalások - UGRÁ-LÓ</title>
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
        <nav>
            <a href="index.php">Vissza</a>
            <a href="user_bookings.php" class="active"><span>📅</span> Saját foglalások</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Foglalások</a>
                <a href="admin_games.php">Játékok kezelése</a>
                <a href="add_game.php">+Új játék</a>
                <a href="users.php">Felhasználók</a>
                <a href="admin_register.php">Admin kezelés/regisztráció</a>
            <?php endif; ?>
            <a href="about_us.php">Rólunk</a>
            <a href="logout.php"><span>🚪</span> Kijelentkezés</a>
        </nav>
    </header>

    <main class="admin-content">
        <h3>Saját foglalások</h3>

        <div class="table-container">
            <table>
                <tbody>
                    <thead>
                        <tr>
                            <th data-label="ID">ID</th>
                            <th data-label="Játék">Játék</th>
                            <th data-label="Dátum">Dátum</th>
                            <th data-label="Foglalás dátuma">Foglalás dátuma</th>
                            <th data-label="Állapot">Állapot</th>
                            <th data-label="Megjegyzés">Megjegyzés</th>
                        </tr>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td data-label="ID"><?= $b['id'] ?></td>
                                <td data-label="Játék"><?= htmlspecialchars($b['game_name']) ?></td>
                                <td data-label="Dátum"><?= htmlspecialchars($b['created_at']) ?></td>
                                <td data-label="Foglalás dátuma"><?= htmlspecialchars($b['booking_date']) ?></td>
                                <td data-label="Állapot">
                                    <span class="status-badge status-<?= strtolower($b['status']) ?>">
                                        <?= htmlspecialchars($b['status']) ?>
                                    </span>
                                </td>
                                <td data-label="Megjegyzés"><?= htmlspecialchars($b['note']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="no-bookings">Nincsenek foglalások</td>
                            </tr>
                        <?php endif; ?>
                    </thead>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>