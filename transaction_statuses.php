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

$orders = [];
$reservations = [];

// kalau mau filter per user, bisa pakai WHERE id_user = ? di kedua query
$orderSql = "SELECT id_transaction_order, status FROM transaction_order";
if ($res = $mysqli->query($orderSql)) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = [
            'id'     => (int)$row['id_transaction_order'],
            'status' => $row['status']
        ];
    }
    $res->free();
}

$resSql = "SELECT id_transaction_reservation, status FROM transaction_reservation";
if ($res = $mysqli->query($resSql)) {
    while ($row = $res->fetch_assoc()) {
        $reservations[] = [
            'id'     => (int)$row['id_transaction_reservation'],
            'status' => $row['status']
        ];
    }
    $res->free();
}

echo json_encode([
    'success'      => true,
    'orders'       => $orders,
    'reservations' => $reservations
]);

$mysqli->close();
