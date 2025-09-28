<?php
require '../backend/db.php';

//Fiók regisztrálása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$username, $email, $password]);
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Hiba: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - UGRÁ-LÓ</title>
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
    <main class="auth-container">
        <h1>UGRÁ-LÓ</h1>
        <h2>Regisztráció</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" class="data-form">
            <div class="form-group">
                <label for="username">Felhasználónév</label>
                <input type="text" id="username" name="username" placeholder="Felhasználónév" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="E-mail" required>
            </div>
            <div class="form-group">
                <label for="password">Jelszó</label>
                <input type="password" id="password" name="password" placeholder="Jelszó" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Regisztráció</button>
            </div>
        </form>
        <p class="auth-link">Van már fiókod? <a href="login.php">Jelentkezz be!</a></p>
        <p class="auth-link">
            Csak nézelődnél?
            Akkor kattints <a href="guest.php">ide</a>
        </p>
    </main>
</body>

</html>