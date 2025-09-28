<?php
require '../backend/db.php';
// Csak admin láthatja
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$users = $pdo->prepare("SELECT id, username, email, role, created_at FROM users;");
$users->execute();
$users = $users->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Felhasználók</title>
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
        <a href="admin.php">Foglalások</a>
        <a href="admin_games.php">Játékok kezelése</a>
        <a href="add_game.php">+Új játék</a>
        <a href="users.php" class="active">Felhasználók</a>
        <a href="admin_register.php">Admin kezelés/regisztráció</a>
      <?php endif; ?>
      <a href="about_us.php">Rólunk</a>
      <a href="logout.php"><span>🚪</span> Kijelentkezés</a>
    </nav>
  </header>

  <main class="admin-content">
    <h1>Felhasználók listája</h1>
    <div class="table-container">
      <table class="users-table">
        <tbody>
          <thead>
            <tr>
              <th>ID</th>
              <th>Felhasználónév</th>
              <th>Email</th>
              <th>Szerep</th>
              <th>Regisztráció dátuma</th>
            </tr>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </thead>
        </tbody>
      </table>
    </div>
  </main>

</body>

</html>