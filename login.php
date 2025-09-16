<?php
require 'db.php';

//Ellenőrizzük,hogy a felhasználói adatok helyesek e,ha igen,beléphet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Hibás felhasználónév vagy jelszó!";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belépés - UGRÁ-LÓ</title>
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
    <main class="auth-container">
        <h1>UGRÁ-LÓ</h1>
        <h2>Belépés</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" class="data-form">
            <div class="form-group">
                <label for="username">Felhasználónév</label>
                <input type="text" id="username" name="username" placeholder="Felhasználónév" required>
            </div>
            <div class="form-group">
                <label for="password">Jelszó</label>
                <input type="password" id="password" name="password" placeholder="Jelszó" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Belépés</button>
            </div>
        </form>
        <p class="auth-link">Nincs fiókod? <a href="register.php">Regisztrálj itt!</a></p>
        <p class="auth-link">
            Csak nézelődnél?
            Akkor kattints <a href="guest.php">ide</a>
        </p>
    </main>
</body>

</html>