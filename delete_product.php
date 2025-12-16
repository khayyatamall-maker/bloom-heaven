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

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $conn = getDBConnection();

    // Get product image path before deleting
    $result = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($product = $result->fetch_assoc()) {
        $imagePath = '../' . $product['image'];

        // Delete product from database
        if ($conn->query("DELETE FROM products WHERE id = $id")) {
            // Delete image file if exists
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            header('Location: products.php?deleted=success');
        } else {
            header('Location: products.php?error=delete_failed');
        }
    } else {
        header('Location: products.php?error=not_found');
    }

    $conn->close();
} else {
    header('Location: products.php');
}
exit;
?>