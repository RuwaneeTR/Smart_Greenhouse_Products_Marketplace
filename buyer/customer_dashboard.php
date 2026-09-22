<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Buyer info
$stmt = $pdo->prepare("SELECT full_name, email, city, address, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$buyer = $stmt->fetch();

$nameParts = explode(' ', $buyer['full_name']);
$initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
$firstName = $nameParts[0];

// Recommended products
$products = $pdo->query("
    SELECT p.id, p.name, p.price, p.description, p.image, p.category, s.store_name AS store
    FROM products p
    JOIN stores s ON p.store_id = s.id
    ORDER BY p.created_at DESC
    LIMIT 4
")->fetchAll();

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.order_reference AS id, DATE_FORMAT(o.created_at, '%b %d, %Y') AS date,
           o.status, o.total_amount AS total,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') AS items
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    WHERE o.customer_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 3
");
$stmt->execute([$userId]);
$recentOrders = $stmt->fetchAll();

// Updates
$stmt = $pdo->prepare("
    SELECT id, message AS text, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->execute([$userId]);
$updates = $stmt->fetchAll();

// Unread count
$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadCount = (int)$stmt->fetch()['cnt'];

include '../includes/header.php';
?>


<?php include 'order_confirmed_modal.php'; ?>


<!-- Page-specific styles (loaded after shared header) -->
<link rel="stylesheet" href="customer_dashboard.css">

  <div class="app-layout">
    <aside class="sidebar">
      <div class="profile-card">
        <div class="avatar-large-wrapper">
          <div class="avatar-circle-lg" id="sidebarAvatar"><?= htmlspecialchars($initials) ?></div>
          <span class="online-indicator"></span>
        </div>
        <h3 class="profile-name" id="sidebarUserName"><?= htmlspecialchars($buyer['full_name']) ?></h3>
        <p class="profile-subtitle" id="sidebarUserSubtitle">Verified Buyer • <?= htmlspecialchars($buyer['city']) ?></p>
      </div>

      <nav class="sidebar-nav">
        <a href="customer_dashboard.php" class="sidebar-link active">
          <i class="fas fa-home"></i>
          <span>Home(Dashboard)</span>
          <span class="active-dot"></span>
        </a>
        <a href="notifications.php" class="sidebar-link">
          <i class="far fa-bell"></i>
          <span>Notifications</span>
          <span class="nav-badge badge-unread" id="sidebarUnreadBadge"><?= $unreadCount ?></span>
        </a>
        <a href="edit_profile.php" class="sidebar-link">
          <i class="far fa-user-circle"></i>
          <span>Edit Profile</span>
        </a>
      </nav>

    </aside>

    <main class="main-content">
      <div class="dashboard-grid">
        <div class="content-primary">

          <section class="welcome-banner">
            <div class="welcome-header">
              <h2>Welcome back, <span id="welcomeUserName"><?= htmlspecialchars($firstName) ?></span> 🌱</h2>
              <p>Your kitchen and garden are thriving. Fresh seasonal arrivals are in for <?= htmlspecialchars($buyer['city']) ?>.</p>
            </div>
            <div class="quick-actions-row">
              <a href="../products.php" class="action-card">
                <div class="action-icon"><i class="fas fa-search"></i></div>
                <span>Shop Fruits & Veggies</span>
              </a>
              <a href="Smart_Greenhouse_Products_Marketplace/stores.php" class="action-card">
                <div class="action-icon"><i class="fas fa-store"></i></div>
                <span>Local Farms & Greenhouses</span>
              </a>
              <a href="Smart_Greenhouse_Products_Marketplace/cart.php" class="action-card">
                <div class="action-icon"><i class="fas fa-truck"></i></div>
                <span>Track Delivery</span>
              </a>
            </div>
          </section>

          <section class="section-container">
            <div class="section-header">
              <h3>Recommended for You</h3>
              <a href="Smart_Greenhouse_Products_Marketplace/products.php" class="view-all-link">View all</a>
            </div>
            <div class="products-grid" id="productsGrid"></div>
          </section>

          <section class="section-container">
            <div class="section-header">
              <h3>Recent Orders</h3>
            </div>
            <div class="orders-card">
              <table class="orders-table">
                <thead>
                  <tr>
                    <th>ORDER ID</th>
                    <th>ITEMS</th>
                    <th>DATE</th>
                    <th>STATUS</th>
                    <th>TOTAL</th>
                  </tr>
                </thead>
                <tbody id="ordersTbody"></tbody>
              </table>
            </div>
          </section>

        </div>

        <aside class="content-sidebar">
          <div class="updates-card">
            <div class="updates-header">
              <h3><i class="far fa-bell"></i> Updates</h3>
              <a href="#" id="markAllReadLink" class="mark-read-link">Mark all read</a>
            </div>
            <div class="updates-list" id="updatesList"></div>
            <button class="btn btn-outline-full" onclick="window.location.href='notifications.php'">
              View all updates
            </button>
          </div>
        </aside>
      </div>
    </main>
  </div>

  
<script>
    window.CROPS_DATA = {
    buyer: <?= json_encode($buyer) ?>,
    initials: <?= json_encode($initials) ?>,
    firstName: <?= json_encode($firstName) ?>,
    recommendedProducts: <?= json_encode($products) ?>,
    recentOrders: <?= json_encode($recentOrders) ?>,
    updates: <?= json_encode($updates) ?>
};
</script>
<script src="customer_dashboard.js"></script>

<?php include '../includes/footer.php'; ?>
