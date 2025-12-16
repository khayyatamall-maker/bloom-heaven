<?php
require_once 'config.php';

$conn = getDBConnection();

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a search term'
    ]);
    exit;
}

// Search in name, description, and category
$searchTerm = "%{$query}%";
$sql = "SELECT * FROM products 
        WHERE name LIKE ? 
        OR description LIKE ? 
        OR category LIKE ? 
        ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
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
    'products' => $products,
    'count' => count($products)
]);

$stmt->close();
$conn->close();
?>