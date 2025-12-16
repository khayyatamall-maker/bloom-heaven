<?php
require_once 'config.php';

try {
    $conn = getDBConnection();

    $category = isset($_GET['category']) ? $_GET['category'] : 'all';

    if ($category === 'all') {
        $sql = "SELECT * FROM products ORDER BY id ASC";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT * FROM products WHERE category = ? ORDER BY id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price'],
                'image' => $row['image'],
                'category' => $row['category'],
                'description' => $row['description'],
                'rating' => (int)$row['rating']
        ];
    }

    echo json_encode([
            'success' => true,
            'products' => $products
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>