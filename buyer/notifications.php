<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT id, title, priority, category, icon, message,
           action_label, action_link, action_label_2, action_link_2,
           is_read, created_at
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$allNotifications = $stmt->fetchAll();

$unreadCount = count(array_filter($allNotifications, fn($n) => !$n['is_read']));

$stmt = $pdo->prepare("SELECT full_name, city FROM users WHERE id = ?");
$stmt->execute([$userId]);
$buyer = $stmt->fetch();

$nameParts = explode(' ', $buyer['full_name']);
$initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

include '../includes/header.php';
?>

<link rel="stylesheet" href="notifications.css">

  
  <div class="app-layout">
    <aside class="sidebar">
      <div class="profile-card">
        <div class="avatar-large-wrapper">
          <div class="avatar-circle-lg"><?= htmlspecialchars($initials) ?></div>
          <span class="online-indicator"></span>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($buyer['full_name']) ?></h3>
        <p class="profile-subtitle">Verified Buyer • <?= htmlspecialchars($buyer['city']) ?></p>
      </div>

      <nav class="sidebar-nav">
        <a href="customer_dashboard.php" class="sidebar-link">
          <i class="fas fa-home"></i>
          <span>Home(Dashboard)</span>
        </a>
        <a href="notifications.php" class="sidebar-link active">
          <i class="far fa-bell"></i>
          <span>Notifications</span>
          <span class="nav-badge badge-unread" id="sidebarUnreadBadge"><?= $unreadCount ?></span>
          <span class="active-dot"></span>
        </a>
        <a href="edit_profile.php" class="sidebar-link">
          <i class="far fa-user-circle"></i>
          <span>Edit Profile</span>
        </a>
      </nav>
    </aside>

    <main class="main-content">
      <div class="notifications-page-card">

        <div class="page-title-header">
          <div class="title-with-badge">
            <h2>Notifications</h2>
            <span class="unread-pill-badge" id="unreadBadgeHeader"><?= $unreadCount ?> unread</span>
          </div>
        </div>

        <p class="notifications-subtitle">
          Alerts on your local farm produce orders, harvest updates, and seasonal tips.
        </p>

        <div class="filter-tabs" id="filterTabs">
          <button class="tab-pill active" data-filter="all">All (<?= count($allNotifications) ?>)</button>
          <button class="tab-pill" data-filter="unread">Unread (<?= $unreadCount ?>)</button>
        </div>

        <div class="notifications-list" id="notificationsList"></div>

        <div class="notifications-footer-summary">
          <span id="showingCountText">Showing <?= count($allNotifications) ?> of <?= count($allNotifications) ?> notifications</span>
        </div>

      </div>
    </main>
  </div>


  <script>
    window.CROPS_NOTIFICATIONS = <?= json_encode($allNotifications) ?>;
    window.CROPS_UNREAD = <?= $unreadCount ?>;
  </script>
  <script src="notifications.js"></script>

  <?php include '../includes/footer.php'; ?>
