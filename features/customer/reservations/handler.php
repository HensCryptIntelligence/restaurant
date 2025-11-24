<?php
session_start();

$host = 'localhost';
$dbname = 'restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || !isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    switch ($_POST['action']) {
        case 'fetch_my_reservations':
            $stmt = $pdo->prepare("
                SELECT r.*, rr.seats as table_capacity, 
                       COALESCE(r.customer_name, u.fullname) as display_name
                FROM reservation r
                JOIN reservation_rooms rr ON r.id_reservation_room = rr.id_reservation_room
                JOIN users u ON r.id_user = u.id_user
                WHERE r.id_user = :user_id AND r.status != 'cancelled'
                ORDER BY r.reservation_date DESC, r.reservation_start DESC
                LIMIT 20
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $res = $stmt->fetchAll();
            $data = [];
            foreach ($res as $r) {
                $data[] = [
                    'id' => $r['id_reservation'],
                    'table' => $r['id_reservation_room'],
                    'date' => date('Y-m-d', strtotime($r['reservation_date'])),
                    'time' => date('H:i', strtotime($r['reservation_start'])),
                    'guests' => $r['seats'],
                    'phone' => $r['phone_number'],
                    'name' => $r['display_name'],
                    'status' => $r['status']
                ];
            }
            $response = ['success' => true, 'data' => $data];
            break;

        case 'add_reservation':
            $req = ['table','hour','phone','guests','date','name'];
            foreach ($req as $f) if (empty($_POST[$f])) throw new Exception('All fields required!');
            
            $table = $_POST['table'];
            $hour = (int)$_POST['hour'];
            $phone = trim($_POST['phone']);
            $guests = (int)$_POST['guests'];
            $date = $_POST['date'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email'] ?? '');

            if ($date < date('Y-m-d')) throw new Exception('Cannot book past date!');
            
            $stmt = $pdo->prepare("SELECT seats FROM reservation_rooms WHERE id_reservation_room = :t");
            $stmt->execute(['t' => $table]);
            $room = $stmt->fetch();
            if (!$room) throw new Exception('Table not found!');
            if ($guests > $room['seats']) throw new Exception("Table only has {$room['seats']} seats!");

            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM reservation 
                WHERE id_reservation_room = :t AND DATE(reservation_date) = :d 
                AND HOUR(reservation_start) = :h AND status != 'cancelled'
            ");
            $stmt->execute(['t' => $table, 'd' => $date, 'h' => $hour]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Time slot already booked!');

            $start = $date . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
            $stmt = $pdo->prepare("
                INSERT INTO reservation (
                    id_user, customer_name, id_reservation_room, seats, 
                    reservation_start, reservation_time, reservation_date,
                    phone_number, email_address, status, created_by, created_at
                ) VALUES (
                    :uid, :name, :t, :g, :start, 120, :d, :phone, :email, 'pending', 'customer', NOW()
                )
            ");
            $stmt->execute([
                'uid' => $_SESSION['user_id'],
                'name' => $name,
                't' => $table,
                'g' => $guests,
                'start' => $start,
                'd' => $date,
                'phone' => $phone,
                'email' => $email
            ]);
            $response = ['success' => true, 'message' => 'Reservation successful! Waiting for admin confirmation.'];
            break;

        case 'cancel_reservation':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("SELECT id_reservation FROM reservation WHERE id_reservation = :id AND id_user = :uid");
            $stmt->execute(['id' => $id, 'uid' => $_SESSION['user_id']]);
            if (!$stmt->fetch()) throw new Exception('Reservation not found!');
            
            $stmt = $pdo->prepare("UPDATE reservation SET status = 'cancelled', updated_at = NOW() WHERE id_reservation = :id");
            $stmt->execute(['id' => $id]);
            $response = ['success' => true, 'message' => 'Reservation cancelled.'];
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;