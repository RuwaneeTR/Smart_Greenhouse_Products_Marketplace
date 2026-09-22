<?php
session_start();
require_once '../includes/dbConnection.php';

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// 1. Fetch owner details
$stmtUser = $pdo->prepare("SELECT full_name, email, city, address, phone, gap_certificate FROM users WHERE id = :id AND role = 'owner'");
$stmtUser->execute([':id' => $userId]);
$owner = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Store Owner';

// 2. Compute initials from full_name
$nameParts = array_values(array_filter(explode(' ', trim($fullName))));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
} elseif (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
} else {
    $initials = 'TO';
}

// 3. Fetch store by owner_id
$stmtStore = $pdo->prepare("SELECT * FROM stores WHERE owner_id = :owner_id LIMIT 1");
$stmtStore->execute([':owner_id' => $userId]);
$store = $stmtStore->fetch(PDO::FETCH_ASSOC);

$totalProducts = 0;
$pendingOrders = 0;
$totalReviews = 0;
$avgRating = '4.9';
$recentOrders = [];
$inventoryAlerts = [];
$storeId = 0;

if ($store) {
    $storeId = $store['id'];
    $storeName = $store['store_name'];

    // Count products
    $stmtProdCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE store_id = :store_id");
    $stmtProdCount->execute([':store_id' => $storeId]);
    $totalProducts = (int)$stmtProdCount->fetchColumn();

    // Count pending orders
    $stmtPendingCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE store_id = :store_id AND status = 'pending'");
    $stmtPendingCount->execute([':store_id' => $storeId]);
    $pendingOrders = (int)$stmtPendingCount->fetchColumn();

    // Count reviews & average rating
    $stmtReviewCount = $pdo->prepare("SELECT COUNT(*), AVG(rating) FROM reviews WHERE store_id = :store_id");
    $stmtReviewCount->execute([':store_id' => $storeId]);
    $reviewData = $stmtReviewCount->fetch(PDO::FETCH_NUM);
    $totalReviews = (int)($reviewData[0] ?? 0);
    if ($reviewData[1] && $totalReviews > 0) {
        $avgRating = number_format((float)$reviewData[1], 1);
    }

    // Last 3 orders with concatenated product names
    $stmtOrders = $pdo->prepare("
        SELECT o.id, o.order_reference, o.created_at, o.status,
               GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS items_list
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.store_id = :store_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 3
    ");
    $stmtOrders->execute([':store_id' => $storeId]);
    $recentOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

    // Inventory alerts (quantity <= 10)
    $stmtAlerts = $pdo->prepare("
        SELECT id, name, price, quantity, image
        FROM products
        WHERE store_id = :store_id AND quantity <= 10
        ORDER BY quantity ASC
        LIMIT 10
    ");
    $stmtAlerts->execute([':store_id' => $storeId]);
    $inventoryAlerts = $stmtAlerts->fetchAll(PDO::FETCH_ASSOC);
} else {
    $storeName = $fullName;
}

include '../includes/header.php';
?>
<!-- Standalone Reusable Order Confirmation Modal Component -->
<?php include 'order_modal.php'; ?>

<link rel="stylesheet" href="owner_dashboard_v2.css">

<!-- Overlay backdrop for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <!-- Left Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Profile Mini-Card at Top -->
        <div class="profile-card">
          <div class="avatar-large-wrapper">
            <div class="avatar-circle-lg"><?= htmlspecialchars($initials) ?></div>
               <span class="online-indicator"></span>
            </div>
            <h3 class="profile-name"><?= htmlspecialchars($fullName) ?></h3>
            <p class="profile-subtitle">Store Owner • <?= htmlspecialchars($owner['city']) ?></p>
         </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="sidebar-menu">
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="sidebar-link active">
            <i class="fa-solid fa-house"></i>
            <span>Home (Dashboard)</span>
            <span class="active-dot"></span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/add_product.php" class="sidebar-link">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Add Products</span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/order_details.php" class="sidebar-link">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Order Details</span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/notifications.php" class="sidebar-link">
            <i class="fa-solid fa-bell"></i>
            <span>Notifications</span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/members.php" class="sidebar-link">
            <i class="fa-solid fa-credit-card"></i>
            <span>Membership Fee</span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/edit_profile.php" class="sidebar-link">
            <i class="fa-solid fa-user-circle"></i>
            <span>Edit Profile</span>
          </a>
          <a href="/Smart_Greenhouse_Products_Marketplace/owner/reviews.php" class="sidebar-link">
            <i class="fa-solid fa-star"></i>
            <span>Reviews and Ratings</span>
          </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- A. Welcome Banner -->
        <section class="welcome-banner">
            <div class="welcome-text">
                <h1 class="greeting-title">Welcome back, <?= htmlspecialchars($storeName) ?></h1>
                <p class="greeting-sub">Here's what's happening in your greenhouse today.</p>
            </div>
            <a href="/Smart_Greenhouse_Products_Marketplace/owner/add_product.php" class="btn btn-add-product" id="quickAddProductBtn">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        </section>

        <!-- B. Stats Grid -->
        <section class="stats-grid">
            <!-- Card 1: Total Products -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon icon-products">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <span class="stat-badge badge-active">Active</span>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number"><?= $totalProducts ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>

            <!-- Card 2: Pending Orders -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon icon-orders">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <span class="stat-badge badge-pending">pending</span>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number"><?= $pendingOrders ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>

            <!-- Card 3: Total Reviews -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon icon-reviews">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <span class="stat-badge badge-rating">★ <?= $avgRating ?></span>
                </div>
                <div class="stat-card-body">
                    <div class="stat-number"><?= $totalReviews ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
            </div>

            <!-- Card 4: Membership Status -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon icon-membership">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <span class="stat-badge badge-active">✓ Active</span>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value-text">Paid</div>
                    <div class="stat-label">Membership Status</div>
                </div>
            </div>
        </section>

        <!-- C. Two-Column Row -->
        <section class="content-split-row">
            <!-- Left Panel (Wider): Recent Orders -->
            <div class="card-panel recent-orders-card">
                <div class="panel-header">
                    <h3 class="panel-title">Recent Orders</h3>
                    <p class="panel-subtitle">Latest customer transactions from your shop</p>
                </div>

                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ORDER ID</th>
                                <th>ITEM</th>
                                <th>DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="4" class="empty-table-cell">
                                        No orders yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr onclick="window.location.href='?order_id=<?= $order['id'] ?>'" style="cursor: pointer;">
                                        <td class="order-id">#<?= htmlspecialchars($order['order_reference'] ?? $order['id']) ?></td>
                                        <td class="order-items"><?= htmlspecialchars($order['items_list'] ?? 'Product Item') ?></td>
                                        <td class="order-date"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <?php
                                            $status = strtolower($order['status'] ?? 'pending');
                                            $badgeClass = match($status) {
                                                'confirmed' => 'status-confirmed',
                                                'processing' => 'status-processing',
                                                'delivered' => 'status-delivered',
                                                default => 'status-pending',
                                            };
                                            ?>
                                            <span class="badge-status <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel (280px): Inventory Alerts -->
            <div class="card-panel inventory-alerts-card">

                <div class="inventory-alerts-body">
                    <?php if (empty($inventoryAlerts)): ?>
                        <div class="empty-alert-state">
                            All products are well stocked.
                        </div>
                    <?php else: ?>
                        <ul class="inventory-alert-list">
                            <?php foreach ($inventoryAlerts as $item): ?>
                                <li class="inventory-alert-item">
                                    <div class="product-thumb">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="/Smart_Greenhouse_Products_Marketplace/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <?php else: ?>
                                            <i class="fa-solid fa-leaf"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <span class="product-name"><?= htmlspecialchars($item['name']) ?></span>
                                        <span class="product-stock-count"><?= (int)$item['quantity'] ?> left in stock</span>
                                    </div>
                                    <span class="badge-warning">Low</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-card">
        <div class="modal-icon text-danger">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3 class="modal-title">Confirm Logout</h3>
        <p class="modal-text">Are you sure you want to log out of your CropS account?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelLogoutBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Logout</button>
        </div>
    </div>
</div>

<script src="owner_dashboard.js"></script>
<?php include '../includes/footer.php'; ?>
