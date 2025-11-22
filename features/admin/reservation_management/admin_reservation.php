<?php
session_start();

// ==========================================
// KONEKSI DATABASE (XAMPP DEFAULT)
// ==========================================
$host = 'localhost';
$dbname = 'restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ DATABASE CONNECTION FAILED: " . $e->getMessage() . "<br><br>
         <strong>SOLUSI:</strong><br>
         1. Pastikan XAMPP MySQL sudah running<br>
         2. Buka phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a><br>
         3. Import file <strong>restaurant.sql</strong> terlebih dahulu<br>
         4. Pastikan database bernama <strong>restaurant</strong> sudah ada");
}

// ==========================================
// LOGIKA ADMIN RESERVATION
// ==========================================

// Data meja per lantai
$floor_data = [
    '1' => ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'],
    '2' => ['D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2'],
    '3' => ['G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2']
];

// Get selected floor (default: 1)
$selectedFloor = $_GET['floor'] ?? ($_COOKIE['selected_floor'] ?? '1');
$floor_tables = $floor_data[$selectedFloor] ?? $floor_data['1'];
$hours = range(10, 22);

// Set cookie for selected floor
setcookie('selected_floor', $selectedFloor, time() + 3600, '/'); // 1 hour

// ==========================================
// HANDLE AJAX REQUESTS
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
                
                // VALIDASI: Cek kapasitas meja
                $stmt = $pdo->prepare("SELECT seats FROM reservation_rooms WHERE id_reservation_room = :table");
                $stmt->execute(['table' => $table]);
                $roomData = $stmt->fetch();
                
                if (!$roomData) {
                    throw new Exception('Table not found!');
                }
                
                if ($guests > $roomData['seats']) {
                    throw new Exception("This table only has {$roomData['seats']} seats! You requested {$guests} guests. Please choose a larger table.");
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
                break;    )
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
                
                // Update user name
                if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
                    $stmt = $pdo->prepare("
                        UPDATE users u 
                        JOIN reservation r ON u.id_user = r.id_user 
                        SET u.fullname = :name 
                        WHERE r.id_reservation = :id
                    ");
                    $stmt->execute(['name' => trim($_POST['name']), 'id' => $id]);
                }
                
                // Update reservation fields
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

// ==========================================
// GET DATA UNTUK TAMPILAN
// ==========================================
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedFloor = $_GET['floor'] ?? '1';

try {
    // Get reservations
    $stmt = $pdo->prepare("
        SELECT r.*, u.fullname as name 
        FROM reservation r 
        JOIN users u ON r.id_user = u.id_user 
        WHERE DATE(r.reservation_date) = :date
        AND r.status != 'cancelled'
        ORDER BY r.reservation_start
    ");
    $stmt->execute(['date' => $selectedDate]);
    $reservations = $stmt->fetchAll();
    
    // Get rooms
    $stmt = $pdo->query("SELECT id_reservation_room, seats FROM reservation_rooms ORDER BY id_reservation_room");
    $rooms = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Helper function
function getReservation($table, $hour, $data) {
    foreach ($data as $res) {
        if ($res['id_reservation_room'] == $table && 
            (int)date('H', strtotime($res['reservation_start'])) == $hour) {
            return [
                'id' => $res['id_reservation'],
                'table' => $res['id_reservation_room'],
                'hour' => (int)date('H', strtotime($res['reservation_start'])),
                'name' => $res['name'],
                'phone' => $res['phone_number'],
                'email' => $res['email_address'],
                'guests' => $res['seats'],
                'status' => $res['status'],
                'date' => date('Y-m-d', strtotime($res['reservation_date']))
            ];
        }
    }
    return null;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bitehive — Reservation Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --pink-primary: #FAC1D9;
      --white: #FFFFFF;
      --dark-primary: #3D4142;
      --dark-secondary: #292C2D;
      --dark-tertiary: #333333;
      --gray-medium: #ADADAD;
      --red-accent: #E70000;
      --pink-light: #F8C0D7;
      --pink-medium: #FBCCE0;
      --black: #111315;
      --gray-light: #D9D9D9;
      --sidebar-width: 260px;
      --font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
      --font-size-base: 15px;
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 18px;
      --transition-fast: 0.15s ease;
      --transition-base: 0.3s ease;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      background: var(--dark-secondary);
      color: var(--white);
      font-family: var(--font-family);
      font-size: var(--font-size-base);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      overflow-x: hidden;
    }

    .app { min-height: 100vh; display: flex; position: relative; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); z-index: 40; opacity: 0; transition: opacity var(--transition-base); }
    .sidebar-overlay.active { display: block; opacity: 1; }

    .sidebar {
      position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width);
      background: linear-gradient(180deg, var(--dark-tertiary) 0%, var(--dark-primary) 100%);
      border-radius: 0 var(--radius-xl) var(--radius-xl) 0;
      padding: 22px; display: flex; flex-direction: column; justify-content: space-between;
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.4);
      border-right: 1px solid rgba(255, 255, 255, 0.05); z-index: 50;
      transition: transform var(--transition-base);
    }

    .brand { font-weight: 800; color: var(--pink-primary); font-size: 22px; padding-bottom: 8px; letter-spacing: -0.5px; }

    .nav { display: flex; flex-direction: column; gap: 8px; margin-top: 24px; overflow-y: auto; overflow-x: hidden; max-height: calc(100vh - 200px); padding-right: 4px; }
    .nav::-webkit-scrollbar { width: 4px; }
    .nav::-webkit-scrollbar-track { background: transparent; }
    .nav::-webkit-scrollbar-thumb { background: var(--gray-medium); border-radius: 4px; }

    .nav-item {
      display: flex; align-items: center; gap: 12px; padding: 12px 14px;
      border-radius: var(--radius-md); background: transparent; color: var(--gray-light);
      border: none; cursor: pointer; width: 100%; transition: all var(--transition-fast);
      text-align: left; font-family: var(--font-family); font-size: 15px;
      text-decoration: none;
    }

    .nav-item:hover { background: rgba(250, 193, 217, 0.08); color: var(--pink-light); transform: translateX(4px); }
    .nav-item.active {
      background: linear-gradient(135deg, rgba(250, 193, 217, 0.15), rgba(250, 193, 217, 0.08));
      color: var(--pink-primary); box-shadow: 0 4px 16px rgba(250, 193, 217, 0.1);
    }

    .nav-item .icon-wrapper { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nav-item svg { width: 20px; height: 20px; stroke: currentColor; transition: transform var(--transition-fast); }
    .nav-item:hover svg { transform: scale(1.1); }
    .nav-item.active svg { stroke: var(--pink-primary); }
    .nav-item .label { font-weight: 600; font-size: 15px; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .sidebar-bottom { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.08); }
    .logout-btn {
      width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(250, 193, 217, 0.08); cursor: pointer; display: flex; align-items: center; justify-content: center;
      transition: all var(--transition-fast);
    }
    .logout-btn svg { width: 20px; height: 20px; stroke: var(--gray-light); transition: stroke var(--transition-fast); }
    .logout-btn:hover { background: var(--red-accent); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(231, 0, 0, 0.3); }
    .logout-btn:hover svg { stroke: var(--white); }

    .main { margin-left: var(--sidebar-width); padding: 20px; flex: 1; min-height: 100vh; transition: margin-left var(--transition-base); width: calc(100% - var(--sidebar-width)); }
    .main-inner { max-width: 1400px; margin: 0 auto; height: 100%; display: flex; flex-direction: column; }

    .topbar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 16px 20px; margin-bottom: 24px;
      background: var(--dark-primary); border-radius: var(--radius-lg);
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2); flex-shrink: 0;
    }
    .title-wrap { display: flex; align-items: center; gap: 16px; }
    .menu-toggle {
      display: none; width: 40px; height: 40px; border: none; background: rgba(250, 193, 217, 0.08);
      border-radius: var(--radius-sm); cursor: pointer; align-items: center; justify-content: center;
      transition: all var(--transition-fast);
    }
    .menu-toggle svg { width: 24px; height: 24px; stroke: var(--pink-primary); }
    .menu-toggle:hover { background: rgba(250, 193, 217, 0.15); }
    .title { font-size: 24px; font-weight: 700; color: var(--white); letter-spacing: -0.5px; }
    .user { display: flex; align-items: center; gap: 12px; }
    .user-text { font-size: 14px; font-weight: 600; color: var(--gray-light); letter-spacing: 0.5px; }
    .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--pink-primary); }

    .content {
      background: var(--dark-primary); border-radius: var(--radius-lg); padding: 24px;
      flex: 1; display: flex; flex-direction: column; overflow: hidden;
    }

    .res-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    .res-tabs { display: flex; gap: 15px; }
    .res-tab { 
        background: transparent; border: none; color: var(--gray-medium); 
        font-family: var(--font-family); font-size: 14px; font-weight: 600; 
        cursor: pointer; padding: 8px 16px; border-radius: var(--radius-sm); transition: 0.2s;
    }
    .res-tab:hover { background: rgba(255,255,255,0.05); color: var(--white); }
    .res-tab.active { background: var(--pink-primary); color: var(--black); }

    .res-actions { display: flex; gap: 15px; align-items: center; }
    
    .custom-select, .date-input {
        background: var(--dark-secondary); color: var(--white); border: 1px solid rgba(255, 255, 255, 0.1); 
        padding: 10px 16px; border-radius: var(--radius-sm); outline: none; cursor: pointer; font-family: var(--font-family);
    }
    
    .btn-add {
        background: var(--pink-primary); color: var(--black); 
        font-weight: 700; border: none; padding: 10px 20px; 
        border-radius: var(--radius-sm); cursor: pointer; font-family: var(--font-family);
        transition: 0.2s;
    }
    .btn-add:hover { opacity: 0.9; transform: translateY(-1px); }

    .grid-wrapper {
        flex: 1; overflow: auto;
        border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-md);
        background: rgba(0,0,0,0.2); position: relative;
    }
    .grid-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
    .grid-wrapper::-webkit-scrollbar-track { background: transparent; }
    .grid-wrapper::-webkit-scrollbar-thumb { background: var(--gray-medium); border-radius: 3px; }

    .grid-table {
        display: grid;
        grid-template-columns: 100px repeat(<?php echo count($hours); ?>, 110px);
        width: max-content;
    }

    .header-cell {
        position: sticky; top: 0; z-index: 10;
        background: var(--dark-secondary);
        height: 50px; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; color: var(--gray-light);
        border-bottom: 1px solid rgba(255,255,255,0.05); border-right: 1px solid rgba(255,255,255,0.05);
    }
    .header-cell.corner { position: sticky; left: 0; z-index: 20; background: var(--dark-secondary); }

    .row-label {
        position: sticky; left: 0; z-index: 5; background: var(--dark-secondary);
        height: 80px; display: flex; align-items: center; padding-left: 20px;
        font-weight: 700; color: var(--white); font-size: 14px;
        border-bottom: 1px solid rgba(255,255,255,0.05); border-right: 1px solid rgba(255,255,255,0.05);
    }

    .cell {
        height: 80px; padding: 5px;
        border-bottom: 1px solid rgba(255,255,255,0.05); border-right: 1px solid rgba(255,255,255,0.05);
        position: relative;
    }
    .cell:hover { background: rgba(255,255,255,0.03); cursor: pointer; }
    .cell.empty:hover::after {
        content: '+'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        font-size: 32px; color: var(--pink-primary); opacity: 0.5;
    }

    .res-block {
        width: 100%; height: 100%; border-radius: 6px; padding: 8px;
        display: flex; flex-direction: column; justify-content: center;
        cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        position: relative;
    }
    .res-block:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
    
    .res-name { font-size: 12px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .res-pax { font-size: 11px; opacity: 0.9; color: #fff; font-weight: 600; margin-top: 2px; }

    .st-pending { background: linear-gradient(135deg, #f39c12, #d35400); color: #fff; }
    .st-confirmed { background: linear-gradient(135deg, #17b79a, #117a65); color: #fff; }
    .st-seated { background: linear-gradient(135deg, #3498db, #2980b9); color: #fff; }
    .st-cancelled { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: #fff; }

    .modal {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.8); z-index: 1000; align-items: center; justify-content: center;
        animation: fadeIn 0.2s ease;
    }
    .modal.active { display: flex; }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: scale(0.85);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    .modal-content {
        background: var(--dark-primary); border-radius: var(--radius-lg);
        padding: 30px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
    }
    
    .modal-title {
        font-size: 20px; font-weight: 700; color: var(--white);
    }
    
    .modal-close {
        background: transparent; border: none; color: var(--gray-light);
        cursor: pointer; font-size: 24px; width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; transition: 0.2s;
    }
    .modal-close:hover { background: rgba(255, 255, 255, 0.1); color: var(--white); }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-label {
        display: block; margin-bottom: 6px; font-weight: 600; color: var(--gray-light); font-size: 14px;
    }
    .form-label .required {
        color: var(--red-accent); margin-left: 2px;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 10px 14px; background: var(--dark-secondary);
        border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius-sm);
        color: var(--white); font-family: var(--font-family); font-size: 14px;
        outline: none; transition: 0.2s;
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--pink-primary);
        background: var(--dark-tertiary);
    }
    
    .form-textarea {
        resize: vertical; min-height: 80px;
    }
    
    .form-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
    }
    
    .modal-actions {
        display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;
    }
    
    .btn {
        padding: 10px 20px; border: none; border-radius: var(--radius-sm);
        cursor: pointer; font-family: var(--font-family); font-weight: 600;
        transition: 0.2s; font-size: 14px;
    }
    
    .btn-primary {
        background: var(--pink-primary); color: var(--black);
    }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
    
    .btn-secondary {
        background: var(--dark-secondary); color: var(--white); border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .btn-secondary:hover { background: var(--dark-tertiary); }
    
    .btn-danger {
        background: var(--red-accent); color: var(--white);
    }
    .btn-danger:hover { opacity: 0.9; transform: translateY(-1px); }
    
    .status-badge {
        display: inline-block; padding: 4px 10px; border-radius: 12px;
        font-size: 11px; font-weight: 700; margin-top: 8px; text-transform: uppercase;
    }
    
    .detail-row {
        padding: 12px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex; justify-content: space-between; align-items: center;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--gray-light); font-weight: 600; font-size: 14px; }
    .detail-value { color: var(--white); font-weight: 500; text-align: right; }

    .toast {
        position: fixed; top: 20px; right: 20px; z-index: 2000;
        background: var(--dark-primary); color: var(--white);
        padding: 16px 24px; border-radius: var(--radius-md);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        display: none; align-items: center; gap: 12px;
        animation: slideInRight 0.3s ease;
        min-width: 300px;
    }
    .toast.active { display: flex; }
    .toast.success { border-left: 4px solid #17b79a; }
    .toast.error { border-left: 4px solid #e74c3c; }

    @keyframes slideInRight {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .toast-icon {
        font-size: 20px;
    }

    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.active { transform: translateX(0); border-radius: 0 var(--radius-xl) var(--radius-xl) 0; }
      .main { margin-left: 0; width: 100%; }
      .menu-toggle { display: flex; }
      .topbar { padding: 12px 16px; }
      .title { font-size: 20px; }
      .user-text { display: none; }
      .content { padding: 16px; }
      .form-row { grid-template-columns: 1fr; }
      .res-toolbar { flex-direction: column; align-items: stretch; }
      .res-actions { flex-direction: column; }
      .toast { right: 10px; left: 10px; min-width: auto; }
    }
  </style>
</head>
<body>
  <div class="app">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>
        <nav class="nav">
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg></span><span class="label">Dashboard</span>
          </a>
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><span class="label">User Management</span>
          </a>
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/></svg></span><span class="label">Inventory</span>
          </a>
          
          <a href="#" class="nav-item active">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 14v2.2l1.6 1"/><path d="M16 2v4"/><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M3 10h5"/><path d="M8 2v4"/><circle cx="16" cy="16" r="6"/></svg></span><span class="label">Reservation</span>
          </a>
          
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg></span><span class="label">Transaction</span>
          </a>
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 14v2.2l1.6 1"/><path d="M16 4h2a2 2 0 0 1 2 2v.832"/><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"/><circle cx="16" cy="16" r="6"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></span><span class="label">Activity Log</span>
          </a>
        </nav>
      </div>
      <div class="sidebar-bottom">
        <button class="logout-btn" id="logoutBtn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg></button>
      </div>
    </aside>

    <main class="main">
      <div class="main-inner">
        <header class="topbar">
          <div class="title-wrap">
            <button class="menu-toggle" id="menuToggle"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
            <h1 class="title">Reservation Management</h1>
          </div>
          <div class="user">
            <div class="user-text">ADMIN</div>
            <img class="avatar" src="https://i.pravatar.cc/40?img=5" alt="Admin avatar">
          </div>
        </header>

        <div class="content">
            <div class="res-toolbar">
                <div class="res-tabs">
                    <button class="res-tab <?php echo $selectedFloor == '1' ? 'active' : ''; ?>" onclick="changeFloor('1')">1st Floor</button>
                    <button class="res-tab <?php echo $selectedFloor == '2' ? 'active' : ''; ?>" onclick="changeFloor('2')">2nd Floor</button>
                    <button class="res-tab <?php echo $selectedFloor == '3' ? 'active' : ''; ?>" onclick="changeFloor('3')">3rd Floor</button>
                </div>
                <div class="res-actions">
                    <input type="date" class="date-input" id="dateFilter" value="<?php echo $selectedDate; ?>">
                    <button class="btn-add" onclick="openAddModal()">+ Add Reservation</button>
                </div>
            </div>

            <div class="grid-wrapper">
                <div class="grid-table">
                    <div class="header-cell corner">TABLE</div>
                    <?php foreach($hours as $h): ?>
                        <div class="header-cell"><?php echo str_pad($h, 2, '0', STR_PAD_LEFT).':00'; ?></div>
                    <?php endforeach; ?>

                    <?php foreach($floor_tables as $table): ?>
                        <div class="row-label"><?php echo $table; ?></div>
                        
                        <?php foreach($hours as $h): ?>
                            <?php $res = getReservation($table, $h, $reservations); ?>
                            <div class="cell <?php echo $res ? '' : 'empty'; ?>" 
                                 onclick="<?php echo $res ? "openDetailModal({$res['id']})" : "openBookingModal('$table', $h)"; ?>">
                                <?php if($res): ?>
                                    <div class="res-block st-<?php echo $res['status']; ?>">
                                        <div class="res-name"><?php echo htmlspecialchars($res['name']); ?></div>
                                        <div class="res-pax">👥 <?php echo $res['guests']; ?> guests</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
      </div>
    </main>
  </div>

  <div class="toast" id="toast">
    <span class="toast-icon" id="toastIcon"></span>
    <span id="toastMessage"></span>
  </div>

  <!-- ADD MODAL -->
  <div class="modal" id="addModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Add New Reservation</h2>
        <button class="modal-close" onclick="closeAddModal()">&times;</button>
      </div>
      
      <!-- INFO BOX -->
      <div style="background: rgba(250, 193, 217, 0.1); border: 1px solid var(--pink-primary); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
          <svg style="width: 20px; height: 20px; stroke: var(--pink-primary);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <strong style="color: var(--pink-primary); font-size: 14px;">Reservation Info</strong>
        </div>
        <div id="bookingInfo" style="color: var(--gray-light); font-size: 13px; line-height: 1.6;">
          Please fill in all required fields below
        </div>
      </div>
      
      <form id="addForm">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Floor <span class="required">*</span></label>
            <select class="form-select" id="addFloor" onchange="updateFloorTables()" required>
              <option value="1">1st Floor</option>
              <option value="2">2nd Floor</option>
              <option value="3">3rd Floor</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Table <span class="required">*</span></label>
            <select class="form-select" id="addTable" onchange="updateTableInfo()" required>
              <?php foreach($rooms as $room): ?>
                <option value="<?php echo $room['id_reservation_room']; ?>" data-seats="<?php echo $room['seats']; ?>">
                  Table <?php echo $room['id_reservation_room']; ?> (<?php echo $room['seats']; ?> seats)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Date <span class="required">*</span></label>
            <input type="date" class="form-input" id="addDate" value="<?php echo $selectedDate; ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Hour <span class="required">*</span></label>
            <select class="form-select" id="addHour" onchange="updateBookingInfo()" required>
              <?php foreach($hours as $h): ?>
                <option value="<?php echo $h; ?>"><?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?>:00</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Guest Name <span class="required">*</span></label>
          <input type="text" class="form-input" id="addName" placeholder="Enter guest name" required>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone <span class="required">*</span></label>
            <input type="tel" class="form-input" id="addPhone" placeholder="08123456789" required>
          </div>
          <div class="form-group">
            <label class="form-label">Guests <span class="required">*</span></label>
            <input type="number" class="form-input" id="addGuests" min="1" max="20" value="2" onchange="validateSeats()" required>
            <small id="seatsWarning" style="color: var(--red-accent); font-size: 12px; display: none; margin-top: 4px;"></small>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Email (Optional)</label>
          <input type="email" class="form-input" id="addEmail" placeholder="guest@email.com">
        </div>
        
        <div class="form-group">
          <label class="form-label">Notes (Optional)</label>
          <textarea class="form-textarea" id="addNotes" placeholder="Special requests or notes..."></textarea>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitReservation">Add Reservation</button>
        </div>
      </form>
    </div>
  </div>

  <!-- DETAIL MODAL -->
  <div class="modal" id="detailModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Reservation Details</h2>
        <button class="modal-close" onclick="closeDetailModal()">&times;</button>
      </div>
      <div id="detailContent">
        <p style="text-align: center; color: var(--gray-medium);">Loading...</p>
      </div>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Edit Reservation</h2>
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
      </div>
      <form id="editForm">
        <input type="hidden" id="editId">
        
        <div class="form-group">
          <label class="form-label">Guest Name <span class="required">*</span></label>
          <input type="text" class="form-input" id="editName" required>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone <span class="required">*</span></label>
            <input type="tel" class="form-input" id="editPhone" required>
          </div>
          <div class="form-group">
            <label class="form-label">Guests <span class="required">*</span></label>
            <input type="number" class="form-input" id="editGuests" min="1" max="20" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" id="editEmail">
        </div>
        
        <div class="form-group">
          <label class="form-label">Status <span class="required">*</span></label>
          <select class="form-select" id="editStatus" required>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="seated">Seated</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea class="form-textarea" id="editNotes"></textarea>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const HOURS = <?php echo json_encode($hours); ?>;
    const FLOOR_TABLES = <?php echo json_encode($floor_tables); ?>;
    const ALL_ROOMS = <?php echo json_encode($rooms); ?>;
    const FLOOR_DATA = <?php echo json_encode($floor_data); ?>;
    
    console.log('🎯 Bitehive Reservation System');
    console.log('Current Floor:', '<?php echo $selectedFloor; ?>');
    console.log('Current Date:', '<?php echo $selectedDate; ?>');
    console.log('Total Reservations:', <?php echo count($reservations); ?>);
    
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const logoutBtn = document.getElementById('logoutBtn');

    function toggleSidebar() {
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
    }

    menuToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    logoutBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to log out?')) {
        window.location.href = 'logout.php';
      }
    });

    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
      }
    });

    document.getElementById('dateFilter').addEventListener('change', function() {
      const currentFloor = new URLSearchParams(window.location.search).get('floor') || '1';
      window.location.href = '?date=' + this.value + '&floor=' + currentFloor;
    });

    function changeFloor(floor) {
      const currentDate = document.getElementById('dateFilter').value;
      // Set cookie
      document.cookie = `selected_floor=${floor}; path=/; max-age=3600`;
      window.location.href = '?date=' + currentDate + '&floor=' + floor;
    }

    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      const toastMessage = document.getElementById('toastMessage');
      const toastIcon = document.getElementById('toastIcon');
      
      toastMessage.textContent = message;
      toastIcon.textContent = type === 'success' ? '✓' : '✕';
      toast.className = 'toast active ' + type;
      
      setTimeout(() => {
        toast.classList.remove('active');
      }, 4000);
    }

    function openAddModal() {
      document.getElementById('addModal').classList.add('active');
      // Set current floor as default
      document.getElementById('addFloor').value = '<?php echo $selectedFloor; ?>';
      updateFloorTables();
      updateBookingInfo();
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.remove('active');
      document.getElementById('addForm').reset();
      document.getElementById('seatsWarning').style.display = 'none';
    }

    function openBookingModal(table, hour) {
      console.log('📝 Opening booking modal for:', table, 'at', hour);
      openAddModal();
      
      // Detect which floor this table belongs to
      let tableFloor = '1';
      for (let floor in FLOOR_DATA) {
        if (FLOOR_DATA[floor].includes(table)) {
          tableFloor = floor;
          break;
        }
      }
      
      document.getElementById('addFloor').value = tableFloor;
      updateFloorTables();
      document.getElementById('addTable').value = table;
      document.getElementById('addHour').value = hour;
      updateBookingInfo();
      updateTableInfo();
    }
    
    // Update tables based on selected floor
    function updateFloorTables() {
      const floor = document.getElementById('addFloor').value;
      const tableSelect = document.getElementById('addTable');
      const tables = FLOOR_DATA[floor];
      
      tableSelect.innerHTML = '';
      
      ALL_ROOMS.forEach(room => {
        if (tables.includes(room.id_reservation_room)) {
          const option = document.createElement('option');
          option.value = room.id_reservation_room;
          option.setAttribute('data-seats', room.seats);
          option.textContent = `Table ${room.id_reservation_room} (${room.seats} seats)`;
          tableSelect.appendChild(option);
        }
      });
      
      updateBookingInfo();
      validateSeats();
    }
    
    // Update booking info box
    function updateBookingInfo() {
      const floor = document.getElementById('addFloor').value;
      const table = document.getElementById('addTable').value;
      const hour = document.getElementById('addHour').value;
      const date = document.getElementById('addDate').value;
      
      const tableOption = document.getElementById('addTable').selectedOptions[0];
      const seats = tableOption ? tableOption.getAttribute('data-seats') : 0;
      
      const floorNames = {'1': '1st Floor', '2': '2nd Floor', '3': '3rd Floor'};
      
      document.getElementById('bookingInfo').innerHTML = `
        <strong style="color: var(--pink-primary);">📍 ${floorNames[floor]}</strong> - Table <strong>${table}</strong> (Capacity: ${seats} seats)<br>
        📅 ${date} at <strong>${String(hour).padStart(2, '0')}:00</strong> (2 hours reservation)
      `;
    }
    
    // Update table info when table changes
    function updateTableInfo() {
      updateBookingInfo();
      validateSeats();
    }
    
    // Validate number of guests vs table capacity
    function validateSeats() {
      const tableOption = document.getElementById('addTable').selectedOptions[0];
      const tableSeats = parseInt(tableOption.getAttribute('data-seats'));
      const guests = parseInt(document.getElementById('addGuests').value) || 0;
      const warning = document.getElementById('seatsWarning');
      const submitBtn = document.getElementById('submitReservation');
      
      if (guests > tableSeats) {
        warning.textContent = `⚠️ This table only has ${tableSeats} seats! Please select a larger table or reduce guests.`;
        warning.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
      } else {
        warning.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
      }
    }

    function closeDetailModal() {
      document.getElementById('detailModal').classList.remove('active');
    }

    function openEditModal() {
      closeDetailModal();
      document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('active');
      document.getElementById('editForm').reset();
    }

    // ADD RESERVATION FORM
    document.getElementById('addForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      console.log('🚀 Submitting reservation...');
      
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Adding...';
      submitBtn.disabled = true;
      
      const formData = new FormData();
      formData.append('action', 'add');
      formData.append('table', document.getElementById('addTable').value);
      formData.append('hour', document.getElementById('addHour').value);
      formData.append('name', document.getElementById('addName').value);
      formData.append('phone', document.getElementById('addPhone').value);
      formData.append('guests', document.getElementById('addGuests').value);
      formData.append('email', document.getElementById('addEmail').value);
      formData.append('date', document.getElementById('addDate').value);
      formData.append('notes', document.getElementById('addNotes').value);
      
      try {
        const response = await fetch('', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        console.log('📥 Server response:', result);
        
        if (result.success) {
          showToast(result.message, 'success');
          closeAddModal();
          
          console.log('✅ Calling addReservationToGrid with:', result.data);
          addReservationToGrid(result.data);
          
        } else {
          showToast(result.message, 'error');
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      } catch (error) {
        console.error('❌ Error:', error);
        showToast('Error: ' + error.message, 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });

    function addReservationToGrid(data) {
      console.log('🔄 Adding reservation to grid:', data);
      
      const table = data.table;
      const hour = parseInt(data.hour);
      
      const tableIndex = FLOOR_TABLES.indexOf(table);
      
      if (tableIndex === -1) {
        console.warn(`⚠️ Table ${table} not in current floor`);
        showToast('✅ Reservation added! Switch to the correct floor to see it.', 'success');
        setTimeout(() => location.reload(), 2000);
        return;
      }
      
      const hourIndex = HOURS.indexOf(hour);
      
      if (hourIndex === -1) {
        console.error(`❌ Hour ${hour} not found in hours array`);
        setTimeout(() => location.reload(), 2000);
        return;
      }
      
      const allCells = document.querySelectorAll('.cell');
      console.log('📊 Total cells:', allCells.length);
      
      const cellIndex = (tableIndex * HOURS.length) + hourIndex;
      console.log('🎯 Target cell index:', cellIndex);
      
      const targetCell = allCells[cellIndex];
      
      if (!targetCell) {
        console.error(`❌ Cell not found at index ${cellIndex}`);
        setTimeout(() => location.reload(), 2000);
        return;
      }
      
      console.log('✅ Target cell found:', targetCell);
      
      targetCell.classList.remove('empty');
      targetCell.setAttribute('onclick', `openDetailModal(${data.id})`);
      
      const statusClass = {
        'pending': 'st-pending',
        'confirmed': 'st-confirmed',
        'seated': 'st-seated',
        'cancelled': 'st-cancelled'
      }[data.status] || 'st-pending';
      
      targetCell.innerHTML = `
        <div class="res-block ${statusClass}" style="animation: slideIn 0.4s ease;">
          <div class="res-name">${escapeHtml(data.name)}</div>
          <div class="res-pax">👥 ${data.guests} guest${data.guests > 1 ? 's' : ''}</div>
        </div>
      `;
      
      targetCell.style.boxShadow = '0 0 0 3px rgba(250, 193, 217, 0.5)';
      setTimeout(() => {
        targetCell.style.boxShadow = '';
      }, 1500);
      
      console.log('✅ Grid updated successfully!');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // VIEW DETAIL RESERVATION
    async function openDetailModal(id) {
      document.getElementById('detailModal').classList.add('active');
      document.getElementById('detailContent').innerHTML = '<p style="text-align: center; color: var(--gray-medium);">Loading...</p>';
      
      const formData = new FormData();
      formData.append('action', 'get');
      formData.append('id', id);
      
      try {
        const response = await fetch('', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          const data = result.data;
          const statusClass = data.status === 'pending' ? 'st-pending' : 
                             data.status === 'confirmed' ? 'st-confirmed' : 
                             data.status === 'seated' ? 'st-seated' : 'st-cancelled';
          
          const statusText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
          
          document.getElementById('detailContent').innerHTML = `'
            <div class="detail-row">
              <span class="detail-label">Guest Name:</span>
              <span class="detail-value">${data.name}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Phone:</span>
              <span class="detail-value">${data.phone}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Email:</span>
              <span class="detail-value">${data.email || '-'}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Table:</span>
              <span class="detail-value">Table ${data.table}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Date & Time:</span>
              <span class="detail-value">${data.date} at ${String(data.hour).padStart(2, '0')}:00</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Guests:</span>
              <span class="detail-value">${data.guests} people</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Status:</span>
              <span class="detail-value">
                <span class="status-badge ${statusClass}">${statusText}</span>
              </span>
            </div>
            <div class="modal-actions" style="margin-top: 24px;">
              <button class="btn btn-danger" onclick="deleteReservation(${data.id})">Delete</button>
              <button class="btn btn-secondary" onclick="loadEditModal(${data.id})">Edit</button>
              <button class="btn btn-primary" onclick="closeDetailModal()">Close</button>
            </div>
          `;
        } else {
          document.getElementById('detailContent').innerHTML = `<p style="text-align: center; color: var(--red-accent);">${result.message}</p>`;
        }
      } catch (error) {
        showToast('Error loading details', 'error');
        document.getElementById('detailContent').innerHTML = '<p style="text-align: center; color: var(--red-accent);">Failed to load reservation details</p>';
      }
    }

    // LOAD EDIT MODAL
    async function loadEditModal(id) {
      const formData = new FormData();
      formData.append('action', 'get');
      formData.append('id', id);
      
      try {
        const response = await fetch('', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          const data = result.data;
          document.getElementById('editId').value = data.id;
          document.getElementById('editName').value = data.name;
          document.getElementById('editPhone').value = data.phone;
          document.getElementById('editEmail').value = data.email || '';
          document.getElementById('editGuests').value = data.guests;
          document.getElementById('editStatus').value = data.status;
          document.getElementById('editNotes').value = data.notes || '';
          
          openEditModal();
        } else {
          showToast(result.message, 'error');
        }
      } catch (error) {
        showToast('Error loading edit form', 'error');
      }
    }

    // UPDATE RESERVATION
    document.getElementById('editForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving...';
      submitBtn.disabled = true;
      
      const formData = new FormData();
      formData.append('action', 'update');
      formData.append('id', document.getElementById('editId').value);
      formData.append('name', document.getElementById('editName').value);
      formData.append('phone', document.getElementById('editPhone').value);
      formData.append('email', document.getElementById('editEmail').value);
      formData.append('guests', document.getElementById('editGuests').value);
      formData.append('status', document.getElementById('editStatus').value);
      formData.append('notes', document.getElementById('editNotes').value);
      
      try {
        const response = await fetch('', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showToast(result.message, 'success');
          closeEditModal();
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(result.message, 'error');
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      } catch (error) {
        showToast('Error: ' + error.message, 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });

    // DELETE RESERVATION
    async function deleteReservation(id) {
      if (!confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) return;
      
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);
      
      try {
        const response = await fetch('', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showToast(result.message, 'success');
          closeDetailModal();
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(result.message, 'error');
        }
      } catch (error) {
        showToast('Error: ' + error.message, 'error');
      }
    }

    // KEYBOARD SHORTCUTS
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeAddModal();
        closeDetailModal();
        closeEditModal();
      }
    });
    
    console.log('✅ Bitehive Reservation System Ready!');
  </script>
</body>
</html>