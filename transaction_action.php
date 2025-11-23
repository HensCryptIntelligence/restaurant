<?php
header('Content-Type: application/json');

// KONFIG DB
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'restaurant';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal koneksi database: ' . $mysqli->connect_error
    ]);
    exit;
}

// ambil input dari POST
$type   = isset($_POST['type'])   ? $_POST['type']   : '';
$id     = isset($_POST['id'])     ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$id || !in_array($type, ['order', 'reservation'], true) || !in_array($action, ['approve', 'cancel'], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak valid'
    ]);
    exit;
}

$status = ($action === 'approve') ? 'confirmed' : 'cancelled';

if ($type === 'order') {
    $stmt = $mysqli->prepare("UPDATE transaction_order SET status = ? WHERE id_transaction_order = ?");
} else {
    $stmt = $mysqli->prepare("UPDATE transaction_reservation SET status = ? WHERE id_transaction_reservation = ?");
}

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare gagal: ' . $mysqli->error
    ]);
    exit;
}

$stmt->bind_param("si", $status, $id);

if (!$stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal update status: ' . $stmt->error
    ]);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'type'    => $type,
    'id'      => $id,
    'status'  => $status
]);

$mysqli->close();
