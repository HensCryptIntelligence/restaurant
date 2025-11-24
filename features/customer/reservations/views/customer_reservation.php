<?php
session_start();

// Simulasi Login Customer
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Budi Santoso';
    $_SESSION['user_role'] = 'customer';
}

// Koneksi Database
$host = 'localhost';
$dbname = 'restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ DATABASE ERROR: " . $e->getMessage());
}

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
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
                $reservations = $stmt->fetchAll();
                $stmt->closeCursor();
                
                $transformedData = [];
                foreach ($reservations as $res) {
                    $transformedData[] = [
                        'id' => $res['id_reservation'],
                        'table' => $res['id_reservation_room'],
                        'date' => date('Y-m-d', strtotime($res['reservation_date'])),
                        'time' => date('H:i', strtotime($res['reservation_start'])),
                        'guests' => $res['seats'],
                        'phone' => $res['phone_number'],
                        'name' => $res['display_name'],
                        'status' => $res['status']
                    ];
                }
                
                $response['success'] = true;
                $response['data'] = $transformedData;
                break;
                
            case 'add_reservation':
                if (empty($_POST['table']) || empty($_POST['hour']) || 
                    empty($_POST['phone']) || empty($_POST['guests']) || 
                    empty($_POST['date']) || empty($_POST['name'])) {
                    throw new Exception('Semua field wajib diisi!');
                }
                
                $table = $_POST['table'];
                $hour = (int)$_POST['hour'];
                $phone = trim($_POST['phone']);
                $guests = (int)$_POST['guests'];
                $date = $_POST['date'];
                $customer_name = trim($_POST['name']);
                $email = trim($_POST['email'] ?? '');
                
                if ($date < date('Y-m-d')) {
                    throw new Exception('Tidak dapat memesan untuk tanggal yang sudah lewat!');
                }
                
                $stmt = $pdo->prepare("SELECT seats FROM reservation_rooms WHERE id_reservation_room = :table");
                $stmt->execute(['table' => $table]);
                $roomData = $stmt->fetch();
                $stmt->closeCursor();
                
                if (!$roomData) {
                    throw new Exception('Meja tidak ditemukan!');
                }
                
                if ($guests > $roomData['seats']) {
                    throw new Exception("Meja ini hanya memiliki {$roomData['seats']} kursi!");
                }
                
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM reservation 
                    WHERE id_reservation_room = :table 
                    AND DATE(reservation_date) = :date 
                    AND HOUR(reservation_start) = :hour
                    AND status != 'cancelled'
                ");
                $stmt->execute(['table' => $table, 'date' => $date, 'hour' => $hour]);
                $isBooked = $stmt->fetchColumn() > 0;
                $stmt->closeCursor();
                
                if ($isBooked) {
                    throw new Exception('Slot waktu ini sudah dipesan!');
                }
                
                $reservationStart = $date . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                $stmt = $pdo->prepare("
                    INSERT INTO reservation (
                        id_user, customer_name, id_reservation_room, seats, 
                        reservation_start, reservation_time, reservation_date,
                        phone_number, email_address, status, created_at
                    ) VALUES (
                        :id_user, :customer_name, :id_room, :seats,
                        :res_start, 120, :res_date,
                        :phone, :email, 'pending', NOW()
                    )
                ");
                
                $stmt->execute([
                    'id_user' => $_SESSION['user_id'],
                    'customer_name' => $customer_name,
                    'id_room' => $table,
                    'seats' => $guests,
                    'res_start' => $reservationStart,
                    'res_date' => $date,
                    'phone' => $phone,
                    'email' => $email
                ]);
                $stmt->closeCursor();
                
                $response['success'] = true;
                $response['message'] = 'Reservasi berhasil! Menunggu konfirmasi admin.';
                break;
                
            case 'cancel_reservation':
                $id = (int)$_POST['id'];
                
                $stmt = $pdo->prepare("
                    SELECT id_reservation, status 
                    FROM reservation 
                    WHERE id_reservation = :id 
                    AND id_user = :user_id
                ");
                $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
                $reservation = $stmt->fetch();
                $stmt->closeCursor();
                
                if (!$reservation) {
                    throw new Exception('Reservasi tidak ditemukan!');
                }
                
                if ($reservation['status'] === 'cancelled') {
                    throw new Exception('Reservasi sudah dibatalkan!');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE reservation 
                    SET status = 'cancelled', updated_at = NOW()
                    WHERE id_reservation = :id
                ");
                $stmt->execute(['id' => $id]);
                $stmt->closeCursor();
                
                $response['success'] = true;
                $response['message'] = 'Reservasi berhasil dibatalkan!';
                break;
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

$floor_data = [
    '1' => ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'],
    '2' => ['D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2'],
    '3' => ['G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2']
];

$hours = range(10, 22);
$stmt = $pdo->query("SELECT id_reservation_room, seats FROM reservation_rooms");
$rooms = $stmt->fetchAll();
$stmt->closeCursor();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Bitehive — My Reservations</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --sidebar-w: 260px;
      --bg: #0b0c0d;
      --panel: #161818;
      --muted: #9aa0a2;
      --accent: #17b79a;
      --text: #e6f0ef;
      --sub: #aeb7b6;
      --radius: 16px;
      --orange: #f39c12;
      --green: #17b79a;
      --blue: #3498db;
      --gray: #95a5a6;
      
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      font-size: 15px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; background: linear-gradient(180deg, #0b0c0d 0%, #0c0d0e 100%); color: var(--text); -webkit-font-smoothing: antialiased; overflow: hidden; }
    .app { min-height: 100vh; display: flex; }

     
    :root {
      --cyan-primary: #17C3B2;
      --white: #FFFFFF;
      --dark-primary: #3D4142;
      --dark-secondary: #292C2D;
      --dark-tertiary: #333333;
      --gray-medium: #ADADAD;
      --red-accent: #E70000;
      --pink-light: #47e7d7;
      --pink-medium: #50ecdd;
      --black: #111315;
      --gray-light: #D9D9D9;
      
      /* Sidebar */
      --sidebar-width: 260px;
      --sidebar-collapsed: 70px;
      
      /* Typography */
      --font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
      --font-size-base: 15px;
      
      /* Border Radius */
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 18px;
      
      /* Transitions */
      --transition-fast: 0.15s ease;
      --transition-base: 0.3s ease;
    }

    /* Reset */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

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

    /* App Container */
    .app {
      min-height: 100vh;
      display: flex;
      position: relative;
    }

    /* Sidebar Overlay (Mobile) */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      z-index: 40;
      opacity: 0;
      transition: opacity var(--transition-base);
    }

    .sidebar-overlay.active {
      display: block;
      opacity: 1;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: var(--sidebar-width);
      background: linear-gradient(180deg, var(--dark-tertiary) 0%, var(--dark-primary) 100%);
      border-radius: 0 var(--radius-xl) var(--radius-xl) 0;
      padding: 22px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 4px 0 24px rgba(0, 0, 0, 0.4);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
      z-index: 50;
      transition: transform var(--transition-base);
    }

    /* Brand */
    .brand {
      font-weight: 800;
      color: var(--cyan-primary);
      font-size: 22px;
      padding-bottom: 8px;
      letter-spacing: -0.5px;
    }

    /* Navigation */
    .nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 24px;
      overflow-y: auto;
      overflow-x: hidden;
      max-height: calc(100vh - 200px);
      padding-right: 4px;
    }

    .nav-link-wrapper {
      text-decoration: none;
    }

    .nav::-webkit-scrollbar {
      width: 4px;
    }

    .nav::-webkit-scrollbar-track {
      background: transparent;
    }

    .nav::-webkit-scrollbar-thumb {
      background: var(--gray-medium);
      border-radius: 4px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border-radius: var(--radius-md);
      background: transparent;
      color: var(--gray-light);
      border: none;
      cursor: pointer;
      width: 100%;
      transition: all var(--transition-fast);
      text-align: left;
      font-family: var(--font-family);
      font-size: 15px;
    }
    .nav-item:hover {
      background: rgba(23, 195, 178, 0.08);
      color: var(--cyan-primary);
      transform: translateX(4px);
    }

    .nav-item.active {
      background: linear-gradient(135deg, rgba(23, 195, 178, 0.15), rgba(23, 195, 178, 0.08));
      color: var(--cyan-primary);
    }

    .nav-item .icon-wrapper {
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .nav-item svg {
      width: 20px;
      height: 20px;
      stroke: currentColor;
      transition: transform var(--transition-fast);
    }

    .nav-item:hover svg {
      transform: scale(1.1);
    }

    .nav-item.active svg {
      stroke: var(--cyan-primary);
    }

    .nav-item .label {
      font-weight: 600;
      font-size: 15px;
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Sidebar Bottom */
    .sidebar-bottom {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding-top: 16px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .logout-btn {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(23, 195, 178, 0.08);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all var(--transition-fast);
    }

    .logout-btn svg {
      width: 20px;
      height: 20px;
      stroke: var(--gray-light);
      transition: stroke var(--transition-fast);
    }

    .logout-btn:hover {
      background: var(--red-accent);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(231, 0, 0, 0.3);
    }

    .logout-btn:hover svg {
      stroke: var(--white);
    }

    /* Main Content */
    .main {
      margin-left: 275px;
      padding: 20px;
      flex: 1;
      min-height: 100vh;
      transition: margin-left var(--transition-base);
      width: calc(100% - 295px);
    }

    .main-inner {
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Topbar */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      border-radius: var(--radius-md);
    }

    .title-wrap {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .menu-toggle {
      display: none;
      width: 40px;
      height: 40px;
      border: none;
      background: rgba(23, 195, 178, 0.08);
      border-radius: var(--radius-sm);
      cursor: pointer;
      align-items: center;
      justify-content: center;
      transition: all var(--transition-fast);
    }

    .menu-toggle svg {
      width: 24px;
      height: 24px;
      stroke: var(--cyan-primary);
    }

    .menu-toggle:hover {
      background: rgba(23, 195, 178, 0.15);
    }

    .title {
      font-size: 24px;
      font-weight: 700;
      color: var(--white);
      letter-spacing: -0.5px;
    }

    .user {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-text {
      font-size: 16px;
      font-weight: 700;
      color: var(--gray-light);
      letter-spacing: 0.5px;
    }

    .avatar {
      width:35px;
      height:35px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--cyan-primary);
    }

    /* Content Area */
    .content {
      /* background: #2a2c2c; */
      min-height: 400px;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.active {
        transform: translateX(0);
        border-radius: 0 var(--radius-xl) var(--radius-xl) 0;
      }

      .main {
        margin-left: 0;
        width: 100%;
      }

      .menu-toggle {
        display: flex;
      }

      .topbar {
        padding: 12px 16px;
      }

      .title {
        font-size: 20px;
      }

      .user-text {
        display: none;
      }

      .content {
        padding: 16px;
      }
    }

    @media (max-width: 480px) {
      .app {
        font-size: 14px;
      }

      .title {
        font-size: 18px;
      }

      .nav-item {
        padding: 10px 12px;
      }

      .content {
        padding: 12px;
      }
    }
    /* MAIN CONTENT */
    .main { margin-left: var(--sidebar-w); padding: 20px; flex: 1; min-height: 100vh; overflow: hidden; }
    .main-inner { height: 100vh; overflow-y: auto; padding-right: 12px; scrollbar-width: none; padding-bottom: 40px; }
    .main-inner::-webkit-scrollbar { display: none; }

    .topbar { display:flex; justify-content:space-between; align-items:center; padding:8px 6px; margin-bottom:12px; }
    .title-wrap { display:flex; align-items:center; gap:12px; }
    .chev { background: rgba(255,255,255,.02); width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--sub); }
    .title { font-size:22px; color:var(--text); }
    .user { display:flex; align-items:center; gap:12px; color:var(--sub); }
    .avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid rgba(23,183,154,.12); }

    /* CONTENT LAYOUT */
    .content-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .section {
      background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(0,0,0,.2));
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.03);
      box-shadow: 0 12px 30px rgba(0,0,0,.6);
      padding: 20px;
    }

    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 16px;
    }

    /* MY RESERVATIONS - PERUBAHAN: Hapus animasi hover ke kanan */
    .res-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
      max-height: 500px;
      overflow-y: auto;
    }

    .res-card {
      background: var(--panel);
      border-radius: 10px;
      padding: 14px;
      border-left: 4px solid;
      transition: all 0.3s ease;
    }

    /* Efek hover sederhana - tanpa pergeseran */
    .res-card:hover {
      background: rgba(255, 255, 255, 0.05);
    }

    .res-card.pending { border-color: var(--orange); }
    .res-card.confirmed { border-color: var(--green); }
    .res-card.seated { border-color: var(--blue); }

    .res-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .res-table {
      font-size: 16px;
      font-weight: 700;
      color: var(--text);
    }

    .status-badge {
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
    }

    .status-badge.pending { background: var(--orange); color: #fff; }
    .status-badge.confirmed { background: var(--green); color: #fff; }
    .status-badge.seated { background: var(--blue); color: #fff; }

    .res-info {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 10px;
    }

    .res-info div { margin-bottom: 4px; }

    .btn-cancel {
      padding: 8px 14px;
      background: #ff4747;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 12px;
      transition: 0.2s;
    }

    .btn-cancel:hover { 
      background: #ff2e2e;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--muted);
    }

    /* BOOKING FORM */
    .form-group { margin-bottom: 14px; }

    .form-label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: var(--muted);
      font-size: 13px;
    }

    .form-input, .form-select {
      width: 100%;
      padding: 10px;
      background: var(--panel);
      border: 1px solid rgba(255,255,255,.05);
      border-radius: 8px;
      color: var(--text);
      font-family: inherit;
      font-size: 14px;
    }

    .form-input:focus, .form-select:focus {
      outline: none;
      border-color: var(--accent);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .btn-submit {
      width: 100%;
      padding: 12px;
      background: var(--accent);
      color: #06221e;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-submit:hover { 
      background: #15a887;
    }

    @media (max-width: 1024px) {
      .content-wrapper { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="app">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>
        <nav class="nav">

        <a href="../../features/customer/home/index.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="home" aria-label="Home">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            </span>
            <span class="label">Home</span>
          </button>
        </a>

        <a href="../orders/index.php" class="nav-link-wrapper">
          <button class="nav-item active" data-target="order" aria-label="Order">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-clock-icon lucide-clipboard-clock"><path d="M16 14v2.2l1.6 1"/><path d="M16 4h2a2 2 0 0 1 2 2v.832"/><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"/><circle cx="16" cy="16" r="6"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
            </span>
            <span class="label">Order</span>
          </button>
        </a>

        <a href="../../../index.php" class="nav-link-wrapper"></a>
          <button class="nav-item" data-target="reservation" aria-label="Reservation">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 14v2.2l1.6 1"/>
                <path d="M16 2v4"/>
                <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/>
                <path d="M3 10h5"/>
                <path d="M8 2v4"/>
                <circle cx="16" cy="16" r="6"/>
              </svg>
            </span>
            <span class="label">Reservation</span>
          </button>
        </a>  

        <a href="../transactions/index.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="transaction" aria-label="Transaction">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 17V5a2 2 0 0 0-2-2H4"/>
                <path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
              </svg>
            </span>
            <span class="label">Transaction</span>
          </button>
        </a>
          
        </nav>
      </div>

      <div class="sidebar-bottom">
        <button class="logout-btn" id="logoutBtn" aria-label="Log out">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m16 17 5-5-5-5"/>
            <path d="M21 12H9"/>
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          </svg>
        </button>
      </div>
    </aside>

    <main class="main">
      <div class="main-inner">
        <header class="topbar">
          <div class="title-wrap">
            <div class="chev">›</div>
            <h1 class="title">My Reservations</h1>
          </div>
          <div class="user">
            <!-- PERUBAHAN: Tampilkan CUSTOMER bukan nama user -->
            <div class="user-text">CUSTOMER</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="user avatar">
          </div>
        </header>

        <div class="content-wrapper">
          <!-- My Reservations Section -->
          <div class="section">
            <h2 class="section-title">📋 My Reservations</h2>
            <div class="res-list" id="reservationList">
              <div class="empty-state">Loading reservations...</div>
            </div>
          </div>

          <!-- New Reservation Section -->
          <div class="section">
            <h2 class="section-title">➕ New Reservation</h2>
            <form id="bookingForm">
              <!-- Input Nama - PERUBAHAN: Dikosongkan -->
              <div class="form-group">
                <label class="form-label">Name <span style="color: #ff4747;">*</span></label>
                <input type="text" class="form-input" id="bookingName" placeholder="Enter your full name" required>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Date <span style="color: #ff4747;">*</span></label>
                  <input type="date" class="form-input" id="bookingDate" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                  <label class="form-label">Floor</label>
                  <select class="form-select" id="bookingFloor">
                    <option value="1">1st Floor</option>
                    <option value="2">2nd Floor</option>
                    <option value="3">3rd Floor</option>
                  </select>
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Table <span style="color: #ff4747;">*</span></label>
                  <select class="form-select" id="bookingTable" required>
                    <option value="">Select table...</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Hour <span style="color: #ff4747;">*</span></label>
                  <select class="form-select" id="bookingHour" required>
                    <?php foreach($hours as $h): ?>
                      <option value="<?= $h ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00</option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Guests <span style="color: #ff4747;">*</span></label>
                  <input type="number" class="form-input" id="bookingGuests" min="1" max="20" value="2" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Phone <span style="color: #ff4747;">*</span></label>
                  <input type="tel" class="form-input" id="bookingPhone" placeholder="08123456789" required>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email (Optional)</label>
                <input type="email" class="form-input" id="bookingEmail" placeholder="your@email.com">
              </div>

              <button type="submit" class="btn-submit">Book Reservation</button>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    const FLOOR_DATA = <?= json_encode($floor_data) ?>;
    const ALL_ROOMS = <?= json_encode($rooms) ?>;
    let myReservations = [];

    // Load reservations
    async function loadReservations() {
      try {
        const formData = new FormData();
        formData.append('action', 'fetch_my_reservations');
        
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
          myReservations = result.data;
          displayReservations();
        } else {
          document.getElementById('reservationList').innerHTML = '<div class="empty-state">No reservations found</div>';
        }
      } catch (error) {
        document.getElementById('reservationList').innerHTML = '<div class="empty-state">Error loading data</div>';
      }
    }

    function displayReservations() {
      const container = document.getElementById('reservationList');
      
      if (myReservations.length === 0) {
        container.innerHTML = '<div class="empty-state">No reservations yet. Book your first table!</div>';
        return;
      }
      
      container.innerHTML = myReservations.map(res => `
        <div class="res-card ${res.status}">
          <div class="res-header">
            <div class="res-table">Table ${res.table}</div>
            <span class="status-badge ${res.status}">${res.status}</span>
          </div>
          <div class="res-info">
            <div>👤 ${res.name}</div>
            <div>📅 ${res.date} at ${res.time}</div>
            <div>👥 ${res.guests} guests</div>
            <div>📞 ${res.phone}</div>
          </div>
          ${res.status === 'pending' || res.status === 'confirmed' ? `
            <button class="btn-cancel" onclick="cancelReservation(${res.id})">Cancel Reservation</button>
          ` : ''}
        </div>
      `).join('');
    }

    async function cancelReservation(id) {
      if (!confirm('Are you sure you want to cancel this reservation?')) return;
      
      try {
        const formData = new FormData();
        formData.append('action', 'cancel_reservation');
        formData.append('id', id);
        
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          loadReservations(); // Reservasi yang dibatalkan akan hilang dari daftar
        } else {
          alert(result.message);
        }
      } catch (error) {
        alert('Error canceling reservation');
      }
    }

    // Update table options based on floor
    function updateTableOptions() {
      const floor = document.getElementById('bookingFloor').value;
      const tables = FLOOR_DATA[floor];
      const select = document.getElementById('bookingTable');
      
      select.innerHTML = '<option value="">Select table...</option>';
      
      ALL_ROOMS.forEach(room => {
        if (tables.includes(room.id_reservation_room)) {
          const option = document.createElement('option');
          option.value = room.id_reservation_room;
          option.textContent = `Table ${room.id_reservation_room} (${room.seats} seats)`;
          select.appendChild(option);
        }
      });
    }

    // Submit booking form
    document.getElementById('bookingForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.textContent = 'Booking...';
      submitBtn.disabled = true;
      
      try {
        const formData = new FormData();
        formData.append('action', 'add_reservation');
        formData.append('table', document.getElementById('bookingTable').value);
        formData.append('hour', document.getElementById('bookingHour').value);
        formData.append('date', document.getElementById('bookingDate').value);
        formData.append('guests', document.getElementById('bookingGuests').value);
        formData.append('phone', document.getElementById('bookingPhone').value);
        formData.append('email', document.getElementById('bookingEmail').value);
        formData.append('name', document.getElementById('bookingName').value);
        
        const response = await fetch('', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          this.reset();
          document.getElementById('bookingDate').value = '<?= date('Y-m-d') ?>';
          // PERUBAHAN: Tidak mengisi ulang nama
          updateTableOptions();
          loadReservations();
        } else {
          alert(result.message);
        }
      } catch (error) {
        alert('Error creating reservation');
      } finally {
        submitBtn.textContent = 'Book Reservation';
        submitBtn.disabled = false;
      }
    });

    // Event listeners
    document.getElementById('bookingFloor').addEventListener('change', updateTableOptions);

    // Initialize
    updateTableOptions();
    loadReservations();

    // Auto refresh every 15 seconds
    setInterval(loadReservations, 15000);

    console.log('✅ Customer Reservation System Ready!');

    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navItems = document.querySelectorAll('.nav-item');
    const logoutBtn = document.getElementById('logoutBtn');

    // Load sidebar state from localStorage
    const sidebarState = localStorage.getItem('sidebarOpen');
    if (window.innerWidth <= 768 && sidebarState === 'true') {
      sidebar.classList.add('active');
      sidebarOverlay.classList.add('active');
      menuToggle.setAttribute('aria-expanded', 'true');
    }

    // Toggle sidebar
    function toggleSidebar() {
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
      const isOpen = sidebar.classList.contains('active');
      menuToggle.setAttribute('aria-expanded', isOpen);
      localStorage.setItem('sidebarOpen', isOpen);
    }

    menuToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    // Navigation items - highlight active
    navItems.forEach(item => {
      item.addEventListener('click', function() {
        // Remove active class from all items
        navItems.forEach(nav => nav.classList.remove('active'));
        // Add active class to clicked item
        this.classList.add('active');
        
        // Update page title
        const target = this.getAttribute('data-target');
        const label = this.querySelector('.label').textContent;
        document.querySelector('.title').textContent = label;
        
        // Close sidebar on mobile after selection
        if (window.innerWidth <= 768) {
          toggleSidebar();
        }
        
        // Store active menu in localStorage
        localStorage.setItem('activeMenu', target);
      });
    });

    // Load active menu from localStorage
    const activeMenu = localStorage.getItem('activeMenu');
    if (activeMenu) {
      const activeItem = document.querySelector(`[data-target="${activeMenu}"]`);
      if (activeItem) {
        navItems.forEach(nav => nav.classList.remove('active'));
        activeItem.classList.add('active');
        const label = activeItem.querySelector('.label').textContent;
        document.querySelector('.title').textContent = label;
      }
    }

    // Logout functionality
    // Logout functionality
    logoutBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to log out?')) {

        // Clear localStorage
        localStorage.removeItem('sidebarOpen');
        localStorage.removeItem('activeMenu');

        // Redirect to logout process
        window.location.href = "../../auth/controllers/logout.php";
      }
    });

    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });

    // Mencegah kembali ke halaman sebelumnya
    history.pushState(null, "", location.href);
    window.onpopstate = function () {
      history.pushState(null, "", location.href);
    };

  </script>
</body>
</html>