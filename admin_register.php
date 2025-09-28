<?php
require 'db.php'; // PDO kapcsolat

// Flash üzenetek kiolvasása és ürítése
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Titkos kulcs a regisztrációhoz és admin kezelőhöz
$secret_key = 'SUPERSECRET123';

// --- Admin <-> User átváltás ---
if (isset($_POST['action']) && $_POST['action'] === 'toggle_role') {
    $key = $_POST['secret_key'] ?? '';
    $userId = $_POST['user_id'] ?? 0;

    if ($key !== $secret_key) {
        $_SESSION['error'] = "Hibás titkos kulcs!";
    } else {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $currentRole = $stmt->fetchColumn();

        if ($currentRole) {
            $newRole = ($currentRole === 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([':role' => $newRole, ':id' => $userId]);
            $_SESSION['success'] = "Szerepkör módosítva: mostantól " . htmlspecialchars($newRole);
        } else {
            $_SESSION['error'] = "Nincs ilyen felhasználó!";
        }
    }
    header('Location: admin_register.php');
    exit;
}

// --- Admin regisztráció ---
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $key = $_POST['secret_key'] ?? '';
    $adminUsername = trim($_POST['username'] ?? '');
    $adminPasswordRaw = trim($_POST['password'] ?? '');
    $adminEmail = trim($_POST['email'] ?? '');
    $adminRole = 'admin';

    if ($key !== $secret_key) {
        $_SESSION['error'] = "Hibás titkos kulcs!";
    } elseif (empty($adminUsername) || empty($adminPasswordRaw) || empty($adminEmail)) {
        $_SESSION['error'] = "Minden mezőt ki kell tölteni!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $adminUsername, ':email' => $adminEmail]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Ez a felhasználónév vagy email már létezik!";
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
            $_SESSION['success'] = "Admin user létrehozva.";
        }
    }
    header('Location: admin_register.php');
    exit;
}

// --- Admin törlés ---
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $key = $_POST['secret_key'] ?? '';
    $userId = $_POST['user_id'] ?? 0;

    if ($key !== $secret_key) {
        $_SESSION['error'] = "Hibás titkos kulcs!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'admin'");
        $stmt->execute([':id' => $userId]);
        $_SESSION['success'] = "Admin törölve.";
    }
    header('Location: admin_register.php');
    exit;
}

// --- Felhasználó módosítás ---
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $key = $_POST['secret_key'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPasswordRaw = trim($_POST['password'] ?? '');

    if ($key !== $secret_key) {
        $_SESSION['error'] = "Hibás titkos kulcs!";
    } else {
        $params = [':id' => $userId, ':username' => $newUsername, ':email' => $newEmail];
        $sql = "UPDATE users SET username = :username, email = :email";

        if (!empty($newPasswordRaw)) {
            $newPassword = password_hash($newPasswordRaw, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params[':password'] = $newPassword;
        }

        $sql .= " WHERE id = :id"; // → eltávolítjuk a role='admin'-t
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success'] = "Felhasználó frissítve.";
    }
    header('Location: admin_register.php');
    exit;
}

// --- Lekérjük az adminokat ---
$stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE role='admin'");
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
    <header>
        <nav>
            <a href="index.php">Vissza</a>
            <a href="user_bookings.php"><span>📅</span> Saját foglalások</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Foglalások</a>
                <a href="admin_games.php">Játékok kezelése</a>
                <a href="add_game.php">+Új játék</a>
                <a href="users.php">Felhasználók</a>
                <a href="admin_register.php" class="active">Admin kezelés/regisztráció</a>
            <?php endif; ?>
            <a href="about_us.php">Rólunk</a>
            <a href="logout.php"><span>🚪</span> Kijelentkezés</a>
        </nav>
    </header>
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
    <div class="admin-register-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Szerepkör</th>
                    <th>Jelszócsere</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <form method="post" id="form-admin-<?= $admin['id'] ?>"></form>
                        <td><?= $admin['id'] ?></td>
                        <td>
                            <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" form="form-admin-<?= $admin['id'] ?>" class="admin-register-input">
                        </td>
                        <td>
                            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" form="form-admin-<?= $admin['id'] ?>" class="admin-register-input">
                        </td>
                        <td><?= htmlspecialchars($admin['role']) ?></td>
                        <td>
                            <input type="password" name="password" placeholder="Új jelszó" form="form-admin-<?= $admin['id'] ?>" class="admin-register-input">
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= $admin['id'] ?>" form="form-admin-<?= $admin['id'] ?>">
                            <input type="text" name="secret_key" placeholder="Titkos kulcs" required form="form-admin-<?= $admin['id'] ?>" class="admin-register-input">

                            <button type="submit" name="action" value="update" form="form-admin-<?= $admin['id'] ?>" class="admin-register-button btn-edit">Módosítás</button>
                            <button type="submit" name="action" value="delete" form="form-admin-<?= $admin['id'] ?>" class="admin-register-button btn-delete" onclick="return confirm('Biztos törlöd ezt az admin fiókot?');">Törlés</button>
                            <button type="submit" name="action" value="toggle_role" form="form-admin-<?= $admin['id'] ?>" class="admin-register-button btn-toggle">Userré fokozás</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2 class="admin_register_h2">User-ek kezelése (adminná emelés)</h2>
    <?php
    // Lekérjük a normál user-eket
    $stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE role='user'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="admin-register-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Szerepkör</th>
                    <th>Jelszócsere</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="post" id="form-user-<?= $user['id'] ?>"></form>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" form="form-user-<?= $user['id'] ?>" class="admin-register-input">
                        </td>
                        <td>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" form="form-user-<?= $user['id'] ?>" class="admin-register-input">
                        </td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <input type="password" name="password" placeholder="Új jelszó" form="form-user-<?= $user['id'] ?>" class="admin-register-input">
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>" form="form-user-<?= $user['id'] ?>">
                            <input type="text" name="secret_key" placeholder="Titkos kulcs" required form="form-user-<?= $user['id'] ?>" class="admin-register-input">

                            <button type="submit" name="action" value="update" form="form-user-<?= $user['id'] ?>" class="admin-register-button btn-edit">Módosítás</button>
                            <button type="submit" name="action" value="toggle_role" form="form-user-<?= $user['id'] ?>" class="admin-register-button btn-toggle">Adminná emelés</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>