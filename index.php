<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloom Heaven - Where Every Petal Tells a Story</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<!-- Header -->
<header>
    <div class="header-content">
        <a href="index.php" class="logo">🌷 Bloom Heaven</a>
        <div class="search-bar">
            <input type="text" placeholder="Search for flowers..." id="searchInput">
        </div>
        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_type'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="user-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Admin Panel
                    </a>
                <?php else: ?>
                    <a href="customer/dashboard.php" class="user-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo $_SESSION['user_type'] === 'admin' ? 'admin' : 'customer'; ?>/logout.php" class="logout-link">
                    Logout
                </a>
            <?php else: ?>
                <a href="customer/login.php" class="login-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Login
                </a>
                <a href="customer/register.php" class="register-btn">
                    Register
                </a>
            <?php endif; ?>

            <div class="cart-icon" onclick="toggleCart()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span class="cart-count">0</span>
            </div>
        </div>
    </div>
</header>


<!--
---
## ✅ Now Test Everything:

1. Register a customer account:
http://localhost:8000/customer/register.php

2. Login as customer:
http://localhost:8000/customer/login.php

3. Add items to cart and checkout
- Now it requires login!

4. After placing order:
- Customer gets confirmation email
- Admin gets order notification email
- Success page shows

5. Admin login:
http://localhost:8000/admin/login.php
Email: admin@bloomheaven.com
Password: Admin@123
---
-->

<body>
<!-- Header -->


<!-- Navigation -->
<nav>
    <div class="nav-content">
        <button class="nav-btn active" data-category="all">All</button>
        <button class="nav-btn" data-category="birthday">Birthday</button>
        <button class="nav-btn" data-category="wedding">Wedding</button>
        <button class="nav-btn" data-category="anniversary">Anniversary</button>
        <button class="nav-btn" data-category="sympathy">Sympathy</button>
        <button class="nav-btn" data-category="get-well">Get Well</button>
        <button class="nav-btn" data-category="seasonal">Seasonal</button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <h1>Where Every Petal Tells a Story</h1>
    <p>Handcrafted bouquets delivered with love</p>
</section>

<!-- Products Section -->
<main class="products-section">
    <h2 class="section-title">Our Beautiful Collections</h2>
    <div class="product-grid" id="productGrid">
        <!-- Products will be loaded here by JavaScript -->
        <p style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #999;">
            Loading products...
        </p>
    </div>
</main>

<!-- Product Detail Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">×</button>
        <div class="modal-body">
            <div class="modal-image" id="modalImage"></div>
            <div class="modal-details">
                <h2 id="modalTitle"></h2>
                <div class="product-price" id="modalPrice"></div>
                <p id="modalDescription"></p>

                <div class="option-group">
                    <label>Size:</label>
                    <select id="modalSize">
                        <option value="small">Small</option>
                        <option value="medium" selected>Medium</option>
                        <option value="large">Large</option>
                    </select>
                </div>

                <div class="option-group">
                    <label>Personalized Card Message (Optional):</label>
                    <textarea id="modalMessage" placeholder="Add a heartfelt message to your bouquet..."></textarea>
                </div>

                <div class="option-group">
                    <label>Quantity:</label>
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="changeModalQuantity(-1)">−</button>
                        <span class="quantity-value" id="modalQuantity">1</span>
                        <button class="quantity-btn" onclick="changeModalQuantity(1)">+</button>
                    </div>
                </div>

                <button class="btn btn-primary" style="width: 100%;" onclick="addToCartFromModal()">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Shopping Cart Drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <h2>Shopping Cart</h2>
        <button class="modal-close" onclick="toggleCart()">×</button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="empty-cart">
            <p>🛒</p>
            <p>Your cart is empty</p>
        </div>
    </div>
    <div class="cart-footer">

        <div class="cart-total">
            <span>Total:</span>
            <span id="cartTotal">$0.00</span>
        </div>
        <button class="btn btn-primary" style="width: 100%;" onclick="proceedToCheckout()">Proceed to Checkout</button>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">Item added to cart!</div>

<!-- Footer -->
<!-- Footer -->
<!-- Footer -->
<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><a href="https://wa.me/970599123456" target="_blank" style="color: #666; text-decoration: none;">📱 Whatsapp: +970 599 123 456</a></p>
            <p><a href="mailto:info@bloomheaven.ps" style="color: #666; text-decoration: none;">📧 info@bloomheaven.ps</a></p>
            <p>Nablus-Palestine</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <a href="#about">We Are</a>
            <a href="#delivery-info" onclick="showDeliveryInfo(); return false;">Dilevery Info</a>
            <a href="#faq" onclick="showFAQ(); return false;">Popular Qustions</a>
            <a href="https://wa.me/970599123456" target="_blank">Contact Us</a>
        </div>
        <div class="footer-section">
            <h3>Shop</h3>
            <a href="#" onclick="filterCategory('birthday'); return false;">Birthday </a>
            <a href="#" onclick="filterCategory('wedding'); return false;">Wedding</a>
            <a href="#" onclick="filterCategory('sympathy'); return false;">Sympathy</a>
            <a href="#" onclick="filterCategory('seasonal'); return false;">Seasonal</a>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <a href="https://www.instagram.com/bloomheaven" target="_blank">📷 Instagram</a>
            <a href="https://www.facebook.com/bloomheaven" target="_blank">📘 Facebook</a>
            <a href="https://wa.me/970599123456" target="_blank">💬 WhatsApp</a>
            <a href="https://t.me/bloomheaven" target="_blank">✈️ Telegram</a>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2025 Bloom Heaven. All rights reserved. Made with 💐 and love.</p>
    </div>
</footer>

<!-- Modal for Delivery Info -->
<div class="modal" id="deliveryModal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <button class="modal-close" onclick="closeDeliveryModal()">×</button>
        <div style="padding: 2rem;">
            <h2 style="color: #FF7AA2; margin-bottom: 1.5rem;">📦 Delivery Information</h2>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">🕐Delivery Times</h3>
                <p style="color: #666; line-height: 1.8;">
                    • Morning: 9:00 AM - 12:00 PM<br>
                    • Afternoon: 12:00 PM - 5:00 PM<br>
                    • Evening: 5:00 PM - 8:00 PM
                </p>

            </div>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">💰 رسوم التوصيل</h3>
                <p style="color: #666; line-height: 1.8;">
                    • Within Nablus: $5.00<br>
                    • Surrounding cities: $10.00<br>
                    • Free delivery for orders over $100
                </p>

            </div>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">📍 مناطق التوصيل</h3>
                <p style="color: #666; line-height: 1.8;">
                    We deliver across Nablus and surrounding cities:<br>
                    Nablus, Tulkarm, Jenin, Qalqilya, Ramallah
                </p>

            </div>

            <div style="background: #FFF5F9; padding: 1rem; border-radius: 10px;">
                <strong style="color: #FF7AA2;">💡 نصيحة:</strong>
                <p style="color: #666; margin-top: 0.5rem;">
                    Book at least 24 hours in advance to ensure on-time delivery!
                </p>

            </div>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="https://wa.me/970599123456?text=Hello, I would like to inquire about delivery"
                   target="_blank"
                   class="btn btn-primary">
                    💬 Contact Us on WhatsApp
                </a>

            </div>
        </div>
    </div>
</div>

<!-- Modal for FAQ -->
<div class="modal" id="faqModal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <button class="modal-close" onclick="closeFAQModal()">×</button>
        <div style="padding: 2rem;">
            <h2 style="color: #FF7AA2; margin-bottom: 1.5rem;">❓ Frequently Asked Questions</h2>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">How do I place an order?</h3>
                <p style="color: #666; line-height: 1.8;">
                    Choose your favorite flowers, add them to the cart, then click "Checkout" and fill in your details.
                </p>
            </div>


            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">Can I add a message with the flowers?</h3>
                <p style="color: #666; line-height: 1.8;">
                    Yes! When adding the product to the cart, you can write a personal message that will be attached to the bouquet.
                </p>
            </div>


            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">What payment methods are available?</h3>
                <p style="color: #666; line-height: 1.8;">
                    We accept cash on delivery or electronic payment by credit card.
                </p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">How long do the flowers stay fresh?</h3>
                <p style="color: #666; line-height: 1.8;">
                    Our bouquets stay fresh for 5–7 days with proper care.
                </p>
            </div>

        </div>

        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: #3A3A3A; margin-bottom: 0.5rem;">Can I cancel or modify my order?</h3>
            <p style="color: #666; line-height: 1.8;">
                Yes, you can modify or cancel your order within 12 hours of placing it.
            </p>
        </div>

        <div style="background: #FFF5F9; padding: 1rem; border-radius: 10px; text-align: center;">
            <p style="color: #666; margin-bottom: 1rem;">
                Have another question? Get in touch with us!
            </p>
        </div>

        <a href="https://wa.me/970599123456?text=Hello, I have a question about..."
           target="_blank"
           class="btn btn-primary">
            💬 Contact Us on WhatsApp
        </a>

    </div>
        </div>
    </div>
</div>

<script>
    function showDeliveryInfo() {
        document.getElementById('deliveryModal').classList.add('active');
    }

    function closeDeliveryModal() {
        document.getElementById('deliveryModal').classList.remove('active');
    }

    function showFAQ() {
        document.getElementById('faqModal').classList.add('active');
    }

    function closeFAQModal() {
        document.getElementById('faqModal').classList.remove('active');
    }

    function filterCategory(category) {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        const btn = document.querySelector(`[data-category="${category}"]`);
        if (btn) {
            btn.classList.add('active');
            if (typeof loadProducts === 'function') {
                loadProducts(category);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // إغلاق النوافذ عند الضغط خارجها
    document.getElementById('deliveryModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'deliveryModal') {
            closeDeliveryModal();
        }
    });

    document.getElementById('faqModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'faqModal') {
            closeFAQModal();
        }
    });
</script>
<!-- JavaScript - MUST be at the end before </body> -->
<script src="js/script.js"></script>
<script>
    console.log('Script tag loaded');

    // Helper function for footer category links
    function filterCategory(category) {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        const btn = document.querySelector(`[data-category="${category}"]`);
        if (btn) {
            btn.classList.add('active');
            loadProducts(category);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
</script>
</body>
</html>