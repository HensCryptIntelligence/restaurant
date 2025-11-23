<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bitehiver</title>
    <style>
        /* CSS Variables - Extracted from UI Reference */
        :root {
            --bg-primary: #0f1112;
            --bg-panel: #17191c;
            --bg-card: #1e2023;
            --border-subtle: rgba(255, 255, 255, 0.05);
            --text-bright: #e4e7eb;
            --text-medium: #9ba0a5;
            --text-dim: #6b7280;
            --accent-green: #17C3B2;
            --accent-link: #17C3B2;
            --hover-card: #252830;
        }

        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-bright);
            line-height: 1.6;
        }

        /* Layout Container */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            overflow-x: hidden;
        }

        /* Header */
        .content-header {
            background-color: var(--black);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 50;
            margin-bottom: 3.5rem;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-green);
            margin-bottom: 1rem;
        }

        .header-description {
            color: var(--text-medium);
            font-size: 0.95rem;
            line-height: 1.5;
            max-width: 900px;
        }

        /* Grid Layout */
        .dishes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* Popular Dishes Section */
        .popular-section {
            background-color: var(--bg-panel);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-subtle);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-bright);
        }

        .see-all-link {
            color: var(--accent-link);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .see-all-link:hover {
            color: var(--accent-green);
        }

        /* Menu List */
        .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .menu-list::-webkit-scrollbar {
            width: 6px;
        }

        .menu-list::-webkit-scrollbar-track {
            background: var(--bg-card);
            border-radius: 3px;
        }

        .menu-list::-webkit-scrollbar-thumb {
            background: var(--text-dim);
            border-radius: 3px;
        }

        /* Menu Card */
        .menu-card {
            background-color: var(--bg-card);
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .menu-card:hover {
            background-color: var(--hover-card);
            border-color: var(--border-subtle);
        }

        .menu-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .menu-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-bright);
        }

        .menu-status {
            font-size: 0.75rem;
            color: var(--accent-green);
            font-weight: 500;
        }

        .menu-status.out-of-stock {
            color: #ef4444;
        }

        .menu-card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-items-count {
            font-size: 0.875rem;
            color: var(--text-medium);
        }

        .menu-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-bright);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {

            .content-header h1 {
                font-size: 1.5rem;
            }

            .content-header,
            .content-body{
                padding: 1.5rem 1rem;
            }

            .dishes-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }


    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="content-header">
                <div class="header-top">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                <h1>Welcome, Bitehiver</h1>
                <p class="header-description">
                    Thank you for choosing Bitehiver. We’ve prepared a selection of our most popular dishes for you to explore. Whether you’re here for a quick bite or a memorable meal, we’re committed to providing a smooth and enjoyable experience every step of the way.
                </p>
            </header>

            <!-- Content Body -->
            <div class="content-body">
                <div class="dishes-grid">
                    <!-- Left Column -->
                    <section class="popular-section">
                        <div class="section-header">
                            <h2>Popular Dishes</h2>
                            <a href="#" class="see-all-link">See All</a>
                        </div>
                        <ul class="menu-list">
                            <?php
                            
                            include 'config_db.php';
                            
                            // Get filter parameters
                            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                            $stock_filter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';
                            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                            $items_per_page = 5;
                            $offset = ($page - 1) * $items_per_page;

                            // Build WHERE clause
                            $whereClause = [];
                            if (!empty($search)) {
                                $search_safe = mysqli_real_escape_string($connection, $search);
                                $whereClause[] = "(name_item LIKE '%$search_safe%' OR category_item LIKE '%$search_safe%')";
                            }
                            if ($stock_filter == 'In Stock') {
                                $whereClause[] = "stock > 0";
                            } elseif ($stock_filter == 'Out of Stock') {
                                $whereClause[] = "stock = 0";
                            }
                            $whereSql = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

                            // Count total items for pagination
                            $countSQL = "SELECT COUNT(*) as total FROM menu_item $whereSql";
                            $countResult = mysqli_query($connection, $countSQL);
                            $totalItems = mysqli_fetch_assoc($countResult)['total'];
                            $totalPages = ceil($totalItems / $items_per_page);

                            // Get menu items
                            $selectSQL = "SELECT * FROM menu_item $whereSql ORDER BY id_menu_item ASC LIMIT $items_per_page OFFSET $offset";
                            $hasilSelectQuery = mysqli_query($connection, $selectSQL);

                            if (!$hasilSelectQuery) {
                                echo "<li class='empty-state'><p>ERROR: Query gagal dijalankan! " . mysqli_error($connection) . "</p></li>";
                            } elseif (mysqli_num_rows($hasilSelectQuery) == 0) {
                                echo '<li class="empty-state">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p>No menu items found</p>
                                </li>';
                            } else {
                                while ($row = mysqli_fetch_assoc($hasilSelectQuery)) {
                                    $stockStatus = $row['stock'] > 0 ? 'In Stock' : 'Out of Stock';
                                    $stockClass = $row['stock'] > 0 ? '' : 'out-of-stock';
                            ?>
                                <li class="menu-card">
                                    <div class="menu-card-header">
                                        <h3 class="menu-title"><?php echo htmlspecialchars($row['name_item']); ?></h3>
                                        <span class="menu-status <?php echo $stockClass; ?>"><?php echo $stockStatus; ?></span>
                                    </div>
                                    <div class="menu-card-body">
                                        <span class="menu-items-count"><?php echo $row['stock']; ?> items</span>
                                        <span class="menu-price">$<?php echo number_format($row['price'], 2); ?></span>
                                    </div>
                                </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </section>

                    <!-- Right Column -->
                    <section class="popular-section">
                        <div class="section-header">
                            <h2>Popular Dishes</h2>
                            <a href="#" class="see-all-link">See All</a>
                        </div>
                        <ul class="menu-list">
                            <?php
                            // Reset query pointer to reuse data for right column
                            mysqli_data_seek($hasilSelectQuery, 0);
                            
                            if (mysqli_num_rows($hasilSelectQuery) == 0) {
                                echo '<li class="empty-state">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p>No menu items found</p>
                                </li>';
                            } else {
                                while ($row = mysqli_fetch_assoc($hasilSelectQuery)) {
                                    $stockStatus = $row['stock'] > 0 ? 'In Stock' : 'Out of Stock';
                                    $stockClass = $row['stock'] > 0 ? '' : 'out-of-stock';
                            ?>
                                <li class="menu-card">
                                    <div class="menu-card-header">
                                        <h3 class="menu-title"><?php echo htmlspecialchars($row['name_item']); ?></h3>
                                        <span class="menu-status <?php echo $stockClass; ?>"><?php echo $stockStatus; ?></span>
                                    </div>
                                    <div class="menu-card-body">
                                        <span class="menu-items-count"><?php echo $row['stock']; ?> items</span>
                                        <span class="menu-price">$<?php echo number_format($row['price'], 2); ?></span>
                                    </div>
                                </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </section>
                </div>
            </div>
        </main>
    </div>

</body>    
</html>