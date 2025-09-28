<?php
// MultipleFiles/utils/ImageUpload.php

/**
 * Képfeltöltést kezelő funkció.
 *
 * @param array $file A $_FILES['fajlnev'] tömb.
 * @return array Asszociatív tömb 'image_url' kulccsal siker esetén, vagy 'error' kulccsal hiba esetén.
 */
function handleImageUpload(array $file): array
{
    // Hibaellenőrzés
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Hiba történt a fájlfeltöltés során. Hibakód: ' . $file['error']];
    }

    $upload_dir = 'uploads/games/';
    // Ellenőrizzük, hogy a mappa létezik-e, ha nem, létrehozzuk
    if (!is_dir($upload_dir)) {
        // Fontos: a 0775 jogok megfelelőek, de a true a rekurzív létrehozáshoz kell
        if (!mkdir($upload_dir, 0775, true)) {
            return ['error' => 'Nem sikerült létrehozni a feltöltési mappát.'];
        }
    }

    // mime_content_type ellenőrzés a fájl tényleges típusára
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return ['error' => 'Érvénytelen fájltípus! Csak JPG, PNG és GIF képek engedélyezettek.'];
    }

    // Méret ellenőrzése (max 2MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['error' => 'A fájl mérete túl nagy! Maximum 10MB lehet.'];
    }

    // Egyedi fájlnév generálása
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('game_', true) . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['image_url' => $destination];
    } else {
        return ['error' => 'Nem sikerült a fájlt a végleges helyére mozgatni.'];
    }
}
