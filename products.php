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

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: products.php?deleted=1');
    exit;
}

$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .product-item {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn-edit, .btn-delete {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        .btn-edit {
            background: #6BBF6B;
            color: white;
        }
        .btn-delete {
            background: #ff4444;
            color: white;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <h1>Manage Products</h1>
    <a href="add_product.php" class="btn btn-primary" style="display: inline-block; margin: 1rem 0;">+ Add New Product</a>

    <div class="products-grid">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="product-item">
                <img src="../<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>$<?php echo number_format($product['price'], 2); ?></p>
                <div class="product-actions">
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit">Edit</a>
                    <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>