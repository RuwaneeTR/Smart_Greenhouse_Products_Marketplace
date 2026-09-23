<?php

// admin_dashboard.php - Admin Dashboard

session_start();

// authentication - Only admins can access

/*if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}*/

require_once '../includes/dbConnection.php';

// fetch statistics from database

// 1. Total Users
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// 2. Total Greenhouse Owners
$totalOwners = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();

// 3. Active Orders (not yet delivered)
$activeOrders = (int)$pdo->query("
    SELECT COUNT(*) FROM orders 
    WHERE status IN ('pending', 'confirmed', 'processing')
")->fetchColumn();

// 4. Total Reviews
$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// Admin info
$adminName = $_SESSION['full_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CropS</title>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7f5;
            color: #1b1c1c;
            min-height: 100vh;
        }

        .admin-header {
            background: #154212;
            color: #fff;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .admin-header h1 {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-header .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
        }

        .admin-header .admin-info a {
            color: #fff;
            text-decoration: none;
            padding: 6px 14px;
            background: rgba(255,255,255,0.15);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }

        .admin-header .admin-info a:hover {
            background: rgba(255,255,255,0.25);
        }

        .main-wrapper {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #154212;
            margin-bottom: 28px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 26px 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border: 1px solid #eef3ee;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .stat-number {
            font-size: 34px;
            font-weight: 800;
            color: #1b1c1c;
            line-height: 1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* Icon background colors */
        .icon-users    { background: #e8f5e9; color: #154212; }
        .icon-owners   { background: #e8f5e9; color: #154212; }
        .icon-orders   { background: #e8f5e9; color: #154212; }
        .icon-reviews  { background: #fef2f2; color: #dc2626; }


        .actions-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        /* Primary button - GAP Certificates */
        .action-btn.primary {
            background: #154212;
            color: #ffffff;
        }
        .action-btn.primary:hover {
            background: #0f2f0c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 66, 18, 0.3);
        }

        /* Secondary buttons - Reviews, Notifications */
        .action-btn.secondary {
            background: #f0f2f0;
            color: #1b1c1c;
            border: 1px solid #dce3dc;
        }
        .action-btn.secondary:hover {
            background: #e5e9e5;
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 16px;
        }

        @media (max-width: 900px) {
            .main-wrapper { padding: 24px 20px; }
            .stats-grid { grid-template-columns: 1fr; }
            .admin-header { padding: 14px 20px; }
            .page-title { font-size: 22px; }
            .stat-number { font-size: 28px; }
        }

        @media (max-width: 520px) {
            .actions-row { flex-direction: column; }
            .action-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>🌿 CropS Admin</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($adminName); ?></span>
            <a href="../logout.php">Logout</a>
        </div>
    </header>

    <div class="main-wrapper">

        <h2 class="page-title">Admin Dashboard</h2>

        <div class="stats-grid">

            <!-- Card 1: Total Users -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Users</span>
                    <span class="stat-number"><?php echo number_format($totalUsers); ?></span>
                </div>
                <div class="stat-icon icon-users">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <!-- Card 2: Greenhouse Owners -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Greenhouse Owners</span>
                    <span class="stat-number"><?php echo number_format($totalOwners); ?></span>
                </div>
                <div class="stat-icon icon-owners">
                    <i class="fa-solid fa-tractor"></i>
                </div>
            </div>

            <!-- Card 3: Active Orders -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Active Orders</span>
                    <span class="stat-number"><?php echo number_format($activeOrders); ?></span>
                </div>
                <div class="stat-icon icon-orders">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>

            <!-- Card 4: Reviews -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Reviews</span>
                    <span class="stat-number"><?php echo number_format($totalReviews); ?></span>
                </div>
                <div class="stat-icon icon-reviews">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>

        </div>

        
        <div class="actions-row">

            <!-- GAP Certificates → gap.php -->
            <a href="gap.php" class="action-btn primary">
                <i class="fa-solid fa-certificate"></i>
                GAP Certificates
            </a>

            <!-- Store Reviews → stores.php -->
            <a href="../stores.php" class="action-btn secondary">
                <i class="fa-regular fa-star"></i>
                Store Reviews
            </a>

            <!-- Notifications → admin_notifications.php -->
            <a href="admin_notifications.php" class="action-btn secondary">
                <i class="fa-regular fa-bell"></i>
                Notifications
            </a>

        </div>

    </div>

</body>
</html>
