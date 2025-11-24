<?php
  session_start();

  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Pragma: no-cache");


  if (!isset($_SESSION['role']) || $_SESSION['role'] !== "customer") {
      header("Location: ../../auth/views/login.php");
      exit;
  }
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bitehive — Customer Home</title>
  <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
  <div class="app">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
      <div class="sidebar-top">
        <div class="brand">Bitehive</div>
        <nav class="nav">

        <a href="../home/index.php" class="nav-link-wrapper">
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

        <a href="../reservation/customer_reservation.php" class="nav-link-wrapper"></a>
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
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
              </svg>
            </button>
            <h1 class="title">Inventory</h1>
          </div>

          <div class="user">
            <div class="user-text">CUSTOMER</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="Customer avatar">
          </div>
        </header>

        <div class="content">

        <?php 
            include __DIR__ . '/home.php';
          ?>

        </div>
      </div>
    </main>
  </div>

  <script>
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