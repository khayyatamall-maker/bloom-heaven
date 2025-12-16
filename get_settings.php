<?php
require_once 'config.php';

try {
    $conn = getDBConnection();

    $sql = "SELECT setting_key, setting_value FROM settings";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>