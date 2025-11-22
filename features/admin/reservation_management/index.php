<?php
// Include semua file yang diperlukan
require_once 'config.php';
require_once 'init_database.php';

// ==========================================
// LOGIKA RESERVATION
// ==========================================

// Data meja per lantai
$floor_data = [
    '1' => ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'],
    '2' => ['D1', 'D2', 'D3', 'E1', 'E2', 'E3', 'F1', 'F2'],
    '3' => ['G1', 'G2', 'G3', 'H1', 'H2', 'H3', 'I1', 'I2']
];

// Get selected floor (default: 1)
$selectedFloor = $_GET['floor'] ?? '1';
$floor_tables = $floor_data[$selectedFloor] ?? $floor_data['1'];
$hours = range(10, 22);

// Get selected date (default: today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get reservasi dari database
try {
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
    
} catch (PDOException $e) {
    die("Error fetching reservations: " . $e->getMessage());
}

// Helper function untuk get reservation
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

// Get daftar rooms
try {
    $stmt = $pdo->query("SELECT id_reservation_room, seats FROM reservation_rooms ORDER BY id_reservation_room");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $rooms = [];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bitehive — Reservation Admin</title>
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
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg></span><span class="label">Dashboard</span>
          </a>
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><span class="label">User Management</span>
          </a>
          <a href="#" class="nav-item">
            <span class="icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/></svg></span><span class="label">Inventory</span>
          </a>
          
          <a href="index.php" class="nav-item active">
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
                <div class="grid-table" style="grid-template-columns: 100px repeat(<?php echo count($hours); ?>, 110px);">
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
      <form id="addForm">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Table <span class="required">*</span></label>
            <select class="form-select" id="addTable" required>
              <?php foreach($rooms as $room): ?>
                <option value="<?php echo $room['id_reservation_room']; ?>">
                  Table <?php echo $room['id_reservation_room']; ?> (<?php echo $room['seats']; ?> seats)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Hour <span class="required">*</span></label>
            <select class="form-select" id="addHour" required>
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
            <input type="number" class="form-input" id="addGuests" min="1" max="20" value="2" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Email (Optional)</label>
          <input type="email" class="form-input" id="addEmail" placeholder="guest@email.com">
        </div>
        
        <div class="form-group">
          <label class="form-label">Date <span class="required">*</span></label>
          <input type="date" class="form-input" id="addDate" value="<?php echo $selectedDate; ?>" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Notes (Optional)</label>
          <textarea class="form-textarea" id="addNotes" placeholder="Special requests or notes..."></textarea>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Reservation</button>
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
    // Pass PHP data to JavaScript
    const HOURS = <?php echo json_encode($hours); ?>;
    const FLOOR_TABLES = <?php echo json_encode($floor_tables); ?>;
    
    console.log('🎯 Bitehive Reservation System');
    console.log('Current Floor:', '<?php echo $selectedFloor; ?>');
    console.log('Current Date:', '<?php echo $selectedDate; ?>');
    console.log('Total Reservations:', <?php echo count($reservations); ?>);
  </script>
  <script src="script.js"></script>
</body>
</html>