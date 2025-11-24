<?php
// ====== KONFIG DATABASE ======
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'restaurant';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}

// ====== HELPER ======
function getUserName($mysqli, $id_user) {
    $id_user = (int)$id_user;
    if ($id_user <= 0) {
        return 'Unknown';
    }

    $default = 'User #' . $id_user;

    $stmt = $mysqli->prepare("SELECT fullname FROM users WHERE id_user = ? LIMIT 1");
    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param("i", $id_user);
    if (!$stmt->execute()) {
        $stmt->close();
        return $default;
    }

    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['fullname'])) {
            $default = $row['fullname'];
        }
    }

    $stmt->close();
    return $default;
}

function formatIdr($amount) {
    if ($amount === null) {
        return 'IDR -';
    }
    return 'IDR ' . number_format((float)$amount, 0, ',', '.');
}

// ====== AMBIL DATA ORDER (transaction_order) ======
$orders = [];

$orderSql = "
    SELECT 
        t.id_transaction_order,
        t.id_user,
        t.status AS trx_status,
        t.created_at,
        c.id_cart_order,
        c.name_item,
        c.quantity,
        c.subtotal,
        c.price,
        p.total_amount,
        p.received,
        p.return_amount
    FROM transaction_order t
    JOIN cart_order c ON t.id_cart_order = c.id_cart_order
    JOIN payment_order p ON t.id_payment_order = p.id_payment_order
    ORDER BY t.created_at DESC
";

if ($result = $mysqli->query($orderSql)) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $result->free();
}

// ====== AMBIL DATA RESERVATION (transaction_reservation) ======
$reservations = [];

$resSql = "
    SELECT
        tr.id_transaction_reservation,
        tr.id_user,
        tr.id_reservation,
        tr.status AS trx_status,
        tr.created_at,
        r.id_reservation_room,
        r.seats,
        r.reservation_date,
        r.reservation_start,
        r.status AS reservation_status,
        rr.price_place,
        pr.total_amount
    FROM transaction_reservation tr
    JOIN reservation r ON tr.id_reservation = r.id_reservation
    JOIN reservation_rooms rr ON r.id_reservation_room = rr.id_reservation_room
    JOIN payment_reservation pr ON tr.id_payment_reservation = pr.id_payment_reservation
    ORDER BY tr.created_at DESC
";

if ($result = $mysqli->query($resSql)) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    $result->free();
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Bitehive Admin — Transaction UI</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="transaction_admin.css">
</head>
<body>
  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>

        <nav class="nav">
        
        <a href="../dashboard/index.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="dashboard" aria-label="Dashboard">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"/>
                <rect width="7" height="5" x="14" y="3" rx="1"/>
                <rect width="7" height="9" x="14" y="12" rx="1"/>
                <rect width="7" height="5" x="3" y="16" rx="1"/>
              </svg>
            </span>
            <span class="label">Dashboard</span>
          </button>
        </a>  

        <a href="../user_management/user_management.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="user" aria-label="User">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <span class="label">User</span>
          </button>
        </a>  

        <a href="../inventory/index.php" class="nav-link-wrapper">
          <button class="nav-item" data-target="inventory" aria-label="Inventory">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                <path d="M12 22V12"/>
                <polyline points="3.29 7 12 12 20.71 7"/>
                <path d="m7.5 4.27 9 5.15"/>
              </svg>
            </span>
            <span class="label">Inventory</span>
          </button>
        </a> 

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

        <a href="../transactions_management/transaction_admin.php" class="nav-link-wrapper">
          <button class="nav-item active" data-target="transaction" aria-label="Transaction">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 17V5a2 2 0 0 0-2-2H4"/>
                <path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
              </svg>
            </span>
            <span class="label">Transaction</span>
          </button>
        </a>  

          <button class="nav-item" data-target="audit" aria-label="Audit Logs">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 14v2.2l1.6 1"/>
                <path d="M16 4h2a2 2 0 0 1 2 2v.832"/>
                <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"/>
                <circle cx="16" cy="16" r="6"/>
                <rect x="8" y="2" width="8" height="4" rx="1"/>
              </svg>
            </span>
            <span class="label">Audit Logs</span>
          </button>
        </nav>
      </div>

      <div class="sidebar-bottom">
        <button class="logout-btn" aria-label="Log out">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m16 17 5-5-5-5"/>
            <path d="M21 12H9"/>
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          </svg>
        </button>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <div class="main-inner">
        <!-- TOPBAR -->
        <header class="topbar">
          <div class="title-wrap">
            <div class="chev">›</div>
            <h1 class="title">Transaction</h1>
          </div>

          <div class="user">
            <div class="user-text">ADMIN</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="admin avatar">
          </div>
        </header>

        <!-- CONTROLS -->
        <section class="controls">
          <div class="tabs" role="tablist" aria-label="Filter transactions">
            <button class="tab active" data-filter="all">All</button>
            <button class="tab" data-filter="completed">Completed</button>
            <button class="tab" data-filter="pending">Pending</button>
            <button class="tab" data-filter="cancelled">Cancelled</button>
          </div>

          <div class="search-wrapper">
            <div class="date-filter-wrapper">
              <select id="date-filter" class="date-filter" aria-label="Date filter">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="all">All Time</option>
              </select>
              <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>

            <div class="search-container">
              <input id="search" placeholder="Search a name, order or etc" aria-label="Search"/>
              <button class="search-btn" aria-label="search"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </section>

        <!-- CARDS -->
        <section id="cards" class="cards-grid">
          <?php
          $badgeNo = 1;

          // ====== CARD ORDER ======
          foreach ($orders as $order):
              $badge = str_pad($badgeNo, 2, '0', STR_PAD_LEFT);

              $statusDb = strtolower($order['trx_status']);
              if ($statusDb === 'confirmed') {
                  $statusClass = 'completed';
                  $statusLabel = '✓ Completed';
              } elseif ($statusDb === 'pending') {
                  $statusClass = 'pending';
                  $statusLabel = '⏱ Pending';
              } else {
                  $statusClass = 'cancelled';
                  $statusLabel = '✕ Cancelled';
              }

              $ts = $order['created_at'] ? strtotime($order['created_at']) : time();
              $dateText = date('l, d-m-Y', $ts);
              $timeText = date('h : i A', $ts);

              $customerName = getUserName($mysqli, $order['id_user']);

              $totalAmount   = (float)$order['total_amount'];
              $received      = (float)$order['received'];
              $returnAmount  = (float)$order['return_amount'];
              $qty           = (int)$order['quantity'];
              $price         = isset($order['price']) ? (float)$order['price'] : 0;
          ?>
          <article
            class="card"
            data-type="order"
            data-order-id="<?php echo (int)$order['id_transaction_order']; ?>"
            data-status="<?php echo $statusClass; ?>"
            data-total="<?php echo htmlspecialchars($totalAmount, ENT_QUOTES); ?>"
            data-received="<?php echo htmlspecialchars($received, ENT_QUOTES); ?>"
            data-return="<?php echo htmlspecialchars($returnAmount, ENT_QUOTES); ?>"
          >
            <div class="card-header">
              <div class="badge"><?php echo $badge; ?></div>
              <div class="meta">
                <div class="name"><?php echo htmlspecialchars($customerName); ?></div>
                <div class="order">Order #<?php echo sprintf('%03d', $order['id_transaction_order']); ?></div>
              </div>
              <div class="status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></div>
            </div>

            <div class="card-body">
              <div class="row info">
                <div><?php echo $dateText; ?></div>
                <div><?php echo $timeText; ?></div>
              </div>
              <hr />
              <div class="items">
                <div class="items-header">
                  <div>Serial Items</div>
                  <div>Qty</div>
                </div>

                <div
                  class="item"
                  data-price="<?php echo htmlspecialchars($price, ENT_QUOTES); ?>"
                >
                  <div class="left"># 01 <?php echo htmlspecialchars($order['name_item']); ?></div>
                  <div class="right"><?php echo $qty; ?></div>
                </div>
              </div>
              <hr />
              <div class="total">
                <div>Total Price</div>
                <div><?php echo formatIdr($totalAmount); ?></div>
              </div>
              <button class="viewall">View All</button>
            </div>
          </article>
          <?php
              $badgeNo++;
          endforeach;

          // ====== CARD RESERVATION ======
          foreach ($reservations as $reservation):
              $badge = str_pad($badgeNo, 2, '0', STR_PAD_LEFT);

              $statusTrx   = strtolower($reservation['trx_status']);          // pending / confirmed / cancelled
              $statusRes   = strtolower($reservation['reservation_status']);  // pending/confirmed/seated/cancelled

              if ($statusRes === 'cancelled' || $statusTrx === 'cancelled') {
                  $statusClass = 'cancelled';
                  $statusLabel = '✕ Cancelled';
              } elseif ($statusTrx === 'confirmed' || $statusRes === 'confirmed' || $statusRes === 'seated') {
                  $statusClass = 'completed';
                  $statusLabel = '✓ Completed';
              } elseif ($statusTrx === 'pending' || $statusRes === 'pending') {
                  $statusClass = 'pending';
                  $statusLabel = '⏱ Pending';
              } else {
                  $statusClass = 'pending';
                  $statusLabel = '⏱ Pending';
              }

              $dateText = $reservation['reservation_date']
                  ? date('d-m-Y', strtotime($reservation['reservation_date']))
                  : date('d-m-Y', strtotime($reservation['created_at']));

              $timeText = $reservation['reservation_start']
                  ? date('h : i A', strtotime($reservation['reservation_start']))
                  : date('h : i A', strtotime($reservation['created_at']));

              $seats   = (int)$reservation['seats'];
              $deposit = (float)$reservation['total_amount'];
              $customerName = getUserName($mysqli, $reservation['id_user']);
              $roomCode = $reservation['id_reservation_room'];
          ?>
          <article
            class="card reservation-card"
            data-type="reservation"
            data-reservation-id="<?php echo (int)$reservation['id_transaction_reservation']; ?>"
            data-status="<?php echo $statusClass; ?>"
          >
            <div class="reservation-image-container">
              <img src="../../../foto/download.jpg" alt="Table photo">
              <div class="reservation-overlay-info">
                <div class="badge"><?php echo $badge; ?></div>
                <div class="overlay-meta">
                  <div class="name">Table #<?php echo htmlspecialchars($roomCode); ?></div>
                  <div class="order">Reservation ID #<?php echo sprintf('%08d', $reservation['id_transaction_reservation']); ?></div>
                </div>
              </div>
            </div>
            <div class="reservation-body-new">
              <div class="status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></div>
              <div class="reservation-datetime">
                <?php echo $dateText; ?> • <?php echo $timeText; ?>
              </div>
              <div class="reservation-info-grid">
                <div class="info-item">
                  <span class="info-label">Seats</span>
                  <strong class="info-value"><?php echo str_pad($seats, 2, '0', STR_PAD_LEFT); ?> persons</strong>
                </div>
                <div class="info-item">
                  <span class="info-label">Deposit Fee</span>
                  <strong class="info-value"><?php echo formatIdr($deposit); ?></strong>
                </div>
                <div class="info-item">
                  <span class="info-label">Customer</span>
                  <strong class="info-value"><?php echo htmlspecialchars($customerName); ?></strong>
                </div>
              </div>
              <button class="viewall view-reservation">View All</button>
            </div>
          </article>
          <?php
              $badgeNo++;
          endforeach;

          if (empty($orders) && empty($reservations)) {
              echo '<p>Tidak ada transaksi.</p>';
          }
          ?>
        </section>
      </div>
    </main>
  </div>

  <!-- RESERVATION DETAIL MODAL -->
  <div id="reservation-modal-overlay" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal modal-reservation-detail" role="document">
      <header class="modal-header-simple">
        <div class="breadcrumb-nav">
          <span>Transaction</span><span class="sep">›</span><span>Detail of Reservation</span>
        </div>
        <button id="reservation-modal-close" class="modal-close" aria-label="Close">&times;</button>
      </header>

      <div class="reservation-hero">
        <img src="../../../foto/download.jpg" alt="Table" class="reservation-hero-img">
        <div class="reservation-hero-overlay">
          <h2 class="reservation-table-name">Table # 01</h2>
        </div>
        <div class="reservation-status-badge completed">✓ Completed</div>
      </div>

      <div class="reservation-modal-content">
        <h3 class="section-title">Reservation Details</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Room 1</span>
            <span class="detail-value reservation-detail-room">01</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Seats</span>
            <span class="detail-value reservation-detail-seats">05 persons</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Reservation Date</span>
            <span class="detail-value reservation-detail-date">28. 03. 2024</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Reservation Time</span>
            <span class="detail-value reservation-detail-time">03 : 00 PM</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Reservation End</span>
            <span class="detail-value">05 : 00 PM</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Deposit Fee</span>
            <span class="detail-value reservation-detail-deposit">IDR 1000000.00</span>
          </div>
        </div>

        <h3 class="section-title" style="margin-top: 24px;">Customer Details</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Reservation ID</span>
            <span class="detail-value reservation-detail-id">#12354564</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Full Name</span>
            <span class="detail-value reservation-detail-customer">Watson Joyce</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Phone number</span>
            <span class="detail-value">+1 (123) 123 4654</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Email Address</span>
            <span class="detail-value">watsonjoyce112@gmail.com</span>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel">Cancel Reservation</button>
          <button class="btn-approve">Approvement</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ORDER DETAIL MODAL -->
  <div id="order-modal-overlay" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal modal-order-detail" role="document">
      <header class="modal-header-simple">
        <div class="breadcrumb-nav">
          <span>Transaction</span><span class="sep">›</span><span>Detail of Order</span>
        </div>
        <button id="order-modal-close" class="modal-close" aria-label="Close">&times;</button>
      </header>

      <div class="order-header">
        <div class="order-header-left">
          <div class="badge-large pink">02</div>
          <div>
            <h3 class="order-customer-name">Watson Joyce</h3>
            <p class="order-number">Order # 002</p>
          </div>
        </div>
        <div class="order-header-right">
          <span class="order-date">Wednesday, 28-08-2025</span>
          <span class="order-time">04 : 34 PM</span>
        </div>
        <div class="order-status-badge completed">✓ Completed</div>
      </div>

      <div class="order-table-container">
        <table class="order-table">
          <thead>
            <tr>
              <th>Orders</th>
              <th>Name of product</th>
              <th>Quantity</th>
              <th>Prices</th>
              <th>Sub Total</th>
            </tr>
          </thead>
          <tbody id="order-table-body">
            <!-- Diisi dinamis oleh JS -->
          </tbody>
        </table>
      </div>

      <div class="order-summary">
        <div class="order-summary-icon">💵</div>
        <div class="order-summary-grid">
          <div class="summary-row">
            <span>Total Prices</span>
            <strong id="order-total-price">IDR 0.00</strong>
          </div>
          <div class="summary-row">
            <span>Disc. 0%</span>
            <strong>IDR 0</strong>
          </div>
          <div class="summary-row">
            <span>Tax 0%</span>
            <strong>IDR 0</strong>
          </div>
          <div class="summary-row">
            <span>Total</span>
            <strong id="order-grand-total">IDR 0.00</strong>
          </div>
          <div class="summary-row">
            <span>Received</span>
            <strong id="order-received">IDR 0.00</strong>
          </div>
          <div class="summary-row">
            <span>Return</span>
            <strong id="order-return">IDR 0.00</strong>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel">Cancel Ordered</button>
        <button class="btn-approve">Approvement</button>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script>
    /* NAV ACTIVE */
    (function () {
      const navItems = document.querySelectorAll('.nav-item');
      const titleEl = document.querySelector('.title');

      navItems.forEach(item => {
        item.addEventListener('click', () => {
          navItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');

          navItems.forEach(i => {
            i.setAttribute('aria-current', i.classList.contains('active') ? 'page' : 'false');
          });

          const label = item.querySelector('.label')?.textContent || '';
          if (label && titleEl) {
            titleEl.textContent = label;
          }
        });
      });
    })();

    /* LIMIT ITEMS TO 3 */
    (function () {
      const allCards = document.querySelectorAll('.card:not(.reservation-card)');
      allCards.forEach(card => {
        const items = card.querySelectorAll('.items .item');
        items.forEach((item, idx) => {
          if (idx >= 3) item.classList.add('hidden-item');
        });
      });
    })();

    /* FILTER & SEARCH */
    (function () {
      const tabs = document.querySelectorAll('.tab');
      const cards = document.querySelectorAll('.card');
      const search = document.getElementById('search');

      function filterCards(filter, q) {
        q = (q || '').toLowerCase().trim();

        cards.forEach(card => {
          const status = (card.dataset.status || '').toLowerCase();
          const name = (card.querySelector('.name')?.textContent || '').toLowerCase();
          const order = (card.querySelector('.order')?.textContent || '').toLowerCase();

          const matchesFilter =
            filter === 'all' ||
            (filter === 'completed' && status === 'completed') ||
            (filter === 'pending' && status === 'pending') ||
            (filter === 'cancelled' && status === 'cancelled');

          const matchesQuery =
            !q ||
            name.includes(q) ||
            order.includes(q) ||
            card.textContent.toLowerCase().includes(q);

          card.style.display = (matchesFilter && matchesQuery) ? '' : 'none';
        });
      }

      tabs.forEach(t => {
        t.addEventListener('click', () => {
          tabs.forEach(x => x.classList.remove('active'));
          t.classList.add('active');
          filterCards(t.dataset.filter, search.value);
        });
      });

      if (search) {
        search.addEventListener('input', (e) => {
          const activeTab = document.querySelector('.tab.active')?.dataset.filter || 'all';
          filterCards(activeTab, e.target.value);
        });
      }

      filterCards('all', '');
    })();

    /* MODAL + ACTION + REALTIME */
    (function () {
      const orderModalOverlay = document.getElementById('order-modal-overlay');
      const orderModalClose = document.getElementById('order-modal-close');
      const reservationModalOverlay = document.getElementById('reservation-modal-overlay');
      const reservationModalClose = document.getElementById('reservation-modal-close');

      const viewButtons = document.querySelectorAll('.viewall, .view-reservation');

      const badgeLarge = document.querySelector('.badge-large.pink');
      const orderCustomerName = document.querySelector('.order-customer-name');
      const orderNumber = document.querySelector('.order-number');
      const orderDate = document.querySelector('.order-date');
      const orderTime = document.querySelector('.order-time');

      const tableBody = document.getElementById('order-table-body');
      const totalPriceEl = document.getElementById('order-total-price');
      const grandTotalEl = document.getElementById('order-grand-total');
      const receivedEl = document.getElementById('order-received');
      const returnEl = document.getElementById('order-return');
      const orderStatusBadge = document.querySelector('.order-status-badge');

      const reservationStatusBadge = document.querySelector('.reservation-status-badge');

      const ACTION_URL = 'transaction_action.php';
      const STATUS_URL = 'transaction_statuses.php';

      let currentOrderId = null;
      let currentReservationId = null;

      function formatIDR(num) {
        const n = Number(num) || 0;
        return 'IDR ' + n.toLocaleString('id-ID') + '.00';
      }

      function mapDbStatusToUi(dbStatus) {
        const s = (dbStatus || '').toString().toLowerCase();
        if (s === 'confirmed' || s === 'complete' || s === 'completed') {
          return { className: 'completed', label: '✓ Completed' };
        }
        if (s === 'cancelled' || s === 'canceled') {
          return { className: 'cancelled', label: '✕ Cancelled' };
        }
        return { className: 'pending', label: '⏱ Pending' };
      }

      function openOrderModalFromCard(card) {
        if (!orderModalOverlay) return;

        currentOrderId = card.getAttribute('data-order-id') || null;

        const badge = card.querySelector('.badge')?.textContent.trim() || '';
        const name = card.querySelector('.name')?.textContent.trim() || '';
        const order = card.querySelector('.order')?.textContent.trim() || '';
        const infoDivs = card.querySelectorAll('.row.info > div');
        const date = infoDivs[0]?.textContent.trim() || '';
        const time = infoDivs[1]?.textContent.trim() || '';
        const statusClass = card.dataset.status || 'completed';

        const totalFromCard = parseFloat(card.getAttribute('data-total') || '0');
        const receivedFromCard = parseFloat(card.getAttribute('data-received') || '0');
        const returnFromCard = parseFloat(card.getAttribute('data-return') || '0');

        if (badgeLarge) badgeLarge.textContent = badge;
        if (orderCustomerName) orderCustomerName.textContent = name;
        if (orderNumber) orderNumber.textContent = order;
        if (orderDate) orderDate.textContent = date;
        if (orderTime) orderTime.textContent = time;

        const itemNodes = Array.from(card.querySelectorAll('.items .item'));
        let rowsHtml = '';
        let totalCalc = 0;

        itemNodes.forEach((item, idx) => {
          const productText = item.querySelector('.left')?.textContent.trim() || '';
          const qtyText = item.querySelector('.right')?.textContent.trim() || '1';
          const qty = parseInt(qtyText, 10) || 0;

          const productName = productText.replace(/^#\s*\d+\s*/, '');
          const priceAttr = parseFloat(item.getAttribute('data-price') || '0');

          let price = priceAttr;
          if (!price && totalFromCard && qty) {
            price = totalFromCard / qty;
          }
          if (!price) price = 330000;

          const subtotal = price * qty;
          totalCalc += subtotal;

          rowsHtml += `
            <tr>
              <td>#${String(idx + 1).padStart(3, '0')}</td>
              <td>${productName}</td>
              <td>${qty}</td>
              <td>${formatIDR(price)}</td>
              <td>${formatIDR(subtotal)}</td>
            </tr>
          `;
        });

        if (tableBody) {
          tableBody.innerHTML = rowsHtml;
        }

        const totalFinal = totalFromCard || totalCalc;
        const receivedFinal = receivedFromCard || totalFinal;
        const returnFinal = returnFromCard || (receivedFinal - totalFinal);

        if (totalPriceEl) totalPriceEl.textContent = formatIDR(totalFinal);
        if (grandTotalEl) grandTotalEl.textContent = formatIDR(totalFinal);
        if (receivedEl) receivedEl.textContent = formatIDR(receivedFinal);
        if (returnEl) returnEl.textContent = formatIDR(returnFinal);

        if (orderStatusBadge) {
          orderStatusBadge.classList.remove('completed', 'cancelled', 'pending');
          const ui = mapDbStatusToUi(statusClass === 'completed' ? 'confirmed' : statusClass);
          orderStatusBadge.classList.add(ui.className);
          orderStatusBadge.textContent = ui.label;
        }

        orderModalOverlay.classList.add('open');
        orderModalOverlay.setAttribute('aria-hidden', 'false');
      }

      function openReservationModalFromCard(card) {
        if (!reservationModalOverlay) return;

        currentReservationId = card.getAttribute('data-reservation-id') || null;

        const tableNameEl = document.querySelector('.reservation-table-name');
        const reservationIdEl = document.querySelector('.reservation-detail-id');
        const seatsEl = document.querySelector('.reservation-detail-seats');
        const dateEl = document.querySelector('.reservation-detail-date');
        const timeEl = document.querySelector('.reservation-detail-time');
        const depositEl = document.querySelector('.reservation-detail-deposit');
        const customerEl = document.querySelector('.reservation-detail-customer');

        const overlayName = card.querySelector('.overlay-meta .name')?.textContent.trim() || '';
        const overlayOrder = card.querySelector('.overlay-meta .order')?.textContent.trim() || '';
        const datetime = card.querySelector('.reservation-datetime')?.textContent.trim() || '';
        const infoItems = card.querySelectorAll('.info-item');
        const seatsText = infoItems[0]?.querySelector('.info-value')?.textContent.trim() || '';
        const depositText = infoItems[1]?.querySelector('.info-value')?.textContent.trim() || '';
        const customerText = infoItems[2]?.querySelector('.info-value')?.textContent.trim() || '';

        if (tableNameEl) tableNameEl.textContent = overlayName || 'Table';
        if (reservationIdEl) reservationIdEl.textContent = overlayOrder || '#ID';
        if (seatsEl) seatsEl.textContent = seatsText || '-';
        if (depositEl) depositEl.textContent = depositText || '-';
        if (customerEl) customerEl.textContent = customerText || '-';

        if (datetime.indexOf('•') > -1) {
          const parts = datetime.split('•').map(s => s.trim());
          if (dateEl) dateEl.textContent = parts[0] || '';
          if (timeEl) timeEl.textContent = parts[1] || '';
        } else {
          if (dateEl) dateEl.textContent = datetime;
          if (timeEl) timeEl.textContent = '';
        }

        const cardStatus = card.dataset.status || 'pending';
        if (reservationStatusBadge) {
          reservationStatusBadge.classList.remove('completed', 'cancelled', 'pending');
          const ui = mapDbStatusToUi(cardStatus === 'completed' ? 'confirmed' : cardStatus);
          reservationStatusBadge.classList.add(ui.className);
          reservationStatusBadge.textContent = ui.label;
        }

        reservationModalOverlay.classList.add('open');
        reservationModalOverlay.setAttribute('aria-hidden', 'false');
      }

      viewButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
          const card = e.currentTarget.closest('.card');
          if (!card) return;

          if (card.classList.contains('reservation-card')) {
            openReservationModalFromCard(card);
          } else {
            openOrderModalFromCard(card);
          }
        });
      });

      function closeOrderModal() {
        if (!orderModalOverlay) return;
        orderModalOverlay.classList.remove('open');
        orderModalOverlay.setAttribute('aria-hidden', 'true');
        currentOrderId = null;
      }

      function closeReservationModal() {
        if (!reservationModalOverlay) return;
        reservationModalOverlay.classList.remove('open');
        reservationModalOverlay.setAttribute('aria-hidden', 'true');
        currentReservationId = null;
      }

      if (orderModalClose && orderModalOverlay) {
        orderModalClose.addEventListener('click', closeOrderModal);
        orderModalOverlay.addEventListener('click', (e) => {
          if (e.target === orderModalOverlay) closeOrderModal();
        });
      }

      if (reservationModalClose && reservationModalOverlay) {
        reservationModalClose.addEventListener('click', closeReservationModal);
        reservationModalOverlay.addEventListener('click', (e) => {
          if (e.target === reservationModalOverlay) closeReservationModal();
        });
      }

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          if (orderModalOverlay && orderModalOverlay.classList.contains('open')) {
            closeOrderModal();
          }
          if (reservationModalOverlay && reservationModalOverlay.classList.contains('open')) {
            closeReservationModal();
          }
        }
      });

      // ====== ACTION BUTTONS (APPROVE / CANCEL) ======
      const orderApproveBtn = document.querySelector('.modal-order-detail .btn-approve');
      const orderCancelBtn = document.querySelector('.modal-order-detail .btn-cancel');
      const reservationApproveBtn = document.querySelector('.modal-reservation-detail .btn-approve');
      const reservationCancelBtn = document.querySelector('.modal-reservation-detail .btn-cancel');

      async function sendAction(type, id, action) {
        if (!id) return;
        try {
          const formData = new FormData();
          formData.append('type', type);
          formData.append('id', id);
          formData.append('action', action);

          const res = await fetch(ACTION_URL, {
            method: 'POST',
            body: formData
          });

          if (!res.ok) {
            console.error('HTTP error', res.status);
            alert('Gagal mengubah status (HTTP ' + res.status + ').');
            return;
          }

          const data = await res.json();
          if (!data.success) {
            alert('Gagal mengubah status: ' + (data.message || 'Unknown error'));
            return;
          }

          // Setelah 2 detik, refresh status dari server
          setTimeout(fetchStatusesAndUpdateUI, 2000);
        } catch (err) {
          console.error(err);
          alert('Terjadi kesalahan koneksi ke server.');
        }
      }

      if (orderApproveBtn) {
        orderApproveBtn.addEventListener('click', () => {
          if (currentOrderId) {
            sendAction('order', currentOrderId, 'approve');
          }
        });
      }

      if (orderCancelBtn) {
        orderCancelBtn.addEventListener('click', () => {
          if (currentOrderId) {
            sendAction('order', currentOrderId, 'cancel');
          }
        });
      }

      if (reservationApproveBtn) {
        reservationApproveBtn.addEventListener('click', () => {
          if (currentReservationId) {
            sendAction('reservation', currentReservationId, 'approve');
          }
        });
      }

      if (reservationCancelBtn) {
        reservationCancelBtn.addEventListener('click', () => {
          if (currentReservationId) {
            sendAction('reservation', currentReservationId, 'cancel');
          }
        });
      }

      // ====== REALTIME STATUS ======
      function updateOrderCardStatusFromDb(orderId, dbStatus) {
        const card = document.querySelector(`.card[data-type="order"][data-order-id="${orderId}"]`);
        if (!card) return;
        const ui = mapDbStatusToUi(dbStatus);
        card.dataset.status = ui.className;

        const statusEl = card.querySelector('.status');
        if (statusEl) {
          statusEl.classList.remove('completed', 'cancelled', 'pending');
          statusEl.classList.add(ui.className);
          statusEl.textContent = ui.label;
        }
      }

      function updateReservationCardStatusFromDb(reservationId, dbStatus) {
        const card = document.querySelector(`.card[data-type="reservation"][data-reservation-id="${reservationId}"]`);
        if (!card) return;
        const ui = mapDbStatusToUi(dbStatus);
        card.dataset.status = ui.className;

        const statusEl = card.querySelector('.status');
        if (statusEl) {
          statusEl.classList.remove('completed', 'cancelled', 'pending');
          statusEl.classList.add(ui.className);
          statusEl.textContent = ui.label;
        }
      }

      function refreshStatusBadgesInOpenModals() {
        if (orderModalOverlay && orderModalOverlay.classList.contains('open') && currentOrderId && orderStatusBadge) {
          const card = document.querySelector(`.card[data-type="order"][data-order-id="${currentOrderId}"]`);
          if (card) {
            const s = card.dataset.status || 'completed';
            const ui = mapDbStatusToUi(s === 'completed' ? 'confirmed' : s);
            orderStatusBadge.classList.remove('completed', 'cancelled', 'pending');
            orderStatusBadge.classList.add(ui.className);
            orderStatusBadge.textContent = ui.label;
          }
        }

        if (reservationModalOverlay && reservationModalOverlay.classList.contains('open') && currentReservationId && reservationStatusBadge) {
          const card = document.querySelector(`.card[data-type="reservation"][data-reservation-id="${currentReservationId}"]`);
          if (card) {
            const s = card.dataset.status || 'pending';
            const ui = mapDbStatusToUi(s === 'completed' ? 'confirmed' : s);
            reservationStatusBadge.classList.remove('completed', 'cancelled', 'pending');
            reservationStatusBadge.classList.add(ui.className);
            reservationStatusBadge.textContent = ui.label;
          }
        }
      }

      async function fetchStatusesAndUpdateUI() {
        try {
          const res = await fetch(STATUS_URL + '?t=' + Date.now());
          if (!res.ok) {
            console.error('HTTP error status:', res.status);
            return;
          }

          const data = await res.json();
          if (!data || !data.success) {
            console.error('Response success=false atau format salah');
            return;
          }

          if (Array.isArray(data.orders)) {
            data.orders.forEach(o => {
              if (o && typeof o.id !== 'undefined') {
                updateOrderCardStatusFromDb(o.id, o.status);
              }
            });
          }

          if (Array.isArray(data.reservations)) {
            data.reservations.forEach(r => {
              if (r && typeof r.id !== 'undefined') {
                updateReservationCardStatusFromDb(r.id, r.status);
              }
            });
          }

          refreshStatusBadgesInOpenModals();
        } catch (err) {
          console.error('Gagal mengambil status transaksi:', err);
        }
      }

      // Panggil pertama kali & set interval 2 detik
      fetchStatusesAndUpdateUI();
      setInterval(fetchStatusesAndUpdateUI, 2000);
    })();
  </script>
</body>
</html>
<?php
$mysqli->close();
?>
