<?php
session_start();

// Koneksi Database
$host = 'localhost';
$dbname = 'restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
} catch (PDOException $e) {
    die("❌ DATABASE CONNECTION FAILED: " . $e->getMessage() . "<br><br>
         <strong>SOLUSI:</strong><br>
         1. Pastikan XAMPP MySQL sudah running<br>
         2. Buka phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a><br>
         3. Import file <strong>restaurant.sql</strong> terlebih dahulu<br>
         4. Pastikan database bernama <strong>restaurant</strong> sudah ada");
}

// Data lantai
$floor_data = [
    '1' => ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'],
    '2' => ['D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2'],
    '3' => ['G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2']
];

// Floor selection
if (isset($_GET['floor'])) {
    $_SESSION['selected_floor'] = $_GET['floor'];
} elseif (!isset($_SESSION['selected_floor'])) {
    $_SESSION['selected_floor'] = '1';
}
$selectedFloor = $_SESSION['selected_floor'];
$floor_tables = $floor_data[$selectedFloor] ?? $floor_data['1'];

// Tanggal
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$_SESSION['selected_date'] = $selectedDate;

// Ambil data
try {
    $stmt = $pdo->prepare("
        SELECT r.*, rr.seats as table_capacity, 
               COALESCE(r.customer_name, u.fullname, 'Guest') as display_name
        FROM reservation r 
        LEFT JOIN users u ON r.id_user = u.id_user 
        JOIN reservation_rooms rr ON r.id_reservation_room = rr.id_reservation_room
        WHERE DATE(r.reservation_date) = :date
        AND r.status NOT IN ('cancelled', 'deleted')
        ORDER BY r.reservation_start
    ");
    $stmt->execute(['date' => $selectedDate]);
    $reservations = $stmt->fetchAll();
    $stmt->closeCursor();

    $stmt = $pdo->query("SELECT id_reservation_room, seats FROM reservation_rooms ORDER BY id_reservation_room");
    $rooms = $stmt->fetchAll();
    $stmt->closeCursor();
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
                'name' => $res['display_name'],
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

$hours = range(10, 22);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitehive - Reservation Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-top">
                <div class="brand">Bitehive</div>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-item">
                        <span class="icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="7" height="9" x="3" y="3" rx="1"/>
                                <rect width="7" height="5" x="14" y="3" rx="1"/>
                                <rect width="7" height="9" x="14" y="12" rx="1"/>
                                <rect width="7" height="5" x="3" y="16" rx="1"/>
                            </svg>
                        </span>
                        <span class="label">Dashboard</span>
                    </a>
                    <a href="user_management.php" class="nav-item">
                        <span class="icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <span class="label">User Management</span>
                    </a>
                    <a href="inventory.php" class="nav-item">
                        <span class="icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                                <path d="M12 22V12"/>
                                <polyline points="3.29 7 12 12 20.71 7"/>
                                <path d="m7.5 4.27 9 5.15"/>
                            </svg>
                        </span>
                        <span class="label">Inventory</span>
                    </a>
                    <a href="admin_reservation.php" class="nav-item active">
                        <span class="icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 14v2.2l1.6 1"/>
                                <path d="M16 2v4"/>
                                <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/>
                                <path d="M3 10h5"/>
                                <path d="M8 2v4"/>
                                <circle cx="16" cy="16" r="6"/>
                            </svg>
                        </span>
                        <span class="label">Reservation</span>
                    </a>
                    <a href="transactions.php" class="nav-item">
                        <span class="icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 17V5a2 2 0 0 0-2-2H4"/>
                                <path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
                            </svg>
                        </span>
                        <span class="label">Transaction</span>
                    </a>
                </nav>
            </div>
            <div class="sidebar-bottom">
                <button class="logout-btn" id="logoutBtn" onclick="location.href='logout.php'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m16 17 5-5-5-5"/>
                        <path d="M21 12H9"/>
                        <path d="M9 21H5a2 2 0 0 0-2-2V5a2 2 0 0 0 2-2h4"/>
                    </svg>
                </button>
            </div>
        </aside>

        <main class="main">
            <div class="main-inner">
                <header class="topbar">
                    <div class="title-wrap">
                        <button class="menu-toggle" id="menuToggle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <line x1="3" y1="18" x2="21" y2="18"/>
                            </svg>
                        </button>
                        <h1 class="title">Reservation Management</h1>
                    </div>
                    <div class="user">
                        <div class="user-text">ADMIN</div>
                        <img class="avatar" src="https://i.pravatar.cc/40?img=5" alt="Admin">
                    </div>
                </header>

                <div class="content">
                    <div class="res-toolbar">
                        <div class="res-tabs">
                            <button class="res-tab <?= $selectedFloor == '1' ? 'active' : '' ?>" onclick="changeFloor('1')">1st Floor</button>
                            <button class="res-tab <?= $selectedFloor == '2' ? 'active' : '' ?>" onclick="changeFloor('2')">2nd Floor</button>
                            <button class="res-tab <?= $selectedFloor == '3' ? 'active' : '' ?>" onclick="changeFloor('3')">3rd Floor</button>
                        </div>
                        <div class="res-actions">
                            <input type="date" class="date-input" id="dateFilter" value="<?= $selectedDate ?>">
                            <button class="btn-add" onclick="openAddModal()">+ Add Reservation</button>
                        </div>
                    </div>

                    <div class="grid-wrapper">
                        <div class="grid-table">
                            <div class="header-cell corner">TABLE</div>
                            <?php foreach($hours as $h): ?>
                                <div class="header-cell"><?= str_pad($h, 2, '0', STR_PAD_LEFT) . ':00' ?></div>
                            <?php endforeach; ?>
                            <?php foreach($floor_tables as $table): ?>
                                <div class="row-label"><?= $table ?></div>
                                <?php foreach($hours as $h): ?>
                                    <?php $res = getReservation($table, $h, $reservations); ?>
                                    <div class="cell <?= $res ? '' : 'empty' ?>" 
                                         onclick="<?= $res ? "openDetailModal({$res['id']})" : "openBookingModal('$table', $h)" ?>">
                                        <?php if($res): ?>
                                            <div class="res-block st-<?= $res['status'] ?>">
                                                <div class="res-name"><?= htmlspecialchars($res['name']) ?></div>
                                                <div class="res-pax">👥 <?= $res['guests'] ?> guests</div>
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

    <!-- Modals (HTML tetap di sini agar DOM siap) -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Reservation</h2>
                <button class="modal-close" onclick="closeAddModal()">&times;</button>
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
                                <option value="<?= $room['id_reservation_room'] ?>" data-seats="<?= $room['seats'] ?>">
                                    Table <?= $room['id_reservation_room'] ?> (<?= $room['seats'] ?> seats)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date <span class="required">*</span></label>
                        <input type="date" class="form-input" id="addDate" value="<?= $selectedDate ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hour <span class="required">*</span></label>
                        <select class="form-select" id="addHour" required>
                            <?php foreach($hours as $h): ?>
                                <option value="<?= $h ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00</option>
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
                        <input type="number" class="form-input" id="addGuests" min="1" max="20" value="2" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" class="form-input" id="addEmail" placeholder="guest@email.com">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Reservation</button>
                </div>
            </form>
        </div>
    </div>

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
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="deleteReservation()">Delete</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kirim data ke JS -->
    <script>
        const HOURS = <?= json_encode($hours) ?>;
        const FLOOR_TABLES = <?= json_encode($floor_tables) ?>;
        const ALL_ROOMS = <?= json_encode($rooms) ?>;
        const FLOOR_DATA = <?= json_encode($floor_data) ?>;
        const SELECTED_FLOOR = <?= json_encode($selectedFloor) ?>;
        const SELECTED_DATE = <?= json_encode($selectedDate) ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>