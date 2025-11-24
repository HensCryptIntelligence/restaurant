<?php
session_start();
include 'config_db.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../auth/views/login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Update quantity (only in_cart)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $id_cart_order = (int)$_POST['id_cart_order'];
    $new_quantity = (int)$_POST['quantity'];
    
    if ($new_quantity > 0) {
        $updateSQL = "UPDATE cart_order 
                      SET quantity = $new_quantity, subtotal = price * $new_quantity 
                      WHERE id_cart_order = $id_cart_order 
                        AND id_user = $id_user
                        AND status='in_cart'";
        mysqli_query($connection, $updateSQL);
        $_SESSION['session_cart'] = "Quantity updated successfully!";
        $_SESSION['session_type'] = 'success';
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete item (only in_cart)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $id_cart_order = (int)$_POST['id_cart_order'];
    
    $deleteSQL = "DELETE FROM cart_order 
                  WHERE id_cart_order = $id_cart_order 
                    AND id_user = $id_user
                    AND status='in_cart'";
    mysqli_query($connection, $deleteSQL);
    $_SESSION['session_cart'] = "Item removed from cart!";
    $_SESSION['session_type'] = 'success';
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get cart items (only in_cart)
$selectSQL = "SELECT * FROM cart_order 
              WHERE id_user = $id_user 
                AND status='in_cart'
              ORDER BY id_cart_order ASC";
$hasilSelectQuery = mysqli_query($connection, $selectSQL);

if (!$hasilSelectQuery) {
    die("ERROR: Query gagal dijalankan! " . mysqli_error($connection));
}

// Calculate total
$totalAmount = 0;
$cartItems = [];
while ($row = mysqli_fetch_assoc($hasilSelectQuery)) {
    $cartItems[] = $row;
    $totalAmount += $row['subtotal'];
}

// Get user info
$userSQL = "SELECT fullname FROM users WHERE id_user = $id_user";
$userResult = mysqli_query($connection, $userSQL);
$userData = mysqli_fetch_assoc($userResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Order</title>
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
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 12px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .order-number {
            background: #1ae0b8;
            color: #0f0f0f;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 700;
        }

        .customer-info h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .customer-info p {
            font-size: 14px;
            color: #888;
        }

        .add-item-btn {
            padding: 12px 24px;
            background: #1ae0b8;
            color: #0f0f0f;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .add-item-btn:hover {
            background: #15c9a5;
            transform: translateY(-2px);
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

        /* Table Section */
        .table-container {
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table thead {
            background: #0f0f0f;
        }

        .order-table th {
            padding: 16px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .order-table td {
            padding: 20px 16px;
            border-top: 1px solid #2a2a2a;
            font-size: 15px;
        }

        .order-table tbody tr:hover {
            background: #222;
        }

        .order-id {
            color: #888;
            font-size: 14px;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .product-category {
            color: #888;
            font-size: 13px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-input {
            width: 60px;
            padding: 6px 10px;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            color: #ffffff;
            text-align: center;
            font-size: 14px;
        }

        .qty-btn {
            padding: 6px 10px;
            background: #2a2a2a;
            border: none;
            border-radius: 6px;
            color: #ffffff;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #3a3a3a;
        }

        .price-cell {
            color: #1ae0b8;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .edit-btn {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        .edit-btn:hover {
            background: #15c9a5;
        }

        .delete-btn {
            background: #ff4444;
            color: #ffffff;
        }

        .delete-btn:hover {
            background: #cc0000;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .summary-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .summary-icon {
            background: #1ae0b8;
            padding: 15px;
            border-radius: 12px;
            font-size: 24px;
        }

        .summary-details {
            display: flex;
            gap: 40px;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
        }

        .summary-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
        }

        .total-value {
            color: #1ae0b8;
        }

        .summary-actions {
            display: flex;
            gap: 12px;
        }

        .cancel-btn, .checkout-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn {
            background: transparent;
            color: #ffffff;
            border: 2px solid #2a2a2a;
        }

        .cancel-btn:hover {
            border-color: #3a3a3a;
            background: #1a1a1a;
        }

        .checkout-btn {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        .checkout-btn:hover {
            background: #15c9a5;
            transform: translateY(-2px);
        }

        .checkout-btn:disabled {
            background: #2a2a2a;
            color: #666;
            cursor: not-allowed;
            transform: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: #1a1a1a;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .order-table {
                font-size: 14px;
            }

            .order-table th,
            .order-table td {
                padding: 12px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info {
                width: 100%;
            }

            .add-item-btn {
                width: 100%;
                text-align: center;
            }

            /* Mobile Card Layout */
            .table-container {
                overflow-x: visible;
            }

            .order-table thead {
                display: none;
            }

            .order-table,
            .order-table tbody,
            .order-table tr,
            .order-table td {
                display: block;
                width: 100%;
            }

            .order-table tr {
                margin-bottom: 15px;
                background: #1a1a1a;
                border-radius: 8px;
                padding: 15px;
                border: 1px solid #2a2a2a;
            }

            .order-table td {
                padding: 8px 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .order-table td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #888;
                font-size: 12px;
                text-transform: uppercase;
            }

            .action-buttons {
                width: 100%;
                justify-content: flex-end;
            }

            .summary-section {
                flex-direction: column;
                align-items: stretch;
            }

            .summary-left {
                flex-direction: column;
                width: 100%;
            }

            .summary-details {
                width: 100%;
                justify-content: space-between;
            }

            .summary-actions {
                width: 100%;
                flex-direction: column;
            }

            .cancel-btn, .checkout-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="order-info">
                <div class="order-number">02</div>
                <div class="customer-info">
                    <h2><?php echo htmlspecialchars($userData['fullname']); ?></h2>
                    <p>Order # 002</p>
                </div>
            </div>
            <a href="index.php" class="add-item-btn">Add Item</a>
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

        <!-- Table Section -->
        <?php if (count($cartItems) > 0): ?>
            <div class="table-container">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Name of product</th>
                            <th>Quantity</th>
                            <th>Prices</th>
                            <th>Sub Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td data-label="Orders">
                                    <div class="order-id">#<?php echo str_pad($item['id_menu_item'], 3, '0', STR_PAD_LEFT); ?></div>
                                </td>
                                <td data-label="Product">
                                    <div class="product-name"><?php echo htmlspecialchars($item['name_item']); ?></div>
                                    <div class="product-category"><?php echo htmlspecialchars($item['category_item']); ?></div>
                                </td>
                                <td data-label="Quantity">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="id_cart_order" value="<?php echo $item['id_cart_order']; ?>">
                                        <div class="quantity-control">
                                            <input type="number" name="quantity" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1">
                                            <button type="submit" name="update_quantity" class="qty-btn">✓</button>
                                        </div>
                                    </form>
                                </td>
                                <td data-label="Price">
                                    <span class="price-cell">IDR <?php echo number_format($item['price'], 2); ?></span>
                                </td>
                                <td data-label="Subtotal">
                                    <span class="price-cell">IDR <?php echo number_format($item['subtotal'], 2); ?></span>
                                </td>
                                <td data-label="Action">
                                    <div class="action-buttons">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="id_cart_order" value="<?php echo $item['id_cart_order']; ?>">
                                            <button type="submit" name="delete_item" class="delete-btn" onclick="return confirm('Remove this item from cart?')"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="summary-section">
                <div class="summary-left">
                    <div class="summary-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart-icon lucide-shopping-cart"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></div>
                    <div class="summary-details">
                        <div class="summary-item">
                            <span class="summary-label">Total Prices</span>
                            <span class="summary-value total-value">IDR <?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Disc. 0%</span>
                            <span class="summary-value">IDR 0</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Tax 0%</span>
                            <span class="summary-value">IDR 0</span>
                        </div>
                    </div>
                </div>
                <div class="summary-actions">
                    <a href="index.php" class="cancel-btn">Cancel</a>
                    <a href="payment_page.php" class="checkout-btn">Checkout</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Your cart is empty</h3>
                <p>Add some items to get started</p>
                <a href="index.php" class="add-item-btn">Browse Menu</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>