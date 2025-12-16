<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    // Save current cart to session before redirecting
    $_SESSION['checkout_redirect'] = true;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Required - Bloom Heaven</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .login-required {
                background: white;
                padding: 3rem;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 500px;
                text-align: center;
            }
            .icon {
                font-size: 4rem;
                margin-bottom: 1rem;
            }
            h2 {
                color: #3A3A3A;
                margin-bottom: 1rem;
            }
            p {
                color: #666;
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
            }
            .btn {
                padding: 1rem 2rem;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s;
            }
            .btn-primary {
                background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
                color: white;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(255, 122, 162, 0.4);
            }
            .btn-secondary {
                background: white;
                color: #FF7AA2;
                border: 2px solid #FF7AA2;
            }
            .btn-secondary:hover {
                background: #FF7AA2;
                color: white;
            }
        </style>
    </head>
    <body>
    <div class="login-required">
        <div class="icon">🔒</div>
        <h2>Login Required</h2>
        <p>Please login or create an account to complete your order. Your cart items are saved!</p>
        <div class="buttons">
            <a href="customer/login.php?redirect=checkout" class="btn btn-primary">Login</a>
            <a href="customer/register.php?redirect=checkout" class="btn btn-secondary">Register</a>
        </div>
        <p style="margin-top: 1.5rem; font-size: 0.9rem;">
            <a href="index.php" style="color: #FF7AA2; text-decoration: none;">← Continue Shopping</a>
        </p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// User is logged in, get user data
require_once 'config.php';
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        body { direction: ltr; text-align: left; }
        .checkout-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section h3 {
            color: #FF7AA2;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #3A3A3A;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #FFE5EF;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FF7AA2;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        .order-summary h3 {
            color: #FF7AA2;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #FFE5EF;
        }
        .summary-item.total {
            border-top: 2px solid #FF7AA2;
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: #FF7AA2;
            margin-top: 1rem;
        }
        .cart-item-preview {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #FFE5EF;
        }
        .cart-item-preview img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
        .cart-item-details {
            flex: 1;
        }
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .cart-item-meta {
            font-size: 0.85rem;
            color: #666;
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 2rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 122, 162, 0.4);
        }
        .back-link {
            display: inline-block;
            color: #FF7AA2;
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .payment-options {
            display: grid;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .payment-option {
            padding: 1rem;
            border: 2px solid #FFE5EF;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .payment-option:hover {
            border-color: #FF7AA2;
            background: #FFF5F9;
        }
        .payment-option.selected {
            border-color: #FF7AA2;
            background: #FFF5F9;
        }
        .payment-option input[type="radio"] {
            width: auto;
        }
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<!-- Header -->
<header>
    <div class="header-content">
        <a href="index.php" class="logo">🌷 Bloom Heaven</a>
    </div>
</header>

<div class="checkout-container">
    <a href="index.php" class="back-link">← Continue Shopping</a>

    <h1>Checkout</h1>

    <div class="checkout-grid" id="checkoutGrid">
        <!-- Content will be inserted via JavaScript -->
    </div>
</div>

<script>
    let cart = JSON.parse(localStorage.getItem('bloomheaven_cart')) || [];

    if (cart.length === 0) {
        document.getElementById('checkoutGrid').innerHTML = `
                <div class="empty-cart" style="grid-column: 1/-1;">
                    <h2>🛒 Your cart is empty</h2>
                    <p>Add some beautiful flowers to your cart before checking out.</p>
                    <a href="index.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Start Shopping</a>
                </div>
            `;
    } else {
        renderCheckoutPage();
    }

    function renderCheckoutPage() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.10;
        const delivery = 5.00;
        const total = subtotal + tax + delivery;

        document.getElementById('checkoutGrid').innerHTML = `
                <div class="checkout-form">
                    <form id="checkoutForm" onsubmit="processOrder(event)">
                        <div class="form-section">
                            <h3>Contact Information</h3>
                          <div class="form-group">
    <label>Full Name *</label>
    <input type="text" name="full_name" required value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="John Doe">
</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" required placeholder="ahmad@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Phone Number *</label>
                                    <input type="tel" name="phone" required placeholder="+970 599 123 456">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Delivery Address</h3>
                            <div class="form-group">
                                <label>Street Address *</label>
                                <input type="text" name="address" required placeholder="Palestine St., Al-Nasr District">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>City *</label>
                                    <input type="text" name="city" required placeholder="Nablus">
                                </div>
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input type="text" name="postal_code" placeholder="00000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Preferred Delivery Date *</label>
                                <input type="date" name="delivery_date" required min="${new Date().toISOString().split('T')[0]}">
                            </div>
                            <div class="form-group">
                                <label>Delivery Time</label>
                                <select name="delivery_time">
                                    <option value="morning">Morning (9 AM - 12 PM)</option>
                                    <option value="afternoon">Afternoon (12 PM - 5 PM)</option>
                                    <option value="evening">Evening (5 PM - 8 PM)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Payment Method</h3>
                            <div class="payment-options">
                                <label class="payment-option selected" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <div>
                                        <strong>💵 Cash on Delivery</strong>
                                        <p style="font-size: 0.9rem; color: #666; margin-top: 0.25rem;">Pay cash when your order arrives</p>
                                    </div>
                                </label>
                                <label class="payment-option" onclick="selectPayment(this)">
                                    <input type="radio" name="payment_method" value="card">
                                    <div>
                                        <strong>💳 Credit/Debit Card</strong>
                                        <p style="font-size: 0.9rem; color: #666; margin-top: 0.25rem;">Pay securely with your card</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Special Notes (Optional)</h3>
                            <div class="form-group">
                                <textarea name="notes" rows="3" placeholder="Any special instructions for delivery..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Place Order - $${total.toFixed(2)}</button>
                    </form>
                </div>

                <div class="order-summary">
                    <h3>Order Summary</h3>

                    ${cart.map(item => `
                        <div class="cart-item-preview">
                            <img src="${item.image}" alt="${item.name}">
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-meta">
                                    Size: ${item.size === 'small' ? 'Small' : item.size === 'medium' ? 'Medium' : 'Large'} | Quantity: ${item.quantity}
                                </div>
                                <div class="cart-item-meta" style="color: #FF7AA2; font-weight: 600;">
                                    $${(item.price * item.quantity).toFixed(2)}
                                </div>
                            </div>
                        </div>
                    `).join('')}

                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (10%)</span>
                        <span>$${tax.toFixed(2)}</span>
                    </div>
                    <div class="summary-item">
                        <span>Delivery</span>
                        <span>$${delivery.toFixed(2)}</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total</span>
                        <span>$${total.toFixed(2)}</span>
                    </div>
                </div>
            `;
    }

    function selectPayment(element) {
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        element.classList.add('selected');
    }

    async function processOrder(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.10;
        const delivery = 5.00;
        const total = subtotal + tax + delivery;

        const orderData = {
            customer: {
                full_name: formData.get('full_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                city: formData.get('city'),
                postal_code: formData.get('postal_code')
            },
            delivery: {
                date: formData.get('delivery_date'),
                time: formData.get('delivery_time')
            },
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes'),
            items: cart,
            subtotal: subtotal,
            tax: tax,
            delivery_fee: delivery,
            total: total
        };

        try {
            const response = await fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (result.success) {
                localStorage.removeItem('bloomheaven_cart');
                window.location.href = `order_success.php?order_id=${result.order_id}`;
            } else {
                alert('Order processing error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to process the order. Please try again.');
        }
    }
</script>
</body>
</html>