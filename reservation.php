<?php
// ==========================================
// 1. SIMULASI DATA (BACKEND)
// ==========================================

$floor_tables = ['Bar', 'A1', 'A2', 'B1', 'B2', 'B3', 'C1', 'C2'];
// Jam operasional: 10:00 sampai 22:00 (13 kolom)
$hours = range(10, 22); 

// Data Reservasi Dummy
$reservations = [
    ['table' => 'Bar', 'hour' => 13, 'name' => 'John Doe',      'guests' => 1, 'id' => '01'],
    ['table' => 'Bar', 'hour' => 17, 'name' => 'John Doe',      'guests' => 1, 'id' => '01'],
    ['table' => 'A1',  'hour' => 18, 'name' => 'Jane Smith',    'guests' => 2, 'id' => '02'],
    ['table' => 'A2',  'hour' => 11, 'name' => 'Mike Johnson',  'guests' => 4, 'id' => '04'],
    ['table' => 'A2',  'hour' => 15, 'name' => 'Sarah Lee',     'guests' => 3, 'id' => '03'],
    ['table' => 'B1',  'hour' => 11, 'name' => 'Tom Brown',     'guests' => 2, 'id' => '02'],
    ['table' => 'B2',  'hour' => 15, 'name' => 'Emma Davis',    'guests' => 5, 'id' => '05'],
    ['table' => 'B3',  'hour' => 12, 'name' => 'Alex Wilson',   'guests' => 3, 'id' => '03'],
    ['table' => 'C2',  'hour' => 19, 'name' => 'Lisa Anderson', 'guests' => 6, 'id' => '06'],
];

function getReservation($table, $hour, $data) {
    foreach ($data as $res) {
        if ($res['table'] === $table && $res['hour'] === $hour) {
            return $res;
        }
    }
    return null;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Bitehive — Reservation</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    /* =========================================
       1. STYLE UTAMA (INTEGRASI CSS BARU)
       ========================================= */
    :root {
      --sidebar-w: 260px;
      --bg: #0b0c0d;
      --panel: #161818;
      --muted: #9aa0a2;
      --accent: #17b79a;
      --card: #222426;
      --text: #e6f0ef;
      --sub: #aeb7b6;
      --radius: 16px;
      --icon-blue: #1e90ff;
      --danger: #ff4747;
      
      /* UKURAN GRID - Sesuaikan disini */
      --grid-label-width: 100px;  
      --grid-slot-width: 110px;   
      --grid-row-height: 80px;    
      --grid-header-height: 50px; 
      
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      font-size: 15px;
    }

    /* RESET */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; background: linear-gradient(180deg, #0b0c0d 0%, #0c0d0e 100%); color: var(--text); -webkit-font-smoothing: antialiased; overflow: hidden; }
    .app { min-height: 100vh; display: flex; }

    /* SIDEBAR */
    .sidebar {
      position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-w);
      background: linear-gradient(180deg, rgba(26,29,29,.98), rgba(20,22,22,.98));
      border-radius: 0 18px 18px 0; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;
      box-shadow: 4px 0 24px rgba(0,0,0,.6); border-right: 1px solid rgba(255,255,255,.03); z-index: 50;
    }
    .brand { font-weight:700; color:var(--accent); font-size:20px; padding-bottom:8px; }
    .nav { display:flex; flex-direction:column; gap:12px; margin-top: 18px; }
    
    .nav-item { display:flex; align-items:center; gap:12px; padding:10px; border-radius:12px; background:transparent; color:var(--sub); border:0; cursor:pointer; width:100%; transition: background .18s, color .18s, transform .12s; text-decoration: none; }
    .nav-item:hover { background: rgba(30,144,255,.06); color:#dceeff; }
    
    .icon { width:48px; height:48px; border-radius:10px; display:inline-block; background-color: rgba(30,144,255,.08); -webkit-mask-position:center; mask-position:center; -webkit-mask-repeat:no-repeat; mask-repeat:no-repeat; -webkit-mask-size:60% 60%; mask-size:60% 60%; transition: background-color .18s, -webkit-mask-size .18s; }
    .nav-item .label { font-weight:600; font-size:15px; color:var(--sub); transition: color .18s; }
    
    /* ACTIVE STATE */
    .nav-item.active { background: linear-gradient(180deg, rgba(30,144,255,.12), rgba(30,144,255,.04)); color:var(--icon-blue); box-shadow:0 8px 20px rgba(30,144,255,.06); }
    .nav-item.active .icon { background-color: var(--icon-blue); -webkit-mask-size:72% 72%; mask-size:72% 72%; }
    .nav-item.active .label { color:var(--icon-blue); }

    .sidebar-bottom { display:flex; align-items:center; gap:12px; }
    .logout { width:48px; height:48px; border-radius:50%; border:1px solid rgba(255,255,255,.06); background-color: rgba(30,144,255,.06); cursor:pointer; -webkit-mask-position:center; mask-position:center; -webkit-mask-repeat:no-repeat; mask-repeat:no-repeat; -webkit-mask-size:60% 60%; mask-size:60% 60%; transition: background-color .16s, transform .12s; }
    .logout:hover { background-color: var(--icon-blue); transform: translateY(-2px); }

    /* MAIN */
    .main { margin-left: var(--sidebar-w); padding: 20px; flex: 1; min-height: 100vh; overflow: hidden; }
    .main-inner { height: 100vh; overflow-y: auto; padding-right: 12px; scrollbar-width: none; padding-bottom: 40px; }
    .main-inner::-webkit-scrollbar { display: none; }

    .topbar { display:flex; justify-content:space-between; align-items:center; padding:8px 6px; margin-bottom:12px; position:sticky; top:0; backdrop-filter: blur(4px); z-index:20; }
    .title-wrap { display:flex; align-items:center; gap:12px; }
    .chev { background: rgba(255,255,255,.02); width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--sub); }
    .title { font-size:22px; color:var(--text); }
    .user { display:flex; align-items:center; gap:12px; color:var(--sub); }
    .avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid rgba(23,201,176,.12); }

    /* CONTROLS */
    .controls { display:flex; justify-content:space-between; align-items:center; padding:8px 6px; margin-bottom:16px; flex-wrap: wrap; gap: 12px; }
    .tabs { display:flex; gap:12px; flex-wrap:wrap; }
    .tab { padding:10px 16px; border-radius:10px; background:transparent; border:0; cursor:pointer; color:var(--sub); font-weight: 600; transition: all 0.3s; }
    .tab.active { background: var(--accent); color:#06221e; }
    
    .right-controls { display:flex; gap:12px; align-items:center; }
    .date-select { padding:10px 16px; background: var(--panel); border:1px solid rgba(255,255,255,.03); border-radius:10px; color:var(--text); cursor: pointer; font-weight: 600; }
    .btn-add { padding:10px 20px; background: var(--accent); border:0; border-radius:10px; color:#06221e; font-weight:700; cursor:pointer; transition: all 0.3s; }
    .btn-add:hover { background: #15a887; }

    /* =========================================
       2. GRID RESERVATION SYSTEM (CSS GRID)
       ========================================= */
    .table-container { 
        background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(0,0,0,.2)); 
        border-radius:14px; 
        border:1px solid rgba(255,255,255,.03); 
        box-shadow:0 12px 30px rgba(0,0,0,.6); 
        display:flex; flex-direction:column;
        position: relative;
        overflow: hidden; 
    }

    .grid-scroll-area {
        overflow: auto; 
        max-height: calc(100vh - 240px);
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.1) rgba(0,0,0,.2);
    }
    .grid-scroll-area::-webkit-scrollbar { width: 8px; height: 8px; }
    .grid-scroll-area::-webkit-scrollbar-track { background: #0b0c0d; }
    .grid-scroll-area::-webkit-scrollbar-thumb { background: #2c2f33; border-radius: 4px; }

    .grid-table {
        display: grid;
        /* Dinamis: Kolom label + Repeat jumlah jam */
        grid-template-columns: var(--grid-label-width) repeat(<?php echo count($hours); ?>, var(--grid-slot-width)); 
        min-width: max-content;
    }

    /* Header Style */
    .header-row { display: contents; } /* Ignore this div, treat children as grid items if wrapped */

    .header-cell {
        background: #161818;
        height: var(--grid-header-height);
        padding: 0 10px;
        display:flex; align-items:center; justify-content:center;
        font-size: 13px; font-weight: 700; color: var(--muted);
        border-bottom: 1px solid rgba(255,255,255,.05);
        border-right: 1px solid rgba(255,255,255,.03);
        position: sticky; top: 0; z-index: 10;
    }

    .header-cell.corner {
        position: sticky; left: 0; top: 0; z-index: 20;
        border-right: 1px solid rgba(255,255,255,.08);
        width: var(--grid-label-width);
    }

    /* Body Cells */
    .row-label {
        position: sticky; left: 0; z-index: 5;
        background: #1a1d1e;
        width: var(--grid-label-width);
        height: var(--grid-row-height);
        padding-left: 24px;
        font-weight: 700; color: var(--text);
        border-right: 1px solid rgba(255,255,255,.08);
        border-bottom: 1px solid rgba(255,255,255,.03);
        display: flex; align-items: center;
    }

    .cell {
        height: var(--grid-row-height);
        padding: 6px; 
        border-right: 1px solid rgba(255,255,255,.03);
        border-bottom: 1px solid rgba(255,255,255,.03);
        display: flex; align-items: center; justify-content: center;
    }

    .reservation-block {
        background: linear-gradient(135deg, var(--accent), #15a887);
        color: #04221e; width: 100%; height: 100%; border-radius: 8px;
        cursor: pointer; padding: 4px 8px;
        display: flex; flex-direction: column; justify-content: center; gap: 2px;
        transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .reservation-block:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(23,183,154,0.4); }
    .reservation-name { font-size: 12px; font-weight: 700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .reservation-info { font-size: 11px; opacity: 0.9; font-weight: 600; }

    /* MODAL */
    .modal-overlay { position: fixed; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); z-index:200; opacity:0; pointer-events:none; transition: opacity .2s ease; backdrop-filter: blur(2px); }
    .modal-overlay.open { opacity:1; pointer-events:auto; }
    .modal { width: min(500px, 90vw); background: linear-gradient(180deg, #1a1c1d, #121212); border-radius:16px; padding:24px; border:1px solid rgba(255,255,255,.05); box-shadow:0 30px 80px rgba(0,0,0,.8); transform: translateY(20px); transition: transform .3s; }
    .modal-overlay.open .modal { transform: translateY(0); }
    .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,.05); }
    .modal-title { font-size: 18px; font-weight: 700; color: var(--text); }
    .modal-close { background: transparent; border: 0; color: var(--sub); font-size:24px; cursor:pointer; line-height:1; padding:4px; }
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,.03); color: var(--sub); font-size: 14px; }
    .detail-row:last-of-type { border-bottom: none; }
    .detail-label { color: var(--muted); font-weight: 500; }
    .detail-value { color: var(--text); font-weight: 600; }
    .modal-footer { margin-top: 24px; display: flex; gap: 12px; }
    .btn { flex: 1; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; border: 0; font-size: 14px; }
    .btn-primary { background: var(--accent); color: #04221e; }
    .btn-secondary { background: rgba(255,255,255,.05); color: var(--sub); }
    .btn-secondary:hover { background: rgba(255,255,255,.1); color: var(--text); }
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>
        <nav class="nav">
          <a href="#" class="nav-item"><span class="icon" style="-webkit-mask-image: url('icon/layout-dashboard.png'); mask-image: url('icon/layout-dashboard.png');"></span><span class="label">Dashboard</span></a>
          <a href="#" class="nav-item"><span class="icon" style="-webkit-mask-image: url('icon/chart-pie.png'); mask-image: url('icon/chart-pie.png');"></span><span class="label">Order</span></a>
          <a href="#" class="nav-item active"><span class="icon" style="-webkit-mask-image: url('icon/calendar-arrow-up.png'); mask-image: url('icon/calendar-arrow-up.png');"></span><span class="label">Reservation</span></a>
          <a href="transaction1.php" class="nav-item"><span class="icon" style="-webkit-mask-image: url('icon/arrow-left-right.png'); mask-image: url('icon/arrow-left-right.png');"></span><span class="label">Transaction</span></a>
        </nav>
      </div>
      <div class="sidebar-bottom">
        <button class="logout" style="-webkit-mask-image: url('icon/log-out.png'); mask-image: url('icon/log-out.png');"></button>
      </div>
    </aside>

    <main class="main">
      <div class="main-inner">
        <header class="topbar">
          <div class="title-wrap">
            <div class="chev">›</div>
            <h1 class="title">Reservation</h1>
          </div>
          <div class="user">
            <div class="user-text">CUSTOMER</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="user avatar">
          </div>
        </header>

        <section class="controls">
          <div class="tabs">
            <button class="tab active">1st Floor</button>
            <button class="tab">2nd Floor</button>
            <button class="tab">3rd Floor</button>
          </div>
          <div class="right-controls">
            <div class="date-select">Today ▼</div>
            <button class="btn-add" onclick="addNewReservation()">Add New Reservation</button>
          </div>
        </section>

        <section class="table-container">
          <div class="grid-scroll-area">
            <div class="grid-table">
                
                <div class="header-cell corner"></div>
                <?php foreach($hours as $hour): ?>
                    <div class="header-cell">
                        <?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                    </div>
                <?php endforeach; ?>

                <?php foreach($floor_tables as $table): ?>
                    
                    <div class="row-label"><?php echo htmlspecialchars($table); ?></div>

                    <?php foreach($hours as $hour): ?>
                        <div class="cell">
                            <?php 
                                $res = getReservation($table, $hour, $reservations);
                                if($res): 
                            ?>
                                <div class="reservation-block" onclick="viewReservation(
                                    '<?php echo $table; ?>', 
                                    '<?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?>', 
                                    '<?php echo $res['name']; ?>', 
                                    '<?php echo $res['guests']; ?>'
                                )">
                                    <div class="reservation-name"><?php echo $res['name']; ?></div>
                                    <div class="reservation-info">👥 <?php echo str_pad($res['guests'], 2, '0', STR_PAD_LEFT); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php endforeach; ?>
            
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>

  <div id="modal-overlay" class="modal-overlay" aria-hidden="true">
    <div class="modal" role="document">
      <header class="modal-header">
        <div class="modal-title">Reservation Details</div>
        <button id="modal-close" class="modal-close">&times;</button>
      </header>
      <div class="modal-content" id="modal-content"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        <button class="btn btn-primary" id="proceed-payment">Proceed to Payment</button>
      </div>
    </div>
  </div>

  <script>
    // Script sama, hanya URL transaction yang disesuaikan
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
      });
    });

    function viewReservation(table, time, customer, guests) {
      const modal = document.getElementById('modal-overlay');
      const content = document.getElementById('modal-content');
      
      content.innerHTML = `
        <div class="detail-row"><span class="detail-label">Table</span><span class="detail-value">${table}</span></div>
        <div class="detail-row"><span class="detail-label">Time</span><span class="detail-value">${time}</span></div>
        <div class="detail-row"><span class="detail-label">Customer Name</span><span class="detail-value">${customer}</span></div>
        <div class="detail-row"><span class="detail-label">Guests</span><span class="detail-value">${guests} person(s)</span></div>
        <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value">Today - Nov 22, 2025</span></div>
        <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value" style="color: var(--accent);">✓ Confirmed</span></div>
      `;
      
      document.getElementById('proceed-payment').onclick = function() {
        // Pastikan ini mengarah ke file PHP
        window.location.href = `transaction1.php?action=pay&table=${table}`;
      };
      
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
      const modal = document.getElementById('modal-overlay');
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
    }

    function addNewReservation() {
      alert("Redirecting to Add Reservation Form...");
    }

    document.getElementById('modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-overlay').addEventListener('click', (e) => { if (e.target.id === 'modal-overlay') closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  </script>
</body>
</html>