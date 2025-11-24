<?php
session_start();

// Redirect jika tidak ada payment details
if (!isset($_SESSION['payment_details'])) {
    header("Location: payment_page.php");
    exit();
}

// Ambil session payment details
$paymentDetails = $_SESSION['payment_details'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f0f0f;
            color: #ffffff;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { max-width: 600px; width: 100%; }
        .success-card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 50px 40px;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #1ae0b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
        }
        .success-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }
        .success-message {
            font-size: 16px;
            color: #888;
            margin-bottom: 40px;
        }
        .payment-details {
            background: #0f0f0f;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #1a1a1a;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-size: 14px; color: #888; }
        .detail-value { font-size: 15px; font-weight: 600; color: #ffffff; }
        .detail-value.highlight { color: #1ae0b8; font-size: 18px; }
        .action-buttons { display: flex; gap: 12px; }
        .btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-align: center;
        }
        .btn-secondary {
            background: transparent;
            color: #ffffff;
            border: 2px solid #2a2a2a;
        }
        .btn-secondary:hover {
            border-color: #3a3a3a;
            background: #1a1a1a;
        }
        .btn-primary {
            background: #1ae0b8;
            color: #0f0f0f;
        }
        .btn-primary:hover {
            background: #15c9a5;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            body { padding: 12px; }
            .success-card { padding: 40px 24px; }
            .success-title { font-size: 24px; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-message">Your order has been confirmed and payment received</p>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value">IDR <?php echo number_format($paymentDetails['total'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Received</span>
                    <span class="detail-value">IDR <?php echo number_format($paymentDetails['received'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Return Amount</span>
                    <span class="detail-value highlight">IDR <?php echo number_format($paymentDetails['return'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value"><?php echo ucfirst(str_replace('-', ' ', htmlspecialchars($paymentDetails['method']))); ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">New Order</a>
                <a href="cart_page.php" class="btn btn-secondary">Back to Cart</a>
            </div>
        </div>
    </div>
</body>
</html>
