<?php
require '../db.php';
header('Content-Type: application/json');

// RESTful végpont kezelése
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$gameId = isset($request[0]) ? (int) $request[0] : null;

// Csak admin hozzáférés
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Hozzáférés megtagadva']);
    exit;
}

try {
    switch ($method) {
        case 'DELETE': // Játék törlése
            if (!$gameId) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen kérés']);
                exit;
            }

            // Ellenőrizzük, van-e aktív foglalás
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE game_id = ? AND status != 'Törölve'");
            $stmt->execute([$gameId]);
            $hasActiveBookings = $stmt->fetchColumn();

            if ($hasActiveBookings) {
                http_response_code(400);
                echo json_encode(['error' => 'Nem törölhető, mert vannak aktív foglalások']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
            $stmt->execute([$gameId]);
            echo json_encode(['success' => 'Játék törölve']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Nem támogatott metódus']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Szerverhiba: ' . $e->getMessage()]);
}
