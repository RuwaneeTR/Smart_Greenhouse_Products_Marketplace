<?php
session_start();
require_once '../includes/dbConnection.php';

// Check if customer is logged in
/*if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];*/
$customer_id = 1; // test user

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fullname   = trim($_POST['fullname'] ?? '');
    $address1   = trim($_POST['address1'] ?? '');
    $address2   = trim($_POST['address2'] ?? '');
    $address3   = trim($_POST['address3'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $district   = trim($_POST['district'] ?? '');
    $nearestCity = trim($_POST['nearest-city'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');

    if ($fullname === '' || $address1 === '' || $city === '' || $phone === '') {
        $error = 'Please fill in all required delivery fields.';
    } else {
        // Build full address string
        $delivery_address = $fullname . ', ' . $address1;
        if ($address2 !== '') $delivery_address .= ', ' . $address2;
        if ($address3 !== '') $delivery_address .= ', ' . $address3;
        $delivery_address .= ', ' . $city . ', ' . $district;
        if ($nearestCity !== '') $delivery_address .= ', ' . $nearestCity;
        $delivery_address .= ', Phone: ' . $phone;

        // Get cart items joined with product and store
        $sql = "
            SELECT 
                c.product_id,
                c.quantity,
                p.price,
                p.name,
                p.store_id
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY p.store_id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        $cart_items = $stmt->fetchAll();

        if (empty($cart_items)) {
            $error = 'Your cart is empty.';
        } else {
            // Group by store_id
            $grouped = [];
            foreach ($cart_items as $item) {
                $grouped[$item['store_id']][] = $item;
            }

            // Create one order per store
            $pdo->beginTransaction();
            try {
                foreach ($grouped as $store_id => $items) {
                    $subtotal = 0.0;
                    foreach ($items as $item) {
                        $subtotal += $item['price'] * $item['quantity'];
                    }
                    $delivery_fee = 0.0; // owner will add later
                    $total_amount = $subtotal + $delivery_fee;

                    // Generate unique order reference
                    $order_ref = 'ORD-' . strtoupper(uniqid());

                    $stmt = $pdo->prepare("
                        INSERT INTO orders 
                            (order_reference, customer_id, store_id, total_amount, delivery_fee, status, delivery_address, created_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
                    ");
                    $stmt->execute([$order_ref, $customer_id, $store_id, $total_amount, $delivery_fee, $delivery_address]);
                    $order_id = $pdo->lastInsertId();


                    // ─── Insert order items AND collect names ───
                    $stmtItem = $pdo->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                        VALUES (?, ?, ?, ?)
                    ");
                    $itemNames = [];
                    foreach ($items as $item) {
                        $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                        $itemNames[] = $item['name'] . ' (x' . $item['quantity'] . ')';
                    }

                    // ─── ✅ NEW: Notify the store owner ───
                    $stmtOwner = $pdo->prepare("SELECT owner_id FROM stores WHERE id = ?");
                    $stmtOwner->execute([$store_id]);
                    $ownerId = $stmtOwner->fetchColumn();

                    if ($ownerId) {
                        $notifTitle   = "New Order Received: #{$order_ref}";
                        $notifMessage = "Order #{$order_ref} placed. Items: " . implode(', ', $itemNames)
                            . ". Total: Rs. " . number_format($total_amount, 2)
                            . ". Please review and confirm delivery fee.";

                        $stmtNotif = $pdo->prepare("
                            INSERT INTO notifications 
                            (user_id, title, priority, category, icon, message,
                            action_label, action_link, is_read, created_at)
                            VALUES (?, ?, 'high', 'orders', 'fa-truck-fast', ?,
                            'View Order', '/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php', 0, NOW())
                        ");
                        $stmtNotif->execute([$ownerId, $notifTitle, $notifMessage]);
                    }

                    // Remove these items from cart for this store
                    $stmtDel = $pdo->prepare("
                        DELETE FROM cart 
                        WHERE user_id = ? AND product_id IN (SELECT product_id FROM products WHERE store_id = ?)
                    ");
                    $stmtDel->execute([$customer_id, $store_id]);
                }
                $pdo->commit();

                // Redirect to success page with last order reference
                header("Location: checkout.php?success=1");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Order failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch cart items for display (if not success)
$cart_items = [];
$subtotal = 0.0;
$total_items = 0;
$grouped_cart = [];

if (!isset($_GET['success'])) {
    $sql = "
        SELECT 
            c.product_id,
            c.quantity,
            p.name,
            p.price,
            p.image,
            p.store_id,
            s.store_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN stores s ON p.store_id = s.id
        WHERE c.user_id = ?
        ORDER BY s.store_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
        $grouped_cart[$item['store_name']][] = $item;
    }
}
include '../includes/header.php';

?>
<!-- Checkout-specific styles -->
<link rel="stylesheet" href="co.css">
<script src="co.js" defer></script>


<main class="checkout-wrapper">
    <div class="checkout-container">
        <div class="checkout-main">
            <?php if (isset($error)): ?>
                <div style="color:red; margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="co-card" style="text-align:center; padding:40px;">
                    <div class="modal-icon-success" style="margin-bottom:20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2>Order Placed Successfully!</h2>
                    <p>Thank you for your order. The store owner will confirm your order soon.</p>
                    <a href="products.php" class="btn-primary-shopping" style="margin-top:20px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <!-- Delivery Form -->
                <section class="co-card delivery-card">
                    <div class="co-card-header">
                        <div class="header-title">
                            <div class="header-icon green-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13" rx="2"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                            </div>
                            <h2>Delivery Details</h2>
                        </div>
                    </div>
                    <form id="checkout-form" class="address-form" method="post" action="checkout.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fullname">FULL NAME</label>
                                <input type="text" id="fullname" name="fullname" required value="<?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">PHONE NUMBER</label>
                                <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($_SESSION['customer_phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="address1">ADDRESS LINE 1</label>
                                <input type="text" id="address1" name="address1" required placeholder="Street address">
                            </div>
                            <div class="form-group">
                                <label for="address2">ADDRESS LINE 2</label>
                                <input type="text" id="address2" name="address2" placeholder="Apartment, suite, etc.">
                            </div>
                            <div class="form-group">
                                <label for="address3">ADDRESS LINE 3 (NEIGHBORHOOD)</label>
                                <input type="text" id="address3" name="address3" placeholder="Neighborhood">
                            </div>
                            <div class="form-group">
                                <label for="city">CITY</label>
                                <input type="text" id="city" name="city" required placeholder="City">
                            </div>
                            <div class="form-group">
                                <label for="district">DISTRICT/PROVINCE</label>
                                <input type="text" id="district" name="district" required placeholder="District or Province">
                            </div>
                            <div class="form-group">
                                <label for="nearest-city">NEAREST CITY (OPTIONAL)</label>
                                <input type="text" id="nearest-city" name="nearest-city" placeholder="Nearest City">
                            </div>
                        </div>
                    </form>
                </section>
                <!-- Payment Method (Simplified) -->
                <!-- Payment Note (Styled) -->
<div style="display: flex; align-items: center; gap: 12px; background-color: #FFFFFF; border: 1px dashed #B0C4B1; border-radius: 12px; padding: 16px 20px; margin: 16px 0 24px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
    <div style="width: 36px; height: 36px; background-color: #E6F0E7; color: #1B361B; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="6" width="20" height="12" rx="2" ry="2"></rect>
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M6 12h.01M18 12h.01"></path>
        </svg>
    </div>
    <p style="font-size: 14.5px; font-weight: 600; color: #1B361B; margin: 0; line-height: 1.4;">Payment will be managed by the owner upon delivery.</p>
</div>
                <!-- Farm Fresh Guarantee Banner -->
                <section class="co-card promo-banner-card">
                    <div class="banner-content">
                        <div class="banner-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            100% Farm Fresh Guarantee
                        </div>
                        <h3>Direct from Farm to Your Doorstep</h3>
                        <p>Our produce is handpicked daily from certified organic local growers. Enjoy peak freshness with eco-friendly temperature-controlled delivery.</p>
                        <div class="banner-features">
                            <div class="feature-item"><span class="check-icon">✓</span> Express Local Delivery</div>
                            <div class="feature-item"><span class="check-icon">✓</span> Zero Plastic Packaging</div>
                            <div class="feature-item"><span class="check-icon">✓</span> Pay Cash on Delivery</div>
                        </div>
                    </div>
                </section>
            </div> <!-- end checkout-main -->

            <!-- Right Column: Order Summary -->
            <aside class="checkout-sidebar">
                <div class="co-card summary-card">
                    <h2 class="summary-title">Order Summary</h2>
                    <div class="cart-items-list" id="cart-items-container">
                        <?php if (empty($cart_items)): ?>
                            <p>Your cart is empty.</p>
                        <?php else: ?>
                            <?php foreach ($grouped_cart as $store_name => $items): ?>
                                <div style="margin-bottom:15px;">
                                    <h3 style="font-size:14px; font-weight:700; color:#333;"><?= htmlspecialchars($store_name) ?></h3>
                                    <?php foreach ($items as $item): ?>
                                        <div class="cart-item">
                                            <div class="item-thumb">
                                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=200&auto=format&fit=crop&q=80'">
                                            </div>
                                            <div class="item-details">
                                                <h4 class="item-name"><?= htmlspecialchars($item['name']) ?></h4>
                                                <div class="item-qty-row">
                                                    <span class="qty-label">Qty: </span>
                                                    <span class="qty-val"><?= $item['quantity'] ?></span>
                                                </div>
                                            </div>
                                            <div class="item-price">Rs. <span><?= number_format($item['price'] * $item['quantity'], 2) ?></span></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="summary-breakdown">
                        <div class="breakdown-row">
                            <span class="row-label">Subtotal</span>
                            <span class="row-value">Rs. <span id="subtotal-val"><?= number_format($subtotal, 2) ?></span></span>
                        </div>
                        <div class="breakdown-row delivery-row">
                            <span class="row-label">Delivery</span>
                            <span class="row-value">Rs. <span id="delivery-val">0.00</span></span>
                        </div>
                    </div>

                    <div class="total-container">
                        <span class="total-label">Total</span>
                        <div class="total-amount">Rs. <span id="grand-total-val"><?= number_format($subtotal, 2) ?></span></div>
                    </div>

                    <?php if (!empty($cart_items)): ?>
                        <button type="submit" form="checkout-form" name="place_order" class="place-order-btn">
                            <span>Place Order</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    <?php endif; ?>

                    <p class="terms-note">
                        By placing your order, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                    </p>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>