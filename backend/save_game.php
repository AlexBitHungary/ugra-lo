<?php
require 'db.php';
require 'utils/ImageUpload.php';

//Csak admin férhet hozzá
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = (float) ($_POST['price'] ?? 0);
$image_url = '';

// Képfeltöltés kezelése
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload = handleImageUpload($_FILES['image']);
    if (isset($upload['error'])) {
        $_SESSION['error'] = $upload['error'];
        header('Location: admin_games.php');
        exit;
    }
    $image_url = $upload['image_url'];
}

try {
    $stmt = $pdo->prepare("INSERT INTO games (name, description, price, image_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $image_url]);
    $_SESSION['message'] = "Játék sikeresen hozzáadva!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba a játék mentésekor: " . $e->getMessage();
}

header('Location: admin_games.php');
