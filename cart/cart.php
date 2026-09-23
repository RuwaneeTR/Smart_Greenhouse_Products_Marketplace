<?php
session_start();
require_once '../includes/dbConnection.php';

// Temporarily bypass login (remove later)
// if (!isset($_SESSION['customer_id'])) {
//     header('Location: login.php');
//     exit;
// }
// $customer_id = $_SESSION['customer_id'];

$user_id = 1; // test user (use actual logged-in user ID later)

// Handle quantity update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity < 1) $quantity = 1;

    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);

    header('Location: cart.php');
    exit;
}

// Handle remove item
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    header('Location: cart.php');
    exit;
}

// Fetch cart items with product and store info
$sql = "
    SELECT 
        c.product_id,
        c.quantity,
        p.name,
        p.price,
        p.image,
        p.description,
        p.category,
        p.quantity AS stock,   -- products table uses 'quantity' for available stock
        p.store_id,
        s.store_name,
        s.city AS shipping_location  -- use city instead of missing shipping_location
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN stores s ON p.store_id = s.id
    WHERE c.user_id = ?
    ORDER BY s.store_name, p.name
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Group items by store
$stores = [];
foreach ($cart_items as $item) {
    $storeKey = $item['store_id'] ?? 'default';
    if (!isset($stores[$storeKey])) {
        $stores[$storeKey] = [
            'store_name' => $item['store_name'] ?? 'My Cart',
            'shipping_location' => $item['shipping_location'] ?? '',
            'items' => []
        ];
    }
    $stores[$storeKey]['items'][] = $item;
}

// Calculate totals
$total_items = 0;
$subtotal = 0.0;
foreach ($cart_items as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['price'] * $item['quantity'];
}
include '../includes/header.php';
?>

<!-- Page-specific styles (loaded after shared header) -->
<link rel="stylesheet" href="/Smart_Greenhouse_Products_Marketplace/static/style.css">
<link rel="stylesheet" href="cart.css">

  <!-- Main Content Wrapper -->
  <main class="main-content">
    <div class="page-container">
      
      <!-- Page Header -->
      <div class="cart-header">
        <div class="title-group">
          <h1 class="page-title">Your Cart</h1>
          <p class="page-subtitle">Review your items before proceeding to checkout.</p>
        </div>
        <div class="cart-count-badge" id="header-item-count"><?= $total_items ?> item<?= $total_items != 1 ? 's' : '' ?></div>
      </div>

      <?php if (empty($stores)): ?>
        <!-- Empty Cart State -->
        <div class="empty-cart-state" id="empty-cart-state">
          <div class="empty-cart-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
          </div>
          <h2 class="empty-title">Your cart is empty</h2>
          <p class="empty-subtitle">Looks like you haven't added any botanical items or produce to your cart yet.</p>
          <div class="empty-actions">
            <a href="products.php" class="btn-primary-shopping">Continue Shopping</a>
          </div>
        </div>
      <?php else: ?>
        <!-- Main Cart Layout Grid -->
        <div class="cart-grid" id="cart-grid">
          
          <!-- Left Column: Cart Items grouped by Store -->
          <section class="cart-items-section" id="cart-items-container">
            <?php foreach ($stores as $store): ?>
              <div class="store-card">
                <div class="store-header">
                  <div class="store-icon-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                      <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                  </div>
                  <div class="store-info">
                    <span class="store-name"><?= htmlspecialchars($store['store_name']) ?></span>
                    <span class="store-shipping"><?= htmlspecialchars($store['shipping_location'] ?? 'SHIPS FROM LOCATION') ?></span>
                  </div>
                </div>
                <div class="store-items-list">
                  <?php foreach ($store['items'] as $item): ?>
                    <div class="cart-item-row">
                      <div class="item-img-container">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-img" onerror="this.src='https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=200&auto=format&fit=crop&q=80'">
                      </div>
                      <div class="item-details">
                        <div>
                          <div class="item-top-row">
                            <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="item-price-unit">Rs. <?= number_format($item['price'], 2) ?></div>
                          </div>
                          <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                          
                          <div class="item-badges">
                            <span class="badge badge-category"><?= htmlspecialchars($item['category']) ?></span>
                            <?php if ($item['stock'] > 0): ?>
                              <span class="badge badge-stock">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                In Stock
                              </span>
                            <?php else: ?>
                              <span class="badge badge-stock" style="background:#fdd; color:#900;">Out of Stock</span>
                            <?php endif; ?>
                          </div>
                          
                          <div class="item-sku">SKU: N/A</div>
                        </div>
                        <div class="item-bottom-row">
                          <!-- Quantity update form -->
                          <form method="post" action="cart.php" style="display: flex; align-items: center;">
                            <div class="quantity-control">
                              <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="qty-btn" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?> aria-label="Decrease quantity">-</button>
                              <span class="qty-display"><?= $item['quantity'] ?></span>
                              <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="qty-btn" aria-label="Increase quantity">+</button>
                            </div>
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                          </form>
                          <a href="cart.php?remove=<?= $item['product_id'] ?>" class="remove-btn" onclick="return confirm('Remove this item?');">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            <span>Remove</span>
                          </a>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </section>

          <!-- Right Column: Order Summary Sidebar -->
          <aside class="order-summary-sidebar">
            <div class="summary-card">
              <div class="summary-card-accent"></div>
              <h2 class="summary-title">Order Summary</h2>
              
              <div class="summary-details">
                <div class="summary-row">
                  <span class="summary-label">Subtotal (<?= $total_items ?> item<?= $total_items != 1 ? 's' : '' ?>)</span>
                  <span class="summary-value">Rs. <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                  <span class="summary-label">Delivery Fee</span>
                  <span class="summary-value delivery-free">Rs. 0.00</span>
                </div>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-total-container">
                <span class="total-label">Total</span>
                <div class="total-price-wrap">
                  <div class="total-price">Rs. <?= number_format($subtotal, 2) ?></div>
                  <div class="currency-tag">LKR</div>
                </div>
              </div>

              <a href="checkout.php" class="checkout-btn" style="text-decoration: none;">
                <span>Proceed to Checkout</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
              </a>
            </div>
          </aside>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <?php include '../includes/footer.php'; ?>

</body>
</html>