<?php
require_once 'config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, 
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 2rem;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        .success-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .success-box h1 {
            color: #6BBF6B;
            margin-bottom: 1rem;
        }
        .order-id {
            background: #FFF5F9;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #FF7AA2;
            margin: 1.5rem 0;
        }
        .order-details {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: left;
            margin-bottom: 2rem;
        }
        .order-details h2 {
            color: #FF7AA2;
            margin-bottom: 1.5rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #FFE5EF;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #FFF5F9;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .order-item img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        .item-info {
            flex: 1;
        }
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
            color: white;
        }
        .btn-secondary {
            background: white;
            color: #FF7AA2;
            border: 2px solid #FF7AA2;
        }
    </style>
</head>
<body>
<!-- Header -->
<header>
    <div class="header-content">
        <a href="index.php" class="logo">🌷 Bloom Heaven</a>
    </div>
</header>

<div class="success-container">
    <div class="success-icon">✅</div>

    <div class="success-box">
        <h1>Order Confirmed!</h1>
        <p>Thank you for your order, <?php echo htmlspecialchars($order['customer_name']); ?>!</p>
        <p>We've sent a confirmation email to <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong></p>

        <div class="order-id">
            Order ID: #<?php echo $order['id']; ?>
        </div>

        <p style="color: #666;">
            Your beautiful flowers will be delivered on
            <strong><?php echo date('F j, Y', strtotime($order['delivery_date'])); ?></strong>
            during the <strong><?php echo ucfirst($order['delivery_time']); ?></strong>
        </p>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>

        <div class="detail-row">
            <span class="detail-label">Order Date:</span>
            <span><?php echo date('M d, Y g:i A', strtotime($order['order_date'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Method:</span>
            <span><?php echo ucfirst($order['payment_method']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Delivery Address:</span>
            <span><?php echo htmlspecialchars($order['delivery_address']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Contact Phone:</span>
            <span><?php echo htmlspecialchars($order['customer_phone']); ?></span>
        </div>

        <h3 style="margin-top: 2rem; margin-bottom: 1rem; color: #FF7AA2;">Items Ordered</h3>
        <?php while ($item = $items->fetch_assoc()): ?>
            <div class="order-item">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="item-info">
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div style="color: #666; font-size: 0.9rem;">
                        Size: <?php echo ucfirst($item['size']); ?> |
                        Quantity: <?php echo $item['quantity']; ?>
                    </div>
                    <?php if ($item['card_message']): ?>
                        <div style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                            💌 Card Message: "<?php echo htmlspecialchars($item['card_message']); ?>"
                        </div>
                    <?php endif; ?>
                    <div style="color: #FF7AA2; font-weight: 600; margin-top: 0.5rem;">
                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="detail-row" style="font-size: 1.3rem; font-weight: 700; color: #FF7AA2; border-top: 2px solid #FF7AA2; margin-top: 1rem; padding-top: 1rem;">
            <span>Total Amount:</span>
            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
    </div>

    <div class="buttons">
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        <a href="javascript:window.print()" class="btn btn-secondary">Print Receipt</a>
    </div>
</div>

<script>
    // Clear cart from localStorage
    localStorage.removeItem('cart');
</script>
</body>
</html>