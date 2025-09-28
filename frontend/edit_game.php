<?php
require '../backend/db.php';
require '../backend/utils/ImageUpload.php'; // EZ AZ ÚJ SOR

// Csak admin férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

/**
 * Képfeltöltést kezelő funkció.
 *
 * @param array $file A $_FILES['fajlnev'] tömb.
 * @return array Asszociatív tömb 'image_url' kulccsal siker esetén, vagy 'error' kulccsal hiba esetén.
 */

// Játék adatainak lekérése szerkesztéshez
$game_id = (int) ($_GET['id'] ?? 0);
$game = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$game->execute([$game_id]);
$game_data = $game->fetch();

if (!$game_data) {
    $_SESSION['error'] = "A játék nem található!";
    header('Location: admin_games.php');
    exit;
}

// Űrlapfeldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $current_image = $game_data['image_url'];
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] === 'on';

    // Validáció
    if (empty($name) || empty($description) || $price <= 0) {
        $_SESSION['error'] = "Kérjük töltsön ki minden kötelező mezőt érvényes adatokkal!";
        header("Location: edit_game.php?id=$game_id");
        exit;
    }

    try {
        $image_url = $current_image;

        // 1. eset: Kép eltávolítása
        if ($remove_image) {
            if ($current_image && file_exists($current_image)) {
                unlink($current_image);
            }
            $image_url = null;
        }
        // 2. eset: Új kép feltöltése
        elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = handleImageUpload($_FILES['image']);
            if (isset($upload['error'])) {
                throw new Exception($upload['error']);
            }
            // Régi kép törlése, ha volt
            if ($current_image && file_exists($current_image)) {
                unlink($current_image);
            }
            $image_url = $upload['image_url'];
        }

        // Játék frissítése az adatbázisban
        $stmt = $pdo->prepare("UPDATE games SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $image_url, $game_id]);

        $_SESSION['message'] = "A játék sikeresen frissítve!";
        header('Location: admin_games.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Hiba: " . $e->getMessage();
        error_log("Szerkesztési hiba: " . $e->getMessage());
        header("Location: edit_game.php?id=$game_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Játék szerkesztése - UGRÁ-LÓ</title>
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
            <a href="admin_games.php">Vissza</a>
            <a href="user_bookings.php"><span>📅</span> Saját foglalások</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Foglalások</a>
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
        <h3>Játék szerkesztése: <?= htmlspecialchars($game_data['name']) ?></h3>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="data-form">
            <div class="form-group">
                <label for="name">Játék neve*</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($game_data['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Leírás*</label>
                <textarea id="description" name="description" rows="5"
                    required><?= htmlspecialchars($game_data['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Ár (Ft)*</label>
                <input type="number" id="price" name="price" value="<?= $game_data['price'] ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Kép kezelése</label>

                <?php if ($game_data['image_url']): ?>
                    <div class="current-image-container">
                        <p><strong>Jelenlegi kép:</strong></p>
                        <img src="<?= htmlspecialchars($game_data['image_url']) ?>" class="image-preview"
                            id="currentImagePreview" style="max-width: 200px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                            <label class="form-check-label" for="remove_image">Jelenlegi kép eltávolítása</label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="image"
                        class="form-label"><?= $game_data['image_url'] ? 'Új kép feltöltése a régi lecseréléséhez' : 'Kép feltöltése' ?></label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small class="form-hint">Engedélyezett formátumok: JPG, PNG, GIF. Max. méret: 10MB.</small>
                </div>

                <img id="imagePreview" src="#" alt="Kép előnézet" class="image-preview"
                    style="display:none; max-width: 200px; margin-top: 10px;">
            </div>

            <div class="form-actions">
                <a href="admin_games.php" class="btn btn-cancel">Mégse</a>
                <button type="submit" class="btn btn-primary">Változtatások mentése</button>
            </div>
        </form>
    </main>

    <script>
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const currentImagePreview = document.getElementById('currentImagePreview');
        const removeImageCheckbox = document.getElementById('remove_image');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                    if (currentImagePreview) {
                        currentImagePreview.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                if (currentImagePreview) {
                    currentImagePreview.style.display = 'block';
                }
            }
        });

        if (removeImageCheckbox) {
            removeImageCheckbox.addEventListener('change', function(e) {
                if (this.checked) {
                    imageInput.disabled = true;
                    imageInput.value = ''; // Töröljük a kiválasztott fájlt, ha van
                    imagePreview.style.display = 'none';
                    if (currentImagePreview) {
                        currentImagePreview.style.opacity = '0.5';
                    }
                } else {
                    imageInput.disabled = false;
                    if (currentImagePreview) {
                        currentImagePreview.style.opacity = '1';
                    }
                }
            });
        }
    </script>
</body>

</html>