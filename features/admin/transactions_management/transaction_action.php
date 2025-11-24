<?php
header('Content-Type: application/json');

// ====== KONFIG DATABASE ======
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'restaurant';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $mysqli->connect_error
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode harus POST'
    ]);
    exit;
}

$type   = isset($_POST['type'])   ? $_POST['type']   : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id     = isset($_POST['id'])     ? (int)$_POST['id'] : 0;

if ($id <= 0 || !in_array($type, ['order','reservation'], true) || !in_array($action, ['approve','cancel'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak valid'
    ]);
    exit;
}

$newStatus = ($action === 'approve') ? 'confirmed' : 'cancelled';

if ($type === 'order') {
    $stmt = $mysqli->prepare("UPDATE transaction_order SET status = ? WHERE id_transaction_order = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Prepare gagal: ' . $mysqli->error
        ]);
        exit;
    }
    $stmt->bind_param("si", $newStatus, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update status order'
        ]);
        exit;
    }
} else {
    // reservation
    $stmt = $mysqli->prepare("UPDATE transaction_reservation SET status = ? WHERE id_transaction_reservation = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Prepare gagal (reservation): ' . $mysqli->error
        ]);
        exit;
    }
    $stmt->bind_param("si", $newStatus, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update status transaction_reservation'
        ]);
        exit;
    }

    // Update tabel reservation juga (optional, biar sinkron)
    $resId = null;
    $stmt2 = $mysqli->prepare("SELECT id_reservation FROM transaction_reservation WHERE id_transaction_reservation = ? LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param("i", $id);
        if ($stmt2->execute()) {
            $stmt2->bind_result($resId);
            $stmt2->fetch();
        }
        $stmt2->close();
    }

    if ($resId) {
        $stmt3 = $mysqli->prepare("UPDATE reservation SET status = ? WHERE id_reservation = ?");
        if ($stmt3) {
            $stmt3->bind_param("si", $newStatus, $resId);
            $stmt3->execute();
            $stmt3->close();
        }
    }
}

echo json_encode([
    'success'    => true,
    'newStatus'  => $newStatus
]);

$mysqli->close();
