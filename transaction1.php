<?php
// ====== KONFIGURASI DATABASE ======
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'restaurant'; // sudah sesuai yang kamu tulis

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}

// ====== HELPER FUNCTION ======
function getUserName($mysqli, $id_user) {
    $id_user = (int)$id_user;
    if ($id_user <= 0) {
        return 'Unknown';
    }

    $default = 'User #' . $id_user;

    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id_user = ? LIMIT 1");
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
        // tabel kamu pakai 'fullname'
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

$reservations = [];
$resSql = "
    SELECT
        tr.id_transaction_reservation,
        tr.id_user,
        tr.id_reservation,
        tr.status AS trx_status,
        tr.created_at,
        r.seats,
        r.reservation_date,
        r.reservation_start,
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
  <title>Bitehive — Transaction UI</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

  <link rel="stylesheet" href="transaction1.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar" aria-label="Sidebar navigation">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>

        <nav class="nav" role="navigation" aria-label="Main navigation">
          <button class="nav-item" id="nav-dashboard" aria-label="Dashboard" data-target="dashboard">
            <span class="icon" style="-webkit-mask-image: url('icon/layout-dashboard.png'); mask-image: url('icon/layout-dashboard.png');"></span>
            <span class="label">Dashboard</span>
          </button>

          <button class="nav-item" id="nav-order" aria-label="Order" data-target="order">
            <span class="icon" style="-webkit-mask-image: url('icon/chart-pie.png'); mask-image: url('icon/chart-pie.png');"></span>
            <span class="label">Order</span>
          </button>

          <button class="nav-item" id="nav-reservation" aria-label="Reservation" data-target="reservation">
            <span class="icon" style="-webkit-mask-image: url('icon/calendar-arrow-up.png'); mask-image: url('icon/calendar-arrow-up.png');"></span>
            <span class="label">Reservation</span>
          </button>

          <button class="nav-item active" id="nav-transaction" aria-label="Transaction" data-target="transaction">
            <span class="icon" style="-webkit-mask-image: url('icon/arrow-left-right.png'); mask-image: url('icon/arrow-left-right.png');"></span>
            <span class="label">Transaction</span>
          </button>
        </nav>
      </div>

      <div class="sidebar-bottom">
        <button class="logout" aria-label="Logout"
        style="-webkit-mask-image: url('icon/log-out.png'); mask-image: url('icon/log-out.png');"></button>
      </div>
    </aside>

    <main class="main">
      <div class="main-inner">
        <header class="topbar">
          <div class="title-wrap">
            <div class="chev">›</div>
            <h1 class="title">Transaction</h1>
          </div>

          <div class="user">
            <div class="user-text">CUSTOMER</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="user avatar">
          </div>
        </header>

        <section class="controls">
          <div class="tabs" role="tablist" aria-label="Filter transactions">
            <button class="tab active" data-filter="all">All</button>
            <button class="tab" data-filter="completed">Completed</button>
            <button class="tab" data-filter="cancelled">Cancelled</button>
            <button class="tab" data-filter="reservation">Reservation</button>
          </div>

          <div class="search-wrapper">
            <div class="search-container">
              <input id="search" placeholder="Search a name, order or etc" aria-label="Search"/>
              <div id="search-history" class="search-history"></div>
            </div>
            <button class="search-btn" aria-label="search"><i class="fas fa-search"></i></button>
          </div>
        </section>

        <section id="cards" class="cards-grid">
          <?php
          $badgeNo = 1;

          foreach ($orders as $order):
              $badge = str_pad($badgeNo, 2, '0', STR_PAD_LEFT);

              $statusDb = $order['trx_status'];
              if ($statusDb === 'confirmed') {
                  $statusClass = 'completed';
                  $statusLabel = '✓ Completed';
              } else {
                  $statusClass = 'cancelled';
                  $statusLabel = '✕ Cancelled';
              }

              $ts = $order['created_at'] ? strtotime($order['created_at']) : time();
              $customerName = getUserName($mysqli, $order['id_user']);

              $totalAmount = $order['total_amount'];

              $dateText = date('l, d-m-Y', $ts);
              $timeText = date('h : i A', $ts);  
          ?>
          <article class="card" data-status="<?php echo $statusClass; ?>">
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
                <div class="items-header"><div>Serial Items</div><div>Qty</div></div>
                <?php
                ?>
                <div class="item">
                  <div class="left"># 01 <?php echo htmlspecialchars($order['name_item']); ?></div>
                  <div class="right"><?php echo (int)$order['quantity']; ?></div>
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

          foreach ($reservations as $reservation):
              $badge = str_pad($badgeNo, 2, '0', STR_PAD_LEFT);

              $statusDb = $reservation['trx_status']; // 'pending' / 'confirmed'
              if ($statusDb === 'confirmed') {
                  $statusClass = 'completed';
                  $statusLabel = '✓ Completed';
              } elseif ($statusDb === 'cancelled') {
                  $statusClass = 'cancelled';
                  $statusLabel = '✕ Cancelled';
              } else {
                  $statusClass = 'cancelled'; // styling saja
                  $statusLabel = '⌛ Pending';
              }

              $ts = $reservation['created_at'] ? strtotime($reservation['created_at']) : time();
              $customerName = getUserName($mysqli, $reservation['id_user']);

              $dateText = date('d-m-Y', $ts);
              $timeText = date('h:i A', $ts);
              $seats = (int)$reservation['seats'];
              $deposit = $reservation['total_amount']; // dari payment_reservation
          ?>
          <article class="card reservation-card" data-status="reservation">
            <div class="reservation-image-container">
              <img src="foto/download.jpg" alt="Table photo">
              <div class="reservation-overlay-info">
                <div class="badge"><?php echo $badge; ?></div>
                <div class="overlay-meta">
                  <div class="name">Table #<?php echo htmlspecialchars($reservation['id_reservation']); ?></div>
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

              <button class="view-reservation">View All</button>
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

  <!-- Modal overlay for "View All" (order details) -->
  <div id="modal-overlay" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal" role="document">
      <header class="modal-header">
        <div class="modal-breadcrumbs">
          <span class="crumb">Transaction</span>
          <span class="crumb sep">›</span>
          <span class="crumb current">Detail of Order</span>
        </div>

        <button id="modal-close" class="modal-close" aria-label="Close detail">&times;</button>
      </header>

      <div class="modal-content">
        <div class="detail-top">
          <div class="detail-left">
            <div class="badge large" id="modal-badge">02</div>
            <div class="meta">
              <div class="name" id="modal-name">Watson Joyce</div>
              <div class="order" id="modal-order">Order #002</div>
            </div>
          </div>

          <div class="detail-right">
            <div class="date" id="modal-date">Wednesday, 28-08-2025</div>
            <div id="modal-status-btn" class="status-btn completed">✓ Completed</div>
          </div>
        </div>

        <hr />

        <div class="detail-table-wrap">
          <table class="detail-table" aria-label="Detail items">
            <thead>
              <tr>
                <th>ID Items</th>
                <th>Name of product</th>
                <th>Quantity</th>
                <th>Prices</th>
                <th>Sub Total</th>
              </tr>
            </thead>
            <tbody id="modal-items">
              <!-- rows injected by JS -->
            </tbody>
          </table>
        </div>

        <div class="detail-totals">
          <div class="totals-left">
            <div class="totals-badge">💵</div>
          </div>
          <div class="totals-right">
            <div class="totals-row"><span>Total Prices</span><strong id="modal-total">IDR 3.300.000,00</strong></div>
            <div class="totals-row"><span>Disc. 0%</span><strong>IDR 0</strong></div>
            <div class="totals-row"><span>Tax 0%</span><strong>IDR 0</strong></div>
            <div class="totals-row"><span>Total</span><strong id="modal-grand">IDR 3.300.000,00</strong></div>
            <div class="totals-row"><span>Received</span><strong>IDR 3.500.000,00</strong></div>
            <div class="totals-row"><span>Return</span><strong>IDR 200.000,00</strong></div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal overlay for Reservation Detail -->
  <div id="res-modal-overlay" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal modal-reservation" role="document">
      <header class="modal-header">
        <div class="modal-breadcrumbs">
          <span class="crumb">Transaction</span>
          <span class="crumb sep">></span>
          <span class="crumb current">Detail of Reservation</span>
        </div>

        <button id="res-modal-close" class="modal-close" aria-label="Close reservation detail">&times;</button>
      </header>

      <div class="modal-content reservation-detail-content">
        <div class="reservation-detail-left">
          <img id="res-modal-image" src="foto/download.jpg" alt="Table photo">
        </div>

        <div class="reservation-detail-right">
          <h2 id="res-modal-table">Table #01</h2>
          <div class="res-meta">
            <div><strong>Reservation ID:</strong> <span id="res-modal-id">#12354564</span></div>
            <div><strong>Customer:</strong> <span id="res-modal-customer">Watson Joyce</span></div>
            <div><strong>Seats:</strong> <span id="res-modal-seats">05 persons</span></div>
            <div><strong>Reservation Date:</strong> <span id="res-modal-date">28.03.2024</span></div>
            <div><strong>Time:</strong> <span id="res-modal-time">03 : 00 PM</span></div>
            <div><strong>Deposit:</strong> <span id="res-modal-deposit">IDR 150.000</span></div>
          </div>

          <div style="margin-top:18px;">
            <button id="btn-cancel-reservation" class="btn-ghost">Cancel Reservation</button>
            <button id="btn-change-table" class="btn-primary">Change Table</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script>
    /* NAV ACTIVE */
    (function () {
      const navItems = document.querySelectorAll('.nav-item');
      navItems.forEach(item => {
        item.addEventListener('click', () => {
          navItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          navItems.forEach(i => i.setAttribute('aria-current', i.classList.contains('active') ? 'page' : 'false'));
          const label = item.querySelector('.label')?.textContent || '';
          document.querySelector('.title').textContent = label;
        });
      });
    })();

    (function () {
      const allCards = document.querySelectorAll('.card:not(.reservation-card)');
      allCards.forEach(card => {
        const items = card.querySelectorAll('.items .item');
        items.forEach((item, idx) => {
          if (idx >= 3) {
            item.classList.add('hidden-item');
          }
        });
      });
    })();

    (function () {
      const tabs = document.querySelectorAll('.tab');
      const cards = document.querySelectorAll('.card');
      const search = document.getElementById('search');

      tabs.forEach(t => t.addEventListener('click', () => {
        tabs.forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        filterCards(t.dataset.filter, search.value);
      }));

      search.addEventListener('input', (e) => {
        const activeTab = document.querySelector('.tab.active').dataset.filter;
        filterCards(activeTab, e.target.value);
      });

      function filterCards(filter, q) {
        q = (q || '').toLowerCase().trim();
        cards.forEach(card => {
          const status = card.dataset.status;
          const name = (card.querySelector('.name')?.textContent || '').toLowerCase();
          const order = (card.querySelector('.order')?.textContent || '').toLowerCase();
          const isReservation = status === 'reservation';
          const matchesFilter = (filter === 'all') || (filter === 'reservation' && isReservation) || (filter === status);
          const matchesQuery = !q || name.includes(q) || order.includes(q) || card.textContent.toLowerCase().includes(q);
          card.style.display = (matchesFilter && matchesQuery) ? '' : 'none';
        });
      }

      filterCards('all', '');
    })();

    /* VIEW ALL (order) MODAL LOGIC */
    (function () {
      const viewBtns = document.querySelectorAll('.viewall');
      const modalOverlay = document.getElementById('modal-overlay');
      const modalClose = document.getElementById('modal-close');

      const modalBadge = document.getElementById('modal-badge');
      const modalName = document.getElementById('modal-name');
      const modalOrder = document.getElementById('modal-order');
      const modalDate = document.getElementById('modal-date');
      const modalItems = document.getElementById('modal-items');
      const modalTotal = document.getElementById('modal-total');
      const modalGrand = document.getElementById('modal-grand');
      const modalStatusBtn = document.getElementById('modal-status-btn');

      function formatIDR(n) {
        return 'IDR ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }

      viewBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
          const card = e.target.closest('.card');
          if (!card) return;

          const badge = card.querySelector('.badge')?.textContent.trim() || '';
          const name = card.querySelector('.name')?.textContent.trim() || '';
          const order = card.querySelector('.order')?.textContent.trim() || '';
          const date = card.querySelector('.row.info > div')?.textContent.trim() || '';
          const status = card.dataset.status || 'completed';

          const itemNodes = Array.from(card.querySelectorAll('.item'));
          const items = itemNodes.map((it, idx) => {
            const id = '#00' + (idx+1);
            const product = it.querySelector('.left')?.textContent.trim() || 'Product';
            const qty = parseInt(it.querySelector('.right')?.textContent.trim() || '1', 10);
            const price = 330000; // sama seperti HTML asli
            return { id, product, qty, price, subtotal: price * qty };
          });

          const total = items.reduce((s, it) => s + it.subtotal, 0);

          modalBadge.textContent = badge;
          modalName.textContent = name;
          modalOrder.textContent = order;
          modalDate.textContent = date;

          modalStatusBtn.classList.remove('completed', 'cancelled');
          modalStatusBtn.classList.add(status === 'cancelled' ? 'cancelled' : 'completed');
          modalStatusBtn.textContent = status === 'cancelled' ? '✕ Cancelled' : '✓ Completed';

          modalItems.innerHTML = items.map(it => `
            <tr>
              <td>${it.id}</td>
              <td class="td-product">${it.product}</td>
              <td>${it.qty}</td>
              <td>${formatIDR(it.price)}</td>
              <td>${formatIDR(it.subtotal)}</td>
            </tr>
          `).join('');

          modalTotal.textContent = formatIDR(total);
          modalGrand.textContent = formatIDR(total);

          modalOverlay.setAttribute('aria-hidden', 'false');
          modalOverlay.classList.add('open');
        });
      });

      function closeModal() {
        modalOverlay.classList.remove('open');
        modalOverlay.setAttribute('aria-hidden', 'true');
      }

      modalClose.addEventListener('click', closeModal);
      modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) closeModal(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modalOverlay.classList.contains('open')) closeModal(); });
    })();

    (function () {
      const resBtns = document.querySelectorAll('.view-reservation');
      const resOverlay = document.getElementById('res-modal-overlay');
      const resClose = document.getElementById('res-modal-close');

      const resImage = document.getElementById('res-modal-image');
      const resTable = document.getElementById('res-modal-table');
      const resId = document.getElementById('res-modal-id');
      const resCustomer = document.getElementById('res-modal-customer');
      const resSeats = document.getElementById('res-modal-seats');
      const resDate = document.getElementById('res-modal-date');
      const resTime = document.getElementById('res-modal-time');
      const resDeposit = document.getElementById('res-modal-deposit');

      resBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
          const card = e.target.closest('.card.reservation-card');
          if (!card) return;

          const tableName = card.querySelector('.overlay-meta .name')?.textContent.trim() || 'Table';
          const reservationId = card.querySelector('.overlay-meta .order')?.textContent.trim() || '#id';
          const datetime = card.querySelector('.reservation-datetime')?.textContent.trim() || '';
          const seats = card.querySelector('.info-value')?.textContent.trim() || 'n/a';
          const infoItems = card.querySelectorAll('.info-item');
          const customer = infoItems[2]?.querySelector('.info-value')?.textContent.trim() || '';
          const deposit = infoItems[1]?.querySelector('.info-value')?.textContent.trim() || '';
          const img = card.querySelector('.reservation-image-container img')?.getAttribute('src') || 'foto/download.jpg';

          resImage.src = img;
          resTable.textContent = tableName;
          resId.textContent = reservationId;
          resCustomer.textContent = customer;
          resSeats.textContent = seats;
          
          if (datetime.indexOf('•') > -1) {
            const parts = datetime.split('•').map(s => s.trim());
            resDate.textContent = parts[0];
            resTime.textContent = parts[1];
          } else {
            resDate.textContent = datetime;
            resTime.textContent = '';
          }
          resDeposit.textContent = deposit;

          resOverlay.setAttribute('aria-hidden', 'false');
          resOverlay.classList.add('open');
        });
      });

      function closeResModal() {
        resOverlay.classList.remove('open');
        resOverlay.setAttribute('aria-hidden', 'true');
      }

      resClose.addEventListener('click', closeResModal);
      resOverlay.addEventListener('click', (e) => { if (e.target === resOverlay) closeResModal(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && resOverlay.classList.contains('open')) closeResModal(); });
    })();
  </script>

</body>
</html>
<?php
$mysqli->close();
?>
