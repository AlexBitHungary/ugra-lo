<?php
require 'db.php';
require 'utils/ImageUpload.php';

// Csak admin férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Inicializáljuk a változókat
$error = '';
$success = '';
$name = '';
$description = '';
$price = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (int) $_POST['price'];
    $image_url = null; // Alapértelmezetten null

    // Validáció
    if (empty($name) || empty($description) || $price <= 0) {
        $error = 'Kérjük, töltsd ki minden kötelező mezőt érvényes adatokkal!';
    } else {
        try {
            // Képfeltöltés kezelése
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleImageUpload($_FILES['image']);
                if (isset($upload_result['error'])) {
                    throw new Exception($upload_result['error']);
                }
                $image_url = $upload_result['image_url'];
            }

            $stmt = $pdo->prepare("INSERT INTO games (name, description, price, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image_url]);
            $success = 'A játék sikeresen hozzáadva!';

            // Űrlap ürítése
            $name = $description = '';
            $price = '';
        } catch (Exception $e) {
            $error = 'Hiba történt: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új játék hozzáadása - UGRÁ-LÓ</title>
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
                <a href="admin_games.php">Játékok kezelése</a>
                <a href="add_game.php" class="active">+Új játék</a>
                <a href="users.php">Felhasználók</a>
            <?php endif; ?>
            <a href="about_us.php">Rólunk</a>
            <a href="logout.php"><span>🚪</span> Kijelentkezés</a>
        </nav>
    </header>

    <main class="admin-content">
        <h3>Új játék hozzáadása</h3>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" class="data-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Játék neve*</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Leírás*</label>
                <textarea id="description" name="description" rows="4"
                    required><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Ár (Ft)*</label>
                <input type="number" id="price" name="price" min="1" value="<?= htmlspecialchars($price) ?>" required>
            </div>

            <div class="form-group">
                <label for="image">Kép feltöltése (opcionális)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small class="form-hint">Engedélyezett formátumok: JPG, PNG, GIF. Max. méret: 10MB.</small>
                <img id="imagePreview" src="#" alt="Kép előnézet" class="image-preview"
                    style="display:none; max-width: 200px; margin-top: 10px;">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Mentés</button>
                <a href="admin_games.php" class="btn btn-cancel">Mégse</a>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
    </script>
</body>

</html>