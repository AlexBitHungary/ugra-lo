<?php
require 'db.php'; // PDO kapcsolat

$success = '';
$error = '';

// Titkos kulcs a regisztrációhoz és admin kezelőhöz
$secret_key = 'SUPERSECRET123';

// --- Admin regisztráció ---
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $key = $_POST['secret_key'] ?? '';
    $adminUsername = trim($_POST['username'] ?? '');
    $adminPasswordRaw = trim($_POST['password'] ?? '');
    $adminEmail = trim($_POST['email'] ?? '');
    $adminRole = 'admin';

    if ($key !== $secret_key) {
        $error = "Hibás titkos kulcs!";
    } elseif (empty($adminUsername) || empty($adminPasswordRaw) || empty($adminEmail)) {
        $error = "Minden mezőt ki kell tölteni!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $adminUsername,
            ':email' => $adminEmail
        ]);

        if ($stmt->rowCount() > 0) {
            $error = "Ez a felhasználónév vagy email már létezik!";
        } else {
            $adminPassword = password_hash($adminPasswordRaw, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password, role, email) VALUES (:username, :password, :role, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $adminUsername,
                ':password' => $adminPassword,
                ':role' => $adminRole,
                ':email' => $adminEmail
            ]);

            $success = "Admin user létrehozva.";
        }
    }
}

// --- Admin törlés ---
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $key = $_POST['secret_key'] ?? '';
    $userId = $_POST['user_id'] ?? 0;

    if ($key !== $secret_key) {
        $error = "Hibás titkos kulcs!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'admin'");
        $stmt->execute([':id' => $userId]);
        $success = "Admin törölve.";
    }
}

// --- Admin módosítás ---
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $key = $_POST['secret_key'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPasswordRaw = trim($_POST['password'] ?? '');

    if ($key !== $secret_key) {
        $error = "Hibás titkos kulcs!";
    } else {
        $params = [':id' => $userId, ':username' => $newUsername, ':email' => $newEmail];
        $sql = "UPDATE users SET username = :username, email = :email";

        if (!empty($newPasswordRaw)) {
            $newPassword = password_hash($newPasswordRaw, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params[':password'] = $newPassword;
        }

        $sql .= " WHERE id = :id AND role = 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $success = "Admin frissítve.";
    }
}

// --- Lekérjük az adminokat ---
$stmt = $pdo->query("SELECT id, username, email FROM users WHERE role='admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- EZ A KULCS! Nélküle minden kicsi marad -->
    <title>Admin kezelés</title>
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

    <h2 class="admin_register_h2">Admin regisztráció</h2>

    <?php if ($success): ?>
        <div class="admin-register-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="admin-register-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="admin-register-form">
        <input type="hidden" name="action" value="register">
        <label>Felhasználónév:</label>
        <input type="text" name="username" required class="admin-register-input">
        <label>Email:</label>
        <input type="email" name="email" required class="admin-register-input">
        <label>Jelszó:</label>
        <input type="password" name="password" required class="admin-register-input">
        <label>Titkos kulcs:</label>
        <input type="text" name="secret_key" required class="admin-register-input">
        <button type="submit" class="admin-register-button">Regisztrálás</button>
    </form>

    <h2 class="admin_register_h2">Adminok kezelése</h2>
    <table class="admin-register-table">
        <tr>
            <th>ID</th>
            <th>Felhasználónév</th>
            <th>Email</th>
            <th>Jelszócsere</th>
            <th>Műveletek</th>
        </tr>
        <?php foreach ($admins as $admin): ?>
            <tr>
                <form method="post">
                    <td data-label="ID">
                        <?= $admin['id'] ?>
                    </td>
                    <td data-label="Felhasználónév"> <!-- Hozzáadva: data-label -->
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" class="admin-register-input">
                    </td>
                    <td data-label="Email"> <!-- Hozzáadva: data-label -->
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="admin-register-input">
                    </td>
                    <td data-label="Jelszó / Kulcs"> <!-- Hozzáadva: data-label -->
                        <input type="password" name="password" placeholder="Új jelszó" class="admin-register-input">
                        <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                        <input type="text" name="secret_key" placeholder="Titkos kulcs" class="admin-register-input" required>
                    </td>
                    <td data-label="Műveletek"> <!-- Hozzáadva: data-label -->
                        <button type="submit" name="action" value="update" class="admin-register-button btn-edit">Módosítás</button> <!-- Osztály hozzáadva a CSS-hez -->
                        <button type="submit" name="action" value="delete" class="admin-register-button btn-delete" onclick="return confirm('Biztos törlöd ezt az admin fiókot?');">Törlés</button> <!-- Osztály hozzáadva -->
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>

    </table>

</body>

</html>