<?php
require '../db.php';
header('Content-Type: application/json');

// RESTful végpont kezelése
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$bookingId = isset($request[0]) ? (int) $request[0] : null;

// Csak admin hozzáférés
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Hozzáférés megtagadva']);
    exit;
}

try {
    switch ($method) {
        case 'PUT': // Státusz frissítése
            $input = json_decode(file_get_contents('php://input'), true);
            $status = $input['status'] ?? null;

            if (!$bookingId || !in_array($status, ['Függőben', 'Jóváhagyva', 'Elutasítva'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen kérés']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $bookingId]);
            echo json_encode(['success' => 'Státusz frissítve']);
            break;

        case 'DELETE': // Törlés (logikai törlés)
            if (!$bookingId) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen kérés']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Törölve' WHERE id = ?");
            $stmt->execute([$bookingId]);
            echo json_encode(['success' => 'Foglalás törölve']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Nem támogatott metódus']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Szerverhiba: ' . $e->getMessage()]);
}
