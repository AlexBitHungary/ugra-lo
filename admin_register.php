<?php
//A regisztrációt ezen .php kód futtatásával kell végrehajtani.
//Futtatáskor azonnal végbemegy a kód,ezért "automata" a kód futása.

require 'db.php';

//Ha az admin helyett más nevet akarunk,itt át lehet írni.
$username = 'admin';
//A password ('jelszó') helyére kell beírni a kívánt jelszót.
$password = password_hash('password', PASSWORD_DEFAULT);
$role = 'admin';

//Az elkészített adatokat itt töltjük fel a táblába
$sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':username' => $username,
    ':password' => $password,
    ':role' => $role
]);

echo "Admin user létrehozva.";
