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

$orders = [];
$reservations = [];

// ====== STATUS ORDER ======
$sqlOrder = "SELECT id_transaction_order, status FROM transaction_order";
if ($result = $mysqli->query($sqlOrder)) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id'     => (int)$row['id_transaction_order'],
            'status' => $row['status']
        ];
    }
    $result->free();
}

// ====== STATUS RESERVATION ======
$sqlRes = "SELECT id_transaction_reservation, status FROM transaction_reservation";
if ($result = $mysqli->query($sqlRes)) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = [
            'id'     => (int)$row['id_transaction_reservation'],
            'status' => $row['status']
        ];
    }
    $result->free();
}

echo json_encode([
    'success'       => true,
    'orders'        => $orders,
    'reservations'  => $reservations
]);

$mysqli->close();
