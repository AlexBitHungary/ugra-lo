<?php
require 'db.php';

// Csak admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$game_id = (int) ($_POST['id'] ?? 0);

if ($game_id > 0) {
    try {
        // Tranzakció kezdete
        $pdo->beginTransaction();

        // 1. Játék adatainak lekérése
        $stmt = $pdo->prepare("SELECT image_url FROM games WHERE id = ?");
        $stmt->execute([$game_id]);
        $game = $stmt->fetch();

        if (!$game) {
            throw new Exception("A játék nem található!");
        }

        // 2. Kép törlése ha van
        $image_deleted = false;
        if (!empty($game['image_url'])) {
            $image_path = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($game['image_url'], '/'));

            // Biztonsági ellenőrzés - csak az uploads mappából engedjük a törlést
            if (
                strpos($image_path, realpath($_SERVER['DOCUMENT_ROOT'] . '/uploads/games/')) !== false &&
                file_exists($image_path)
            ) {
                unlink($image_path);
                $image_deleted = true;
            }
        }

        // 3. Játék törlése az adatbázisból
        $delete_stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
        $delete_stmt->execute([$game_id]);

        // Tranzakció commit
        $pdo->commit();

        $_SESSION['message'] = "Játék sikeresen törölve!" . ($image_deleted ? " A kép is törölve lett." : "");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Hiba: " . $e->getMessage();
        error_log("Törlési hiba: " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = "Érvénytelen játék azonosító!";
}

header('Location: admin_games.php');
exit;
