<?php
require_once 'config.php';

// ==========================================
// HANDLE AJAX REQUESTS ONLY
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($_POST['action']) {
            case 'add':
                // Validasi input
                if (empty($_POST['table']) || empty($_POST['hour']) || empty($_POST['name']) || 
                    empty($_POST['phone']) || empty($_POST['guests']) || empty($_POST['date'])) {
                    throw new Exception('All required fields must be filled!');
                }
                
                $table = $_POST['table'];
                $hour = (int)$_POST['hour'];
                $name = trim($_POST['name']);
                $phone = trim($_POST['phone']);
                $guests = (int)$_POST['guests'];
                $date = $_POST['date'];
                $email = trim($_POST['email'] ?? '');
                
                if ($guests <= 0) {
                    throw new Exception('Number of guests must be at least 1!');
                }
                
                // Cek apakah slot sudah terisi
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM reservation 
                    WHERE id_reservation_room = :table 
                    AND DATE(reservation_date) = :date 
                    AND HOUR(reservation_start) = :hour
                    AND status != 'cancelled'
                ");
                $stmt->execute([
                    'table' => $table,
                    'date' => $date,
                    'hour' => $hour
                ]);
                
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('This time slot is already booked!');
                }
                
                // Cari atau buat user baru
                $stmt = $pdo->prepare("SELECT id_user FROM users WHERE fullname = :name LIMIT 1");
                $stmt->execute(['name' => $name]);
                $userId = $stmt->fetchColumn();
                
                if (!$userId) {
                    $stmt = $pdo->prepare("
                        INSERT INTO users (fullname, password_hash, role) 
                        VALUES (:name, :password, 'customer')
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'password' => password_hash('default123', PASSWORD_BCRYPT)
                    ]);
                    $userId = $pdo->lastInsertId();
                }
                
                // Insert reservasi
                $reservationStart = $date . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                $stmt = $pdo->prepare("
                    INSERT INTO reservation (
                        id_user, id_reservation_room, seats, 
                        reservation_start, reservation_time, reservation_date,
                        phone_number, email_address, status
                    ) VALUES (
                        :id_user, :id_room, :seats,
                        :res_start, 120, :res_date,
                        :phone, :email, 'pending'
                    )
                ");
                
                $stmt->execute([
                    'id_user' => $userId,
                    'id_room' => $table,
                    'seats' => $guests,
                    'res_start' => $reservationStart,
                    'res_date' => $date,
                    'phone' => $phone,
                    'email' => $email
                ]);
                
                $resId = $pdo->lastInsertId();
                
                $response['success'] = true;
                $response['message'] = '✅ Reservation added successfully!';
                $response['data'] = [
                    'id' => $resId,
                    'table' => $table,
                    'hour' => $hour,
                    'name' => $name,
                    'phone' => $phone,
                    'guests' => $guests,
                    'date' => $date,
                    'status' => 'pending'
                ];
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                
                if ($id <= 0) {
                    throw new Exception('Invalid reservation ID!');
                }
                
                $stmt = $pdo->prepare("SELECT id_user FROM reservation WHERE id_reservation = :id");
                $stmt->execute(['id' => $id]);
                $reservation = $stmt->fetch();
                
                if (!$reservation) {
                    throw new Exception('Reservation not found!');
                }
                
                $updateFields = [];
                $params = ['id' => $id];
                
                if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
                    $stmt = $pdo->prepare("
                        UPDATE users u 
                        JOIN reservation r ON u.id_user = r.id_user 
                        SET u.fullname = :name 
                        WHERE r.id_reservation = :id
                    ");
                    $stmt->execute(['name' => trim($_POST['name']), 'id' => $id]);
                }
                
                if (isset($_POST['phone']) && !empty(trim($_POST['phone']))) {
                    $updateFields[] = "phone_number = :phone";
                    $params['phone'] = trim($_POST['phone']);
                }
                
                if (isset($_POST['email'])) {
                    $updateFields[] = "email_address = :email";
                    $params['email'] = trim($_POST['email']);
                }
                
                if (isset($_POST['guests']) && (int)$_POST['guests'] > 0) {
                    $updateFields[] = "seats = :seats";
                    $params['seats'] = (int)$_POST['guests'];
                }
                
                if (isset($_POST['status']) && in_array($_POST['status'], ['pending', 'confirmed', 'seated', 'cancelled'])) {
                    $updateFields[] = "status = :status";
                    $params['status'] = $_POST['status'];
                }
                
                if (!empty($updateFields)) {
                    $sql = "UPDATE reservation SET " . implode(', ', $updateFields) . " WHERE id_reservation = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                }
                
                $response['success'] = true;
                $response['message'] = '✅ Reservation updated successfully!';
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                if ($id <= 0) {
                    throw new Exception('Invalid reservation ID!');
                }
                
                $stmt = $pdo->prepare("SELECT id_reservation FROM reservation WHERE id_reservation = :id");
                $stmt->execute(['id' => $id]);
                
                if (!$stmt->fetch()) {
                    throw new Exception('Reservation not found!');
                }
                
                $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = :id");
                $stmt->execute(['id' => $id]);
                
                $response['success'] = true;
                $response['message'] = '✅ Reservation deleted successfully!';
                break;
                
            case 'get':
                $id = (int)$_POST['id'];
                
                if ($id <= 0) {
                    throw new Exception('Invalid reservation ID!');
                }
                
                $stmt = $pdo->prepare("
                    SELECT r.*, u.fullname as name 
                    FROM reservation r 
                    JOIN users u ON r.id_user = u.id_user 
                    WHERE r.id_reservation = :id
                ");
                $stmt->execute(['id' => $id]);
                $res = $stmt->fetch();
                
                if ($res) {
                    $response['success'] = true;
                    $response['data'] = [
                        'id' => $res['id_reservation'],
                        'table' => $res['id_reservation_room'],
                        'hour' => (int)date('H', strtotime($res['reservation_start'])),
                        'name' => $res['name'],
                        'phone' => $res['phone_number'],
                        'email' => $res['email_address'],
                        'guests' => $res['seats'],
                        'date' => date('Y-m-d', strtotime($res['reservation_date'])),
                        'status' => $res['status'],
                        'notes' => ''
                    ];
                } else {
                    throw new Exception('Reservation not found!');
                }
                break;
                
            default:
                throw new Exception('Invalid action!');
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = '❌ ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>