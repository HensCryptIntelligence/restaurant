<?php
session_start();
include 'config_db.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'customer') {
    header("Location: ../../../features/auth/views/login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Get only in_cart items
$cartSQL = "SELECT * FROM cart_order WHERE id_user = $id_user AND status='in_cart'";
$cartResult = mysqli_query($connection, $cartSQL);

if (!$cartResult) {
    die("ERROR: Query gagal dijalankan! " . mysqli_error($connection));
}

$cartItems = [];
$totalAmount = 0;
while ($row = mysqli_fetch_assoc($cartResult)) {
    $cartItems[] = $row;
    $totalAmount += $row['subtotal'];
}

// Redirect if no items in cart
if (count($cartItems) === 0) {
    header("Location: cart_page.php");
    exit();
}

// Get user info
$userSQL = "SELECT fullname FROM users WHERE id_user = $id_user";
$userResult = mysqli_query($connection, $userSQL);
$userData = mysqli_fetch_assoc($userResult);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $received = (float)$_POST['received'];
    $payment_method = mysqli_real_escape_string($connection, $_POST['payment_method']);

    if ($received < $totalAmount) {
        $_SESSION['session_payment'] = "Received amount must be at least IDR " . number_format($totalAmount, 2);
        $_SESSION['session_type'] = 'error';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $return_amount = $received - $totalAmount;

    mysqli_begin_transaction($connection);

    try {
        foreach ($cartItems as $item) {
            $id_cart_order = $item['id_cart_order'];
            $id_menu_item  = $item['id_menu_item'];
            $quantity      = $item['quantity'];

            // Insert payment record
            $paymentSQL = "INSERT INTO payment_order 
                (id_user, id_cart_order, total_amount, received, return_amount, payment_method, status)
                VALUES
                ($id_user, $id_cart_order, {$item['subtotal']}, $received, $return_amount, '$payment_method', 'confirmed')";
            mysqli_query($connection, $paymentSQL);
            $id_payment_order = mysqli_insert_id($connection);

            // Insert transaction record
            $transactionSQL = "INSERT INTO transaction_order
                (id_payment_order, id_user, status)
                VALUES ($id_payment_order, $id_user, 'confirmed')";
            mysqli_query($connection, $transactionSQL);

            // Update stock
            $stockSQL = "UPDATE menu_item SET stock = stock - $quantity WHERE id_menu_item = $id_menu_item";
            mysqli_query($connection, $stockSQL);

            // Mark cart as checked out
            $updateCartSQL = "UPDATE cart_order SET status='checked_out' WHERE id_cart_order = $id_cart_order";
            mysqli_query($connection, $updateCartSQL);
        }

        mysqli_commit($connection);

        $_SESSION['payment_details'] = [
            'total'    => $totalAmount,
            'received' => $received,
            'return'   => $return_amount,
            'method'   => $payment_method
        ];

        $_SESSION['session_payment'] = "Payment successful! Thank you for your order.";
        $_SESSION['session_type'] = 'success';

        $alertMessage = $_SESSION['session_payment'] ?? null;
        $alertType    = $_SESSION['session_type'] ?? null;

        // unset session supaya tidak muncul lagi
        if ($alertMessage) {
            unset($_SESSION['session_payment']);
            unset($_SESSION['session_type']);
        }

        header("Location: payment_success.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($connection);
        $_SESSION['session_payment'] = "Payment failed: " . $e->getMessage();
        $_SESSION['session_type'] = 'error';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
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
            max-width: 800px;
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

        .back-btn {
            padding: 11px 22px;
            background: #2a2a2a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #3a3a3a;
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

        .alert-error {
            background: #ff4444;
            color: #ffffff;
        }

        /* Payment Card */
        .payment-card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .card-header {
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-subtitle {
            color: #888;
            font-size: 14px;
        }

        /* Order Summary */
        .order-summary {
            background: #0f0f0f;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-size: 14px;
            color: #ffffff;
        }

        .item-qty {
            color: #888;
            font-size: 13px;
            margin-left: 8px;
        }

        .item-price {
            font-size: 14px;
            font-weight: 600;
            color: #1ae0b8;
        }

        .summary-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label {
            font-size: 16px;
            font-weight: 700;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #1ae0b8;
        }

        /* Payment Form */
        .payment-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #ffffff;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #1ae0b8;
        }

        .form-select {
            width: 100%;
            padding: 14px 16px;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #ffffff;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-select:focus {
            outline: none;
            border-color: #1ae0b8;
        }

        .form-hint {
            font-size: 13px;
            color: #888;
            margin-top: 4px;
        }

        /* Return Amount Display */
        .return-display {
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .return-label {
            font-size: 14px;
            color: #888;
        }

        .return-value {
            font-size: 20px;
            font-weight: 700;
            color: #1ae0b8;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .cancel-btn, .submit-btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
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

        .submit-btn {
            background: #1ae0b8;
            color: #0f0f0f;
        }

        .submit-btn:hover {
            background: #15c9a5;
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background: #2a2a2a;
            color: #666;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .payment-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .total-amount {
                font-size: 20px;
            }
        }
    </style>
    <script>
        function calculateReturn() {
            const total = <?php echo $totalAmount; ?>;
            const received = parseFloat(document.getElementById('received').value) || 0;
            const returnAmount = received - total;
            
            document.getElementById('return-amount').textContent = 'IDR ' + returnAmount.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            // Enable/disable submit button
            const submitBtn = document.getElementById('submit-btn');
            if (received >= total) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Header Bar -->
        <div class="header-bar">
            <h1 class="header-title">Payment</h1>
            <a href="cart_page.php" class="back-btn">Back to Cart</a>
        </div>

        <!-- Payment Card -->
        <div class="payment-card">
            <div class="card-header">
                <h2 class="card-title">Complete Your Payment</h2>
                <p class="card-subtitle">Customer: <?php echo htmlspecialchars($userData['fullname']); ?></p>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-title">Order Summary</div>
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-item">
                        <div>
                            <span class="item-name"><?php echo htmlspecialchars($item['name_item']); ?></span>
                            <span class="item-qty">× <?php echo $item['quantity']; ?></span>
                        </div>
                        <span class="item-price">IDR <?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-total">
                    <span class="total-label">Total Amount</span>
                    <span class="total-amount">IDR <?php echo number_format($totalAmount, 2); ?></span>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" action="" class="payment-form">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="">Select payment method</option>
                        <option value="cash">Cash</option>
                        <option value="e-wallet">E-Wallet</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="received">Amount Received</label>
                    <input 
                        type="number" 
                        name="received" 
                        id="received" 
                        class="form-input" 
                        step="0.01" 
                        min="<?php echo $totalAmount; ?>"
                        placeholder="Enter amount received"
                        oninput="calculateReturn()"
                        required>
                    <span class="form-hint">Minimum: IDR <?php echo number_format($totalAmount, 2); ?></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Return Amount</label>
                    <div class="return-display">
                        <span class="return-label">Change to return:</span>
                        <span class="return-value" id="return-amount">IDR 0.00</span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="cart_page.php" class="cancel-btn">Cancel</a>
                    <button type="submit" name="process_payment" id="submit-btn" class="submit-btn" disabled>
                        Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>




