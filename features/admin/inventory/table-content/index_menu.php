<?php

include 'config_db.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'All Items';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : 'All';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build SQL query with filters
$whereClause = [];
if ($category_filter != 'All Items') {
    $whereClause[] = "category_item = '" . mysqli_real_escape_string($connection, $category_filter) . "'";
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
    echo "ERROR: Query gagal dijalankan!" . mysqli_error($connection);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #1a1a1a;
            color: #ffffff;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background: #2a2a2a;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .category-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 10px 20px;
            background: transparent;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .tab-btn:hover {
            background: #3a3a3a;
        }

        .tab-btn.active {
            background: #ffc0cb;
            color: #1a1a1a;
        }

        .controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .dropdown {
            position: relative;
        }

        .dropdown select {
            padding: 10px 40px 10px 16px;
            background: #3a3a3a;
            color: #ffffff;
            border: 1px solid #4a4a4a;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            appearance: none;
            min-width: 150px;
        }

        .dropdown::after {
            content: '▼';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #ffffff;
            font-size: 10px;
        }

        .btn-add {
            padding: 10px 24px;
            background: #ffc0cb;
            color: #1a1a1a;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: #ffb0bb;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #22c55e;
            color: white;
        }

        .alert-error {
            background: #ef4444;
            color: white;
        }

        /* Table Styles */
        .table-wrapper {
            max-height: 500px;
            min-height: 400px;
            overflow: auto;
            border-radius: 8px;
            border: 1px solid #3a3a3a;
            background: #2a2a2a;
            margin-top: 20px;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 5px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #4a4a4a;
            border-radius: 5px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #5a5a5a;
        }

        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 14px;
        }

        .col-id { width: 10%; min-width: 80px; }
        .col-name { width: 25%; min-width: 180px; }
        .col-stock { width: 15%; min-width: 100px; }
        .col-category { width: 20%; min-width: 120px; }
        .col-price { width: 15%; min-width: 100px; }
        .col-action { width: 15%; min-width: 120px; }

        .table-wrapper th {
            background: #3a3a3a;
            color: #ffffff;
            padding: 14px 16px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 5;
            text-align: left;
            border-bottom: 1px solid #4a4a4a;
        }

        .table-wrapper td {
            padding: 16px;
            border-bottom: 1px solid #3a3a3a;
            color: #e0e0e0;
            vertical-align: middle;
        }

        .table-wrapper tr:hover {
            background: #333333;
            transition: background 0.2s;
        }

        .table-wrapper tr:last-child td {
            border-bottom: none;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-edit {
            background: transparent;
        }

        .btn-edit:hover {
            background: #3a3a3a;
        }

        .btn-delete {
            background: transparent;
        }

        .btn-delete:hover {
            background: #3a3a3a;
        }

        .btn-icon svg {
            width: 18px;
            height: 18px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888888;
            font-style: italic;
            font-size: 15px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            background: #3a3a3a;
            color: #ffffff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .page-btn:hover:not(.active):not(:disabled) {
            background: #4a4a4a;
        }

        .page-btn.active {
            background: #ffc0cb;
            color: #1a1a1a;
        }

        .page-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .card {
                padding: 16px;
            }

            .header-section {
                flex-direction: column;
                align-items: stretch;
            }

            .category-tabs {
                order: 2;
                justify-content: center;
            }

            .controls {
                order: 1;
                justify-content: space-between;
            }

            .tab-btn {
                padding: 8px 14px;
                font-size: 13px;
            }

            .table-wrapper {
                max-height: 400px;
                min-height: 300px;
            }

            .table-wrapper th,
            .table-wrapper td {
                padding: 12px 10px;
                font-size: 13px;
            }

            .col-id { width: 12%; min-width: 70px; }
            .col-name { width: 30%; min-width: 150px; }
            .col-stock { width: 15%; min-width: 90px; }
            .col-category { width: 20%; min-width: 100px; }
            .col-price { width: 18%; min-width: 90px; }
            .col-action { width: 15%; min-width: 100px; }
        }

        @media (max-width: 480px) {
            .pagination {
                gap: 4px;
            }

            .page-btn {
                width: 36px;
                height: 36px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php
            if (isset($_SESSION['session_barang'])) {
                $type = isset($_SESSION['session_type']) ? $_SESSION['session_type'] : 'success';
                echo '<div class="alert alert-' . $type . '">' . $_SESSION['session_barang'] . '</div>';
                unset($_SESSION['session_barang']);
                unset($_SESSION['session_type']);
            }
            ?>

            <div class="header-section">
                <div class="category-tabs">
                    <a href="?category=All Items&stock=<?php echo urlencode($stock_filter); ?>" 
                       class="tab-btn <?php echo $category_filter == 'All Items' ? 'active' : ''; ?>">All Items</a>
                    <a href="?category=Appetizer&stock=<?php echo urlencode($stock_filter); ?>" 
                       class="tab-btn <?php echo $category_filter == 'Appetizer' ? 'active' : ''; ?>">Appetizer</a>
                    <a href="?category=Main Course&stock=<?php echo urlencode($stock_filter); ?>" 
                       class="tab-btn <?php echo $category_filter == 'Main Course' ? 'active' : ''; ?>">Main Menu</a>
                    <a href="?category=<?php echo urlencode('Soup & Salad'); ?>&stock=<?php echo urlencode($stock_filter); ?>"
                    class="tab-btn <?php echo $category_filter == 'Soup & Salad' ? 'active' : ''; ?>">Soup & Salad</a>
                    <a href="?category=Beverages&stock=<?php echo urlencode($stock_filter); ?>" 
                       class="tab-btn <?php echo $category_filter == 'Beverages' ? 'active' : ''; ?>">Beverages</a>
                    <a href="?category=<?php echo urlencode('Grill & BBQ'); ?>&stock=<?php echo urlencode($stock_filter); ?>"
                    class="tab-btn <?php echo $category_filter == 'Grill & BBQ' ? 'active' : ''; ?>">Grill & BBQ</a>
                    <a href="?category=Dessert&stock=<?php echo urlencode($stock_filter); ?>" 
                       class="tab-btn <?php echo $category_filter == 'Dessert' ? 'active' : ''; ?>">Dessert</a>
                </div>

                <div class="controls">
                    <div class="dropdown">
                        <select onchange="window.location.href='?category=<?php echo urlencode($category_filter); ?>&stock=' + this.value">
                            <option value="All" <?php echo $stock_filter == 'All' ? 'selected' : ''; ?>>All Stock</option>
                            <option value="In Stock" <?php echo $stock_filter == 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="Out of Stock" <?php echo $stock_filter == 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <a href="table-content/add_menu.php" class="btn-add">Add Item</a>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <colgroup>
                        <col class="col-id">
                        <col class="col-name">
                        <col class="col-stock">
                        <col class="col-category">
                        <col class="col-price">
                        <col class="col-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>ID Item</th>
                            <th>Name Item</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $adaData = $hasilSelectQuery->num_rows > 0;
                        if ($adaData) {
                            while ($dataMenu = $hasilSelectQuery->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>#' . htmlspecialchars($dataMenu['id_menu_item']) . '</td>';
                                echo '<td>' . htmlspecialchars($dataMenu['name_item']) . '</td>';
                                echo '<td>' . htmlspecialchars($dataMenu['stock']) . ' items</td>';
                                echo '<td>' . htmlspecialchars($dataMenu['category_item']) . '</td>';
                                echo '<td>$' . number_format(htmlspecialchars($dataMenu['price']), 2) . '</td>';
                                echo '<td>';
                                echo '<div class="action-buttons">';
                                echo '<a href="table-content/edit_menu.php?id=' . urlencode($dataMenu['id_menu_item']) . '" class="btn-icon btn-edit" title="Edit">';
                                echo '<svg viewBox="0 0 24 24" fill="none" stroke="#a0a0a0" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
                                echo '</a>';
                                echo '<a href="table-content/delete_menu.php?id=' . urlencode($dataMenu['id_menu_item']) . '" class="btn-icon btn-delete" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\')">';
                                echo '<svg viewBox="0 0 24 24" fill="none" stroke="#a0a0a0" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                                echo '</a>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo "<tr><td colspan='6' class='empty-state'>No menu items found. Please add new items!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <a href="?category=<?php echo urlencode($category_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&page=<?php echo max(1, $page - 1); ?>" 
                   class="page-btn" <?php echo $page <= 1 ? 'style="pointer-events:none;opacity:0.3;"' : ''; ?>>‹</a>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?category=<?php echo urlencode($category_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&page=<?php echo $i; ?>" 
                       class="page-btn <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <a href="?category=<?php echo urlencode($category_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&page=<?php echo min($totalPages, $page + 1); ?>" 
                   class="page-btn" <?php echo $page >= $totalPages ? 'style="pointer-events:none;opacity:0.3;"' : ''; ?>>›</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>