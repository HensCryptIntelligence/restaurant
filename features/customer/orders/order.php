<?php
ob_start();
include 'config_db.php';


// Check if user is logged in and is a customer
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'customer') {
    // header("Location: ../../../features/auth/views/login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $id_menu_item = (int)$_POST['id_menu_item'];
    $name_item = mysqli_real_escape_string($connection, $_POST['name_item']);
    $category_item = mysqli_real_escape_string($connection, $_POST['category_item']);
    $price = (float)$_POST['price'];
    $quantity = 1;
    $subtotal = $price * $quantity;

    // Check if item already exists in cart (in_cart only)
    $checkSQL = "SELECT * FROM cart_order 
                 WHERE id_user = $id_user 
                   AND id_menu_item = $id_menu_item
                   AND status='in_cart'";
    $checkResult = mysqli_query($connection, $checkSQL);

    if (mysqli_num_rows($checkResult) > 0) {
        // Update quantity and subtotal
        $updateSQL = "UPDATE cart_order 
                      SET quantity = quantity + 1, subtotal = price * (quantity + 1) 
                      WHERE id_user = $id_user 
                        AND id_menu_item = $id_menu_item
                        AND status='in_cart'";
        mysqli_query($connection, $updateSQL);
        $_SESSION['session_cart'] = "Item quantity updated in cart!";
        $_SESSION['session_type'] = 'success';
    } else {
        // Insert new item to cart
        $insertSQL = "INSERT INTO cart_order 
                      (id_user, id_menu_item, name_item, category_item, price, quantity, subtotal) 
                      VALUES 
                      ($id_user, $id_menu_item, '$name_item', '$category_item', $price, $quantity, $subtotal)";
        mysqli_query($connection, $insertSQL);
        $_SESSION['session_cart'] = "Item added to cart successfully!";
        $_SESSION['session_type'] = 'success';
    }
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?category=" . urlencode($_GET['category'] ?? 'All Items') . "&stock=" . urlencode($_GET['stock'] ?? 'All'));
    exit();
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'All Items';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : 'All';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12;
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
    die("ERROR: Query gagal dijalankan! " . mysqli_error($connection));
}

// Count cart items
$cartCountSQL = "SELECT SUM(quantity) as total_items FROM cart_order WHERE id_user = $id_user AND status='in_cart'";
$cartCountResult = mysqli_query($connection, $cartCountSQL);
$cartCount = mysqli_fetch_assoc($cartCountResult)['total_items'] ?? 0;

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Menu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f0f0f;
            color: #ffffff;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 10px 0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title {
            font-size: 22px;
            font-weight: 600;
            color: #ffffff;
        }

        .cart-btn {
            padding: 11px 22px;
            background: #1ae0b8;
            color: #0f0f0f;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .cart-btn:hover {
            background: #15c9a5;
            transform: translateY(-2px);
        }

        .cart-badge {
            background: #0f0f0f;
            color: #1ae0b8;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
        }

        /* Alert Messages */
        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        /* Filter Section */
        .filter-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .category-tabs {
            width: 100%;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 10px 20px;
            background: #1a1a1a;
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
            background: #2a2a2a;
        }

        .tab-btn.active {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .menu-card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(26, 224, 184, 0.1);
        }

        .card-category {
            font-size: 12px;
            color: #1ae0b8;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .card-name {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .card-price {
            font-size: 20px;
            font-weight: 700;
            color: #1ae0b8;
            margin-bottom: 16px;
        }

        .card-stock {
            font-size: 13px;
            color: #888;
            margin-bottom: 16px;
        }

        .card-stock.in-stock {
            color: #1ae0b8;
        }

        .card-stock.out-stock {
            color: #ff4444;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 12px;
            background: #1ae0b8;
            color: #0f0f0f;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s;
            margin-top: auto;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background: #15c9a5;
            transform: scale(1.02);
        }

        .add-to-cart-btn:disabled {
            background: #2a2a2a;
            color: #666;
            cursor: not-allowed;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            background: #1a1a1a;
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
            background: #2a2a2a;
        }

        .page-btn.active {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        .page-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header-bar {
                padding: 16px;
            }

            .header-title {
                font-size: 20px;
            }

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .category-tabs {
                justify-content: center;
            }

            .tab-btn {
                padding: 8px 14px;
                font-size: 13px;
            }

            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }

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
        <!-- Header Bar -->
        <div class="header-bar">
            <h1 class="header-title">All Item</h1>
            <a href="cart_page.php" class="cart-btn">
                Cart of Items
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['session_cart'])): ?>
            <div class="alert alert-<?php echo $_SESSION['session_type']; ?>">
                <?php 
                echo $_SESSION['session_cart'];
                unset($_SESSION['session_cart']);
                unset($_SESSION['session_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="category-tabs">
                <a href="?category=All Items&stock=<?php echo urlencode($stock_filter); ?>" 
                   class="tab-btn <?php echo $category_filter == 'All Items' ? 'active' : ''; ?>">All Items</a>
                <a href="?category=Appetizer&stock=<?php echo urlencode($stock_filter); ?>" 
                   class="tab-btn <?php echo $category_filter == 'Appetizer' ? 'active' : ''; ?>">Appetizer</a>
                <a href="?category=Main Course&stock=<?php echo urlencode($stock_filter); ?>" 
                   class="tab-btn <?php echo $category_filter == 'Main Course' ? 'active' : ''; ?>">Main Course</a>
                <a href="?category=<?php echo urlencode('Soup & Salad'); ?>&stock=<?php echo urlencode($stock_filter); ?>"
                   class="tab-btn <?php echo $category_filter == 'Soup & Salad' ? 'active' : ''; ?>">Soup & Salad</a>
                <a href="?category=Beverages&stock=<?php echo urlencode($stock_filter); ?>" 
                   class="tab-btn <?php echo $category_filter == 'Beverages' ? 'active' : ''; ?>">Beverages</a>
                <a href="?category=<?php echo urlencode('Grill & BBQ'); ?>&stock=<?php echo urlencode($stock_filter); ?>"
                   class="tab-btn <?php echo $category_filter == 'Grill & BBQ' ? 'active' : ''; ?>">Grill & BBQ</a>
                <a href="?category=Dessert&stock=<?php echo urlencode($stock_filter); ?>" 
                   class="tab-btn <?php echo $category_filter == 'Dessert' ? 'active' : ''; ?>">Dessert</a>
            </div>
        </div>

        <!-- Menu Grid -->
        <?php if (mysqli_num_rows($hasilSelectQuery) > 0): ?>
            <div class="menu-grid">
                <?php while ($item = mysqli_fetch_assoc($hasilSelectQuery)): ?>
                    <div class="menu-card">
                        <div class="card-category">Order → Kitchen</div>
                        <h3 class="card-name"><?php echo htmlspecialchars($item['name_item']); ?></h3>
                        <div class="card-price">$<?php echo number_format($item['price'], 2); ?></div>
                        <div class="card-stock <?php echo $item['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $item['stock'] > 0 ? '⏤ ' . $item['stock'] : '⏤ 0'; ?>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="id_menu_item" value="<?php echo $item['id_menu_item']; ?>">
                            <input type="hidden" name="name_item" value="<?php echo htmlspecialchars($item['name_item']); ?>">
                            <input type="hidden" name="category_item" value="<?php echo htmlspecialchars($item['category_item']); ?>">
                            <input type="hidden" name="price" value="<?php echo $item['price']; ?>">
                            <button type="submit" name="add_to_cart" class="add-to-cart-btn" 
                                    <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <?php echo $item['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No items found</h3>
                <p>Try adjusting your filters</p>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
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
</body>
</html>