<?php
session_start();

// Check if logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config.php';
$conn = getDBConnection();

$orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الطلبات - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }
        body {
            background: #FFF5F9;
            direction: rtl;
        }
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .header {
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: white;
            color: #FF7AA2;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid #FFE5EF;
        }
        th {
            background: #FFF5F9;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📦 جميع الطلبات</h1>
        <a href="dashboard.php" class="btn">← لوحة التحكم</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>اسم العميل</th>
            <th>البريد الإلكتروني</th>
            <th>رقم الهاتف</th>
            <th>المبلغ</th>
            <th>تاريخ الطلب</th>
            <th>الحالة</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem; color: #999;">لا توجد طلبات بعد</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
```

---

## ✅ **الآن جرب:**

1. **سجل دخول كمدير:**
```
http://localhost:8000/admin/login.php