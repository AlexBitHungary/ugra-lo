<?php
// Kiléptetés - logout.php
session_start();

// Töröljük a session változókat
$_SESSION = [];

// Ha van session cookie, töröljük
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Végleges session megsemmisítés
session_unset();
session_destroy();

// Átirányítás az új login oldalra
header('Location: ../frontend/login.php');
exit;
