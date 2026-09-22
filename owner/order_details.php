<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Owner for sidebar
$stmt = $pdo->prepare("SELECT full_name, city FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Store Owner';

// Compute initials
$nameParts = array_values(array_filter(explode(' ', trim($fullName))));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
} elseif (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
} else {
    $initials = 'TO';
}

// Owner's store
$stmt = $pdo->prepare("SELECT id FROM stores WHERE owner_id = ? LIMIT 1");
$stmt->execute([$userId]);
$store = $stmt->fetch(PDO::FETCH_ASSOC);
$storeId = $store['id'] ?? 0;

// Filters
$statusFilter = $_GET['status'] ?? 'all';

// Fetch all orders for this store joined with buyer
$sql = "
    SELECT o.*, 
           u.full_name AS buyer_name, u.city AS buyer_city,
           (SELECT GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ')
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = o.id) AS items_summary
    FROM orders o
    JOIN users u ON u.id = o.customer_id
    WHERE o.store_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$storeId]);
$allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalCount      = count($allOrders);
$pendingCount    = count(array_filter($allOrders, fn($o) => $o['status'] === 'pending'));
$processingCount = count(array_filter($allOrders, fn($o) => $o['status'] === 'processing'));
$inTransitCount  = count(array_filter($allOrders, fn($o) => $o['status'] === 'confirmed'));
$deliveredCount  = count(array_filter($allOrders, fn($o) => $o['status'] === 'delivered'));

// Apply filter for display list
$filteredOrders = $allOrders;
if ($statusFilter !== 'all') {
    $map = ['pending' => 'pending', 'processing' => 'processing', 'intransit' => 'confirmed', 'delivered' => 'delivered'];
    $key = $map[$statusFilter] ?? null;
    if ($key) {
        $filteredOrders = array_values(array_filter($allOrders, fn($o) => $o['status'] === $key));
    }
}

// Selected order for detail panel
$selectedId = (int)($_GET['id'] ?? ($filteredOrders[0]['id'] ?? 0));
$selectedOrder = null;
foreach ($allOrders as $o) {
    if ($o['id'] == $selectedId) {
        $selectedOrder = $o;
        break;
    }
}

// Fetch order items for selected order
$selectedItems = [];
if ($selectedOrder) {
    $stmt = $pdo->prepare("
        SELECT oi.quantity, oi.price_at_purchase, p.name, p.category
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$selectedOrder['id']]);
    $selectedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="order_details.css">

<!-- Overlay backdrop for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <!-- Left Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-user-mini">
            <div class="mini-avatar-circle">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div class="mini-user-info">
                <h4 class="mini-name"><?= htmlspecialchars($fullName) ?></h4>
                <span class="mini-role">Store Owner</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/add_product.php" class="sidebar-link">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Add Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/order_details.php" class="sidebar-link active">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Order Details</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/notifications.php" class="sidebar-link">
                        <i class="fa-solid fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/membership.php" class="sidebar-link">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Membership Fee</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/edit_profile.php" class="sidebar-link">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/reviews.php" class="sidebar-link">
                        <i class="fa-solid fa-star"></i>
                        <span>Reviews and Ratings</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-divider"></div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Top Hero Banner (Hardcoded Chrome) -->
        <div class="order-hero-banner">
            <div class="hero-text-content">
                <h1 class="page-title">Order Management</h1>
            </div>
        </div>

        <!-- KPI Stats Grid (4 Cards from DB) -->
        <div class="kpi-stats-grid">
            <!-- Card 1: Total Orders -->
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-title">TOTAL ORDERS (Q4)</span>
                    <div class="kpi-icon icon-blue">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= $totalCount ?></div>
            </div>

            <!-- Card 2: Pending Dispatch -->
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-title">PENDING DISPATCH</span>
                    <div class="kpi-icon icon-amber">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                </div>
                <div class="kpi-value-row">
                    <span class="kpi-value"><?= $pendingCount ?></span>
                    <span class="badge-urgent">Urgent</span>
                </div>
            </div>

            <!-- Card 3: In Transit -->
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-title">IN TRANSIT</span>
                    <div class="kpi-icon icon-blue">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= $inTransitCount ?></div>
            </div>

            <!-- Card 4: Delivered & Signed -->
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-title">DELIVERED & SIGNED</span>
                    <div class="kpi-icon icon-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= $deliveredCount ?></div>
            </div>
        </div>

        <!-- Filter Tabs & Search Bar Row -->
        <div class="filter-controls-row">
            <div class="status-tabs-group">
                <a href="?status=all" class="status-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
                    All (<?= $totalCount ?>)
                </a>
                <a href="?status=pending" class="status-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                    Pending (<?= $pendingCount ?>)
                </a>
                <a href="?status=processing" class="status-tab <?= $statusFilter === 'processing' ? 'active' : '' ?>">
                    Processing (<?= $processingCount ?>)
                </a>
                <a href="?status=intransit" class="status-tab <?= $statusFilter === 'intransit' ? 'active' : '' ?>">
                    In Transit (<?= $inTransitCount ?>)
                </a>
                <a href="?status=delivered" class="status-tab <?= $statusFilter === 'delivered' ? 'active' : '' ?>">
                    Delivered (<?= $deliveredCount ?>)
                </a>
                <a href="?status=cancelled" class="status-tab <?= $statusFilter === 'cancelled' ? 'active' : '' ?>">
                    Cancelled (0)
                </a>
            </div>

        </div>

        <!-- Search Input Bar -->
        <div class="search-bar-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="orderSearchInput" class="search-input" placeholder="Search by Order ID, Buyer name, or crop...">
        </div>

        <!-- Two Column Main Row -->
        <div class="manifest-split-row">
            <!-- LEFT COLUMN (Wider): Wholesale Manifest Log -->
            <div class="card-panel manifest-panel">
                <div class="panel-header-flex">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-list-check"></i> Wholesale Manifest Log
                    </h3>
                    <span class="entries-count-text">
                        Showing 1-<?= count($filteredOrders) ?> of <?= count($filteredOrders) ?> ENTRIES
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="manifest-table" id="manifestTable">
                        <thead>
                            <tr>
                                <th>ORDER ID & DATE</th>
                                <th>BUYER & CHANNEL</th>
                                <th>HARVEST PAYLOAD</th>
                                <th>AMOUNT</th>
                                <th>FULFILLMENT STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filteredOrders)): ?>
                                <tr>
                                    <td colspan="5" class="empty-table-cell">
                                        No orders match this filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filteredOrders as $o): ?>
                                    <?php
                                    $isSelected = ($o['id'] == $selectedId);
                                    $status = strtolower($o['status'] ?? 'pending');
                                    $statusBadgeClass = match($status) {
                                        'confirmed' => 'status-in-transit',
                                        'processing' => 'status-processing',
                                        'delivered' => 'status-delivered',
                                        default => 'status-pending-dispatch',
                                    };
                                    $statusLabel = match($status) {
                                        'confirmed' => 'IN TRANSIT',
                                        'processing' => 'PROCESSING',
                                        'delivered' => 'DELIVERED',
                                        default => 'PENDING DISPATCH',
                                    };
                                    ?>
                                    <tr class="manifest-row <?= $isSelected ? 'selected-row' : '' ?>" 
                                        onclick="window.location.href='?id=<?= $o['id'] ?>&status=<?= htmlspecialchars($statusFilter) ?>'">
                                        <td class="col-order-id">
                                            <div class="order-ref">#<?= htmlspecialchars($o['order_reference'] ?? $o['id']) ?></div>
                                            <div class="order-date"><?= date('M d, Y', strtotime($o['created_at'])) ?></div>
                                        </td>
                                        <td class="col-buyer">
                                            <div class="buyer-name"><?= htmlspecialchars($o['buyer_name']) ?></div>
                                            <div class="buyer-city"><?= htmlspecialchars($o['buyer_city']) ?> Hub</div>
                                        </td>
                                        <td class="col-payload">
                                            <?= htmlspecialchars($o['items_summary'] ?? 'Greenhouse Harvest') ?>
                                        </td>
                                        <td class="col-amount">
                                            <div class="amount-val">$<?= number_format($o['total_amount'], 2) ?></div>
                                            <div class="pay-method">Direct EFT</div>
                                        </td>
                                        <td class="col-status">
                                            <span class="badge-fulfillment <?= $statusBadgeClass ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="manifest-footer">
                    <span class="footer-info">Showing 1 to <?= count($filteredOrders) ?> of <?= $totalCount ?> orders</span>
                    <div class="pagination">
                        <span class="page-btn disabled"><i class="fa-solid fa-chevron-left"></i> Previous</span>
                        <span class="page-num active">1</span>
                        <span class="page-num">2</span>
                        <span class="page-num">3</span>
                        <span class="page-ellipsis">...</span>
                        <span class="page-num">26</span>
                        <span class="page-btn">Next <i class="fa-solid fa-chevron-right"></i></span>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (380px): Order Detail Panel -->
            <div class="card-panel detail-panel">
                <?php if ($selectedOrder): ?>
                    <div class="detail-header">
                        <div class="detail-title-row">
                            <h2 class="detail-order-id">Order #<?= htmlspecialchars($selectedOrder['order_reference'] ?? $selectedOrder['id']) ?></h2>
                            <span class="badge-priority">PRIORITY</span>
                            <button type="button" class="btn-icon-print" title="Print Manifest" onclick="window.print()">
                                <i class="fa-solid fa-print"></i>
                            </button>
                        </div>
                        <p class="detail-subtitle">
                            Created <?= date('M d, Y H:i', strtotime($selectedOrder['created_at'])) ?> · <?= htmlspecialchars($selectedOrder['buyer_city'] ?? 'Central Region') ?>
                        </p>
                    </div>

                    <!-- Horizontal Fulfillment Milestone -->
                    <div class="fulfillment-timeline">
                        <div class="timeline-header">
                            <span>FULFILLMENT TIMELINE</span>
                            <span class="timeline-status-sub">Awaiting Cold Transport</span>
                        </div>
                        <div class="milestones-row">
                            <?php
                            $st = strtolower($selectedOrder['status'] ?? 'pending');
                            $step1 = true;
                            $step2 = in_array($st, ['processing', 'confirmed', 'delivered']);
                            $step3 = in_array($st, ['processing', 'confirmed', 'delivered']);
                            $step4 = in_array($st, ['confirmed', 'delivered']);
                            ?>
                            <div class="milestone-step <?= $step1 ? 'completed' : '' ?>">
                                <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                                <span class="step-label">Received</span>
                            </div>
                            <div class="milestone-line <?= $step2 ? 'active-line' : '' ?>"></div>

                            <div class="milestone-step <?= $step2 ? 'completed' : '' ?>">
                                <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                                <span class="step-label">QC Passed</span>
                            </div>
                            <div class="milestone-line <?= $step3 ? 'active-line' : '' ?>"></div>

                            <div class="milestone-step <?= $step3 ? 'completed' : '' ?>">
                                <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                                <span class="step-label">Packed</span>
                            </div>
                            <div class="milestone-line <?= $step4 ? 'active-line' : '' ?>"></div>

                            <div class="milestone-step <?= $step4 ? 'completed' : '' ?>">
                                <div class="step-icon">
                                    <?php if ($step4): ?>
                                        <i class="fa-solid fa-check"></i>
                                    <?php else: ?>
                                        <span>4</span>
                                    <?php endif; ?>
                                </div>
                                <span class="step-label">Dispatched</span>
                            </div>
                        </div>
                    </div>

                    <!-- Two Side-by-Side Info Cards -->
                    <div class="info-cards-row">
                        <div class="mini-info-card">
                            <div class="card-icon-title">
                                <i class="fa-solid fa-store text-blue"></i>
                                <strong>WHOLESALE BUYER</strong>
                            </div>
                            <p class="buyer-name-bold"><?= htmlspecialchars($selectedOrder['buyer_name']) ?></p>
                            <p class="buyer-sub">Attr: Nimal Perera (Procurement)</p>
                            <p class="buyer-phone">+94 77 456 7890</p>
                        </div>

                        <div class="mini-info-card">
                            <div class="card-icon-title">
                                <i class="fa-solid fa-location-dot text-blue"></i>
                                <strong>DELIVERY DESTINATION</strong>
                            </div>
                            <p class="address-bold"><?= htmlspecialchars($selectedOrder['delivery_address'] ?? 'Cold Freight Receiving Bay #2, Colombo 11') ?></p>
                            <p class="address-sub">Direct dock unloading required</p>
                        </div>
                    </div>

                    <!-- Harvest Cargo Breakdown -->
                    <div class="cargo-breakdown-section">
                        
                        <div class="cargo-items-list">
                            <?php if (!empty($selectedItems)): ?>
                                <?php foreach ($selectedItems as $item): ?>
                                    <div class="cargo-item-row">
                                        <div class="item-name-group">
                                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            <span class="item-qty-sub"><?= (int)$item['quantity'] ?> kg @ $<?= number_format($item['price_at_purchase'], 2) ?> / kg</span>
                                        </div>
                                        <span class="item-total-val">$<?= number_format($item['quantity'] * $item['price_at_purchase'], 2) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="cargo-item-row">
                                    <div class="item-name-group">
                                        <strong>Hydroponic Bell Peppers & Produce</strong>
                                        <span class="item-qty-sub">Wholesale Produce Batch</span>
                                    </div>
                                    <span class="item-total-val">$<?= number_format($selectedOrder['total_amount'], 2) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="cargo-summary">
                            
                            <div class="summary-line gross-total-line">
                                <strong>Gross Total</strong>
                                <strong class="total-val">$<?= number_format($selectedOrder['total_amount'], 2) ?> USD</strong>
                            </div>
                        </div>
                    </div>

                    
                <?php else: ?>
                    <div class="empty-detail-state">
                        <i class="fa-solid fa-clipboard-question"></i>
                        <h3>No Order Selected</h3>
                        <p>Select an order from the manifest list to view full logistics details.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Logout Modal -->
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

<script src="order_details.js"></script>
<?php include '../includes/footer.php'; ?>
