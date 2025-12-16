<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $conn = getDBConnection();

    // Start transaction
    $conn->begin_transaction();

    // Calculate totals
    $subtotal = $data['subtotal'];
    $tax = $data['tax'];
    $delivery_fee = $data['delivery_fee'];
    $total = $data['total'];

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_name, 
            customer_email, 
            customer_phone, 
            total_amount, 
            status,
            delivery_date,
            delivery_time,
            payment_method,
            delivery_address,
            notes
        ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)
    ");

    $address = $data['customer']['address'] . ', ' . $data['customer']['city'];
    if (!empty($data['customer']['postal_code'])) {
        $address .= ', ' . $data['customer']['postal_code'];
    }

    $stmt->bind_param(
        "sssdsssss",
        $data['customer']['full_name'],
        $data['customer']['email'],
        $data['customer']['phone'],
        $total,
        $data['delivery']['date'],
        $data['delivery']['time'],
        $data['payment_method'],
        $address,
        $data['notes']
    );

    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    $stmt = $conn->prepare("
        INSERT INTO order_items (
            order_id, 
            product_id, 
            quantity, 
            size, 
            card_message, 
            price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['items'] as $item) {
        $stmt->bind_param(
            "iiissd",
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['size'],
            $item['message'],
            $item['price']
        );
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();

    // Send email notification to admin
    sendAdminNotification($order_id, $data);

    // Send confirmation email to customer
    sendCustomerConfirmation($data['customer']['email'], $order_id, $data);

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully!'
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Function to send admin notification
function sendAdminNotification($order_id, $data) {
    $admin_email = "admin@bloomheaven.com"; // Change to your admin email
    $subject = "New Order #$order_id - Bloom Heaven";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #FF7AA2, #FFB6D0); color: white; padding: 20px; text-align: center; }
            .content { background: #fff; padding: 20px; }
            .order-details { background: #f9f9f9; padding: 15px; margin: 15px 0; }
            .total { font-size: 24px; color: #FF7AA2; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌷 New Order Received!</h1>
            </div>
            <div class='content'>
                <h2>Order #$order_id</h2>
                <div class='order-details'>
                    <strong>Customer Information:</strong><br>
                    Name: {$data['customer']['full_name']}<br>
                    Email: {$data['customer']['email']}<br>
                    Phone: {$data['customer']['phone']}<br>
                    Address: {$data['customer']['address']}, {$data['customer']['city']}<br><br>
                    
                    <strong>Delivery Details:</strong><br>
                    Date: {$data['delivery']['date']}<br>
                    Time: {$data['delivery']['time']}<br><br>
                    
                    <strong>Payment Method:</strong> {$data['payment_method']}<br><br>
                    
                    <strong>Items:</strong><br>
                    ";

    foreach ($data['items'] as $item) {
        $message .= "• {$item['name']} - Size: {$item['size']} - Qty: {$item['quantity']} - $" . number_format($item['price'] * $item['quantity'], 2) . "<br>";
    }

    $message .= "
                    <br>
                    <div class='total'>Total: $" . number_format($data['total'], 2) . "</div>
                </div>
                
                <p><strong>Notes:</strong> {$data['notes']}</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Bloom Heaven <noreply@bloomheaven.com>" . "\r\n";

    mail($admin_email, $subject, $message, $headers);
}

// Function to send customer confirmation
function sendCustomerConfirmation($email, $order_id, $data) {
    $subject = "Order Confirmation #$order_id - Bloom Heaven";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #FF7AA2, #FFB6D0); color: white; padding: 20px; text-align: center; }
            .content { background: #fff; padding: 20px; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .order-details { background: #f9f9f9; padding: 15px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌷 Thank You for Your Order!</h1>
            </div>
            <div class='content'>
                <div class='success'>
                    <h2>✅ Order Confirmed!</h2>
                    <p>Your order #$order_id has been received and is being processed.</p>
                </div>
                
                <div class='order-details'>
                    <strong>Order Details:</strong><br>
                    Order Number: #$order_id<br>
                    Delivery Date: {$data['delivery']['date']}<br>
                    Delivery Time: {$data['delivery']['time']}<br>
                    Total Amount: $" . number_format($data['total'], 2) . "<br>
                </div>
                
                <p>We'll send you another email when your order is out for delivery.</p>
                <p>If you have any questions, contact us at admin@bloomheaven.com</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Bloom Heaven <noreply@bloomheaven.com>" . "\r\n";

    mail($email, $subject, $message, $headers);
}
?>