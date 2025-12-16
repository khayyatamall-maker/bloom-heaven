<?php
session_start();

// Check if logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<?php
require_once '../auth.php';
requireAdmin();

require_once '../config.php';
$conn = getDBConnection();

// Get all customers with their order statistics
$customers = $conn->query("
    SELECT 
        u.*,
        COUNT(o.id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.user_type = 'customer'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin</title>
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
        .admin-header {
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .admin-header h1 {
            margin-bottom: 0.5rem;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .admin-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .admin-nav a {
            padding: 0.75rem 1.5rem;
            background: white;
            color: #3A3A3A;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .admin-nav a:hover {
            background: #FF7AA2;
            color: white;
            transform: translateY(-2px);
        }
        .customers-table {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow-x: auto;
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
        .customer-info {
            font-size: 0.875rem;
            color: #666;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge.vip {
            background: #FFD700;
            color: #856404;
        }
        .badge.regular {
            background: #E0E0E0;
            color: #666;
        }
    </style>
</head>
<body>
<div class="admin-header">
    <div class="container">
        <h1>👥 Customer Management</h1>
        <p>View all registered customers</p>
    </div>
</div>

<div class="container">
    <div class="admin-nav">
        <a href="index.php">📊 Dashboard</a>
        <a href="products.php">🌸 Manage Products</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="orders.php">📦 View Orders</a>
        <a href="customers.php">👥 Customers</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="customers-table">
        <h2 style="margin-bottom: 1.5rem;">All Customers</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Total Orders</th>
                <th>Total Spent</th>
                <th>Member Since</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($customers && $customers->num_rows > 0): ?>
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($customer['full_name']); ?></strong></td>
                        <td class="customer-info">
                            📧 <?php echo htmlspecialchars($customer['email']); ?><br>
                            📱 <?php echo htmlspecialchars($customer['phone']); ?>
                        </td>
                        <td class="customer-info"><?php echo htmlspecialchars($customer['address']); ?></td>
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td><strong>$<?php echo number_format($customer['total_spent'], 2); ?></strong></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <?php if ($customer['total_spent'] > 100): ?>
                                <span class="badge vip">VIP</span>
                            <?php else: ?>
                                <span class="badge regular">Regular</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 2rem;">No customers yet</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>