<?php
require '../backend/db.php';

// Csak admin láthatja
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Üzenetek megjelenítése (ezek továbbra is jöhetnek más oldalakról, pl. add_game.php)
$message = $_SESSION['message'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['message'], $_SESSION['error']);

// Foglalások lekérése (nem töröltek)
$bookings = $pdo->query("
    SELECT b.id, g.name AS game_name, u.username, b.created_at,b.booking_date, b.status, b.note
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN games g ON b.game_id = g.id
    WHERE b.status != 'Törölve'
    ORDER BY b.booking_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Foglalások</title>
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
            <a href="user_bookings.php"><span>📅</span> Saját foglalások</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="active">Foglalások</a>
                <a href="admin_games.php">Játékok kezelése</a>
                <a href="add_game.php">+Új játék</a>
                <a href="users.php">Felhasználók</a>
                <a href="admin_register.php">Admin kezelés/regisztráció</a>
            <?php endif; ?>
            <a href="about_us.php">Rólunk</a>
            <a href="../backend/logout.php"><span>🚪</span> Kijelentkezés</a>
        </nav>
    </header>

    <main class="admin-content">
        <h3>Foglalások kezelése</h3>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div id="response-message-container">
            <!-- Ide kerülnek az AJAX válaszok üzenetei -->
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tbody>
                    <tr>
                        <th data-label="ID">ID</th>
                        <th data-label="Felhasználó">Felhasználó</th>
                        <th data-label="Játék">Játék</th>
                        <th data-label="Dátum">Dátum</th>
                        <th data-label="Foglalás ideje">Foglalás ideje</th>
                        <th data-label="Megjegyzés">Megjegyzés</th>
                        <th data-label="Állapot">Állapot</th>
                        <th data-label="Műveletek">Műveletek</th>
                    </tr>

                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr id="booking-row-<?= $b['id'] ?>">
                                <td data-label="ID"><?= $b['id'] ?></td>
                                <td data-label="Felhasználó"><?= htmlspecialchars($b['username']) ?></td>
                                <td data-label="Játék"><?= htmlspecialchars($b['game_name']) ?></td>
                                <td data-label="Dátum"><?= htmlspecialchars($b['created_at']) ?></td>
                                <td data-label="Foglalás ideje"><?= htmlspecialchars($b['booking_date']) ?></td>
                                <td data-label="Megjegyzés"><?= htmlspecialchars($b['note']) ?></td>
                                <td data-label="Állapot">
                                    <div class="inline-form">
                                        <select name="status" class="status-select" data-booking-id="<?= $b['id'] ?>">
                                            <option value="Függőben" <?= $b['status'] === 'Függőben' ? 'selected' : '' ?>>Függőben</option>
                                            <option value="Jóváhagyva" <?= $b['status'] === 'Jóváhagyva' ? 'selected' : '' ?>>Jóváhagyva</option>
                                            <option value="Elutasítva" <?= $b['status'] === 'Elutasítva' ? 'selected' : '' ?>>Elutasítva</option>
                                        </select>
                                        <button type="button" class="btn btn-status btn-sm update-status-btn" data-booking-id="<?= $b['id'] ?>">Mentés</button>
                                    </div>
                                </td>
                                <td data-label="Műveletek">
                                    <button type="button" class="btn btn-delete btn-sm delete-booking-btn" data-booking-id="<?= $b['id'] ?>">Törlés</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-bookings">Nincsenek foglalások</td>
                        </tr>
                    <?php endif; ?>
                    </thead>
                </tbody>
            </table>
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

        // Státusz frissítése AJAX-szal
        document.querySelectorAll('.update-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                const selectElement = document.querySelector(`.status-select[data-booking-id="${bookingId}"]`);
                const newStatus = selectElement.value;

                // Betöltési állapot jelzése
                const originalText = this.textContent;
                this.textContent = "Folyamatban...";
                this.disabled = true;

                fetch(`api/bookings.php/${bookingId}`, { // RESTful URL: /api/bookings/{id}
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
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
                        } else {
                            displayMessage('error', data.error);
                            // Visszaállítjuk a régi értéket, ha hiba történt
                            selectElement.value = document.querySelector(`.status-select[data-booking-id="${bookingId}"] option[selected]`).value;
                        }
                    })
                    .catch(error => {
                        console.error('Hiba:', error);
                        displayMessage('error', 'Hiba történt a státusz frissítésekor.');
                    })
                    .finally(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                    });
            });
        });

        // Foglalás törlése AJAX-szal
        document.querySelectorAll('.delete-booking-btn').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                if (!confirm('Biztos törölni akarod ezt a foglalást?')) {
                    return;
                }

                // Betöltési állapot jelzése
                const originalText = this.textContent;
                this.textContent = "Törlés...";
                this.disabled = true;

                fetch(`api/bookings.php/${bookingId}`, { // RESTful URL: /api/bookings/{id}
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
                            // Eltávolítjuk a sort a táblázatból
                            const row = document.getElementById(`booking-row-${bookingId}`);
                            if (row) {
                                row.remove();
                            }
                        } else {
                            displayMessage('error', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Hiba:', error);
                        displayMessage('error', 'Hiba történt a foglalás törlésekor.');
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