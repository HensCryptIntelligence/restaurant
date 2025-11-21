
<?php
session_start();
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bitehive — Inventory Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      /* Color Palette from Selection */
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
      color: var(--pink-primary);
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
      background: rgba(250, 193, 217, 0.08);
      color: var(--pink-light);
      transform: translateX(4px);
    }

    .nav-item.active {
      background: linear-gradient(135deg, rgba(250, 193, 217, 0.15), rgba(250, 193, 217, 0.08));
      color: var(--pink-primary);
      box-shadow: 0 4px 16px rgba(250, 193, 217, 0.1);
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
      stroke: var(--pink-primary);
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
      background: rgba(250, 193, 217, 0.08);
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
      margin-left: var(--sidebar-width);
      padding: 20px;
      flex: 1;
      min-height: 100vh;
      transition: margin-left var(--transition-base);
      width: calc(100% - var(--sidebar-width));
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
      padding: 16px 20px;
      margin-bottom: 24px;
      background: var(--dark-primary);
      border-radius: var(--radius-lg);
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
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
      background: rgba(250, 193, 217, 0.08);
      border-radius: var(--radius-sm);
      cursor: pointer;
      align-items: center;
      justify-content: center;
      transition: all var(--transition-fast);
    }

    .menu-toggle svg {
      width: 24px;
      height: 24px;
      stroke: var(--pink-primary);
    }

    .menu-toggle:hover {
      background: rgba(250, 193, 217, 0.15);
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
      font-size: 14px;
      font-weight: 600;
      color: var(--gray-light);
      letter-spacing: 0.5px;
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--pink-primary);
    }

    /* Content Area */
    .content {
      background: var(--dark-primary);
      border-radius: var(--radius-lg);
      padding: 24px;
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
  </style>
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

          <button class="nav-item" data-target="user-management" aria-label="User Management">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <span class="label">User Management</span>
          </button>

          <button class="nav-item active" data-target="inventory" aria-label="Inventory">
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

          <button class="nav-item" data-target="transaction" aria-label="Transaction">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 17V5a2 2 0 0 0-2-2H4"/>
                <path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>
              </svg>
            </span>
            <span class="label">Transaction</span>
          </button>

          <button class="nav-item" data-target="activity-log" aria-label="Activity Log">
            <span class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 14v2.2l1.6 1"/>
                <path d="M16 4h2a2 2 0 0 1 2 2v.832"/>
                <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"/>
                <circle cx="16" cy="16" r="6"/>
                <rect x="8" y="2" width="8" height="4" rx="1"/>
              </svg>
            </span>
            <span class="label">Activity Log</span>
          </button>
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

    <!-- Main Content -->
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
            <div class="user-text">ADMIN</div>
            <img class="avatar" src="https://i.pravatar.cc/40" alt="Admin avatar">
          </div>
        </header>

        <div class="content">

          <!-- Content goes here -->
          <?php 
            include __DIR__ . '/table-content/index_menu.php';
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
    logoutBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to log out?')) {
        // Clear localStorage
        localStorage.removeItem('sidebarOpen');
        localStorage.removeItem('activeMenu');
        // Redirect to login page or perform logout action
        console.log('Logging out...');
        // window.location.href = '/login';
      }
    });

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });
  </script>
</body>
</html>