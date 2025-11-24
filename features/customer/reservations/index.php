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

// Ambil data untuk dropdown
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app">
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

        <!-- ORDER: TIDAK AKTIF -->
        <a href="../orders/index.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="order" aria-label="Order">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-clock-icon lucide-clipboard-clock"><path d="M16 14v2.2l1.6 1"/><path d="M16 4h2a2 2 0 0 1 2 2v.832"/><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"/><circle cx="16" cy="16" r="6"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
            </span>
            <span class="label">Order</span>
          </button>
        </a>

        <!-- RESERVATION: SEKARANG AKTIF ✅ -->
        <a href="index.php" class="nav-link-wrapper">
          <button class="nav-item active" data-target="reservation" aria-label="Reservation">
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

        <a href="../transactions/transaction1.php" class="nav-link-wrapper">
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
  </script>
  <script src="script.js"></script>
</body>
</html>