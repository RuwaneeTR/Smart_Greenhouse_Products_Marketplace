<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch owner for sidebar
$stmt = $pdo->prepare("SELECT full_name, city FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Store Owner';

// Compute initials (used in sidebar)
$nameParts = array_values(array_filter(explode(' ', trim($fullName))));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
} elseif (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
} else {
    $initials = 'TO';
}

// Handle "mark all as read"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    $stmtMark = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmtMark->execute([$userId]);
    header('Location: notifications.php');
    exit;
}

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCount  = count($notifications);
$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

// Category counts
$catCounts = ['orders'=>0, 'compliance'=>0, 'stock'=>0, 'settlement'=>0, 'broadcast'=>0, 'review'=>0];
foreach ($notifications as $n) {
    if (isset($catCounts[$n['category']])) $catCounts[$n['category']]++;
}

// Pagination — 8 per page
$perPage    = 8;
$page       = max(1, (int)($_GET['page'] ?? 1));
$totalPages = max(1, (int)ceil($totalCount / $perPage));
$offset     = ($page - 1) * $perPage;
$paged      = array_slice($notifications, $offset, $perPage);

// Helper: relative time
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)        return ($diff > 0 ? $diff : 1) . ' sec ago';
    if ($diff < 3600)      return floor($diff / 60) . ' min ago';
    if ($diff < 86400)     return floor($diff / 3600) . ' hrs ago';
    if ($diff < 172800)    return 'Yesterday';
    return date('M d, Y', strtotime($datetime));
}

// Helper: priority icon color class
function priorityClass($priority) {
    return match($priority) {
        'high'    => 'priority-high',
        'info'    => 'priority-info',
        'success' => 'priority-success',
        default   => 'priority-normal',
    };
}

include '../includes/header.php';
?>
<!-- Standalone Reusable Order Confirmation Modal Component -->
<?php include 'order_modal.php'; ?>

<link rel="stylesheet" href="notifications.css">

<!-- Overlay backdrop for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <!-- Left Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <!-- Profile Mini-Card at Top -->
        <div class="sidebar-user-mini">
            <div class="mini-avatar-circle">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div class="mini-user-info">
                <h4 class="mini-name"><?= htmlspecialchars($fullName) ?></h4>
                <span class="mini-role">Store Owner</span>
            </div>
        </div>

        <!-- Sidebar Menu Items -->
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
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/order_details.php" class="sidebar-link">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Order Details</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/notifications.php" class="sidebar-link active">
                        <i class="fa-solid fa-bell"></i>
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="nav-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
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
        <!-- Top Toolbar Row -->
        <div class="top-toolbar-row">
            <div class="toolbar-actions">
                <form id="markAllForm" method="POST" action="notifications.php" style="display: inline;">
                    <input type="hidden" name="mark_all" value="1">
                </form>
            </div>
        </div>

        <!-- Hero Banner Container -->
        <div class="notifications-hero-banner">
            <div class="hero-left">
                <div class="live-feed-tag">
                    <span class="tag-pill">LIVE FEED</span>
                    <span class="tag-sub">Telemetry & Store Hub</span>
                </div>
                <div class="header-title-row">
                    <h1 class="page-title">Notifications</h1>
                    <?php if ($unreadCount > 0): ?>
                        <span class="unread-pill"><?= $unreadCount ?> UNREAD</span>
                    <?php endif; ?>
                </div>
                <p class="hero-subtitle">
                    Stay updated on wholesale crop orders, GAP compliance status, store alerts, and system announcements across your greenhouse facilities.
                </p>
            </div>

            <!-- Hardcoded SYNC ACTIVE Status Widget -->
            <div class="sync-widget">
                <div class="sync-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs-row">
            <button type="button" class="filter-tab active" data-filter="all">
                <span>All</span>
                <span class="tab-count"><?= $totalCount ?></span>
            </button>
            <button type="button" class="filter-tab" data-filter="unread">
                <span>Unread</span>
                <span class="tab-count"><?= $unreadCount ?></span>
            </button>
        </div>

        <!-- Notifications Card List -->
        <div class="notifications-list" id="notificationsList">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-bell-slash"></i>
                    <h3>No notifications yet</h3>
                    <p>Activity across your store will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($paged as $n): ?>
                    <div class="notif-card <?= !$n['is_read'] ? 'unread-card' : '' ?>" 
                         data-category="<?= htmlspecialchars($n['category'] ?? 'orders') ?>" 
                         data-read="<?= $n['is_read'] ? '1' : '0' ?>">
                        
                        <div class="notif-icon <?= priorityClass($n['priority'] ?? 'normal') ?>">
                            <i class="fa-solid <?= htmlspecialchars($n['icon'] ?? 'fa-bell') ?>"></i>
                        </div>

                        <div class="notif-content">
                            <div class="notif-meta-row">
                                <?php if (!$n['is_read']): ?>
                                    <span class="unread-indicator-dot" title="Unread"></span>
                                <?php endif; ?>

                                <?php
                                $priority = strtolower($n['priority'] ?? 'normal');
                                $priorityBadgeClass = match($priority) {
                                    'high' => 'badge-priority-high',
                                    'info' => 'badge-priority-info',
                                    'success' => 'badge-priority-success',
                                    default => 'badge-priority-normal',
                                };
                                ?>
                                <span class="priority-badge <?= $priorityBadgeClass ?>">
                                    <?= htmlspecialchars(strtoupper($priority)) ?>
                                </span>

                                <span class="meta-details">
                                    <?= htmlspecialchars(strtoupper($n['category'] ?? 'GENERAL')) ?> · <?= timeAgo($n['created_at']) ?>
                                </span>
                            </div>

                            <h3 class="notif-title"><?= htmlspecialchars($n['title']) ?></h3>
                            <p class="notif-message"><?= htmlspecialchars($n['message']) ?></p>

                            <?php if (!empty($n['action_label']) || !empty($n['action_label_2'])): ?>
                                <div class="notif-actions">
                                    <?php if (!empty($n['action_label'])): ?>
                                        <a href="<?= htmlspecialchars($n['action_link'] ?? '#') ?>" class="btn-action-primary">
                                            <?= htmlspecialchars($n['action_label']) ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($n['action_label_2'])): ?>
                                        <a href="<?= htmlspecialchars($n['action_link_2'] ?? '#') ?>" class="btn-action-outline">
                                            <?= htmlspecialchars($n['action_label_2']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer / Pagination -->
        <?php if ($totalCount > 0): ?>
            <div class="pagination-footer">
                <span class="pagination-info">
                    Showing <?= count($paged) ?> of <?= $totalCount ?> notifications · Page <?= $page ?> of <?= $totalPages ?>
                </span>
                <div class="pagination-buttons">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="btn-page"><i class="fa-solid fa-chevron-left"></i> Previous</a>
                    <?php else: ?>
                        <span class="btn-page disabled"><i class="fa-solid fa-chevron-left"></i> Previous</span>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?page=<?= $p ?>" class="btn-page-num <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="btn-page">Next <i class="fa-solid fa-chevron-right"></i></a>
                    <?php else: ?>
                        <span class="btn-page disabled">Next <i class="fa-solid fa-chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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

<script src="notifications.js"></script>
<?php include '../includes/footer.php'; ?>
