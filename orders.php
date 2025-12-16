<?php
require_once '../auth.php';
requireLogin();

if (!isCustomer()) {
    header('Location: ../admin/index.php');
    exit;
}

require_once '../config.php';
$user = getCurrentUser();
$conn = getDBConnection();

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$orders = $stmt->get_result();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #FFF5F9;
        }
        .header {
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            color: white;
            padding: 2rem;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            transition: opacity 0.3s;
        }
        .nav-links a:hover {
            opacity: 0.8;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .page-title {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .page-title h1 {
            color: #FF7AA2;
        }
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #FFE5EF;
        }
        .order-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: #3A3A3A;
        }
        .order-date {
            color: #666;
            font-size: 0.875rem;
        }
        .status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .status.processing {
            background: #cce5ff;
            color: #004085;
        }
        .status.completed {
            background: #d4edda;
            color: #155724;
        }
        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .order-items {
            margin-bottom: 1rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #FFE5EF;
        }
        .item-name {
            color: #3A3A3A;
            font-weight: 500;
        }
        .item-details {
            color: #666;
            font-size: 0.875rem;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #FFE5EF;
        }
        .total-label {
            font-weight: 600;
            color: #3A3A3A;
        }
        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FF7AA2;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .empty-state h2 {
            color: #3A3A3A;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 122, 162, 0.4);
        }
    </style>
</head>
<body>
<div class="header">
    <div class="header-content">
        <div class="logo">🌷 Bloom Heaven</div>
        <div class="nav-links">
            <a href="../index.php">Shop</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">My Orders</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="page-title">
        <h1>My Orders 📦</h1>
        <p>Track and view all your orders</p>
    </div>

    <?php if ($orders && $orders->num_rows > 0): ?>
        <div class="orders-list">
            <?php while ($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                        </div>
                        <span class="status <?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                    </div>

                    <div class="order-items">
                        <h3 style="margin-bottom: 1rem; color: #3A3A3A;">Order Items:</h3>
                        <?php
                        $items = json_decode($order['order_items'], true);
                        if ($items):
                            foreach ($items as $item):
                                ?>
                                <div class="order-item">
                                    <div>
                                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="item-details">Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                    <div class="item-name">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <div class="order-total">
                        <span class="total-label">Total Amount:</span>
                        <span class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #FFE5EF;">
                        <strong>Delivery Address:</strong><br>
                        <?php echo htmlspecialchars($order['customer_address']); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No orders yet</h2>
            <p>Start shopping to see your orders here!</p>
            <a href="../index.php" class="btn" style="margin-top: 1.5rem;">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>