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
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$orders = $stmt->get_result();

// Get statistics
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = " . $user['id'])->fetch_assoc()['count'];
$totalSpent = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE user_id = " . $user['id'])->fetch_assoc()['total'] ?? 0;

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Bloom Heaven</title>
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
        .welcome {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .welcome h1 {
            color: #FF7AA2;
            margin-bottom: 0.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FF7AA2;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
        }
        .orders-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .orders-section h2 {
            color: #3A3A3A;
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #FFE5EF;
        }
        th {
            background: #FFF5F9;
            font-weight: 600;
            color: #3A3A3A;
        }
        .btn {
            padding: 0.5rem 1rem;
            background: #FF7AA2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #FF5C8D;
            transform: translateY(-2px);
        }
        .status {
            padding: 0.25rem 0.75rem;
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
    <div class="welcome">
        <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 🌸</h1>
        <p>Here's a summary of your account</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $totalOrders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$<?php echo number_format($totalSpent, 2); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <div class="orders-section">
        <h2>Recent Orders</h2>
        <?php if ($orders && $orders->num_rows > 0): ?>
            <table>
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                        <td><a href="orders.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div style="margin-top: 1rem;">
                <a href="orders.php" class="btn">View All Orders</a>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #999; padding: 2rem;">No orders yet. <a href="../index.php" class="btn">Start Shopping</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>