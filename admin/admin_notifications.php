<?php

// admin_notifications.php - Admin: Send Notifications to Stores


session_start();

// authentication 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/dbConnection.php';

$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['full_name'] ?? 'Admin';

// handle form submission - Send Notification

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $message = trim($_POST['message'] ?? '');
    $ownerId = (int)($_POST['owner_id'] ?? 0);

    if (empty($message)) {
        $errorMsg = 'Notification message cannot be empty.';
    } elseif ($ownerId <= 0) {
        $errorMsg = 'Please select a valid store.';
    } else {
        // Verify the selected user is actually an owner
        $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'owner'");
        $check->execute([$ownerId]);

        if ($check->fetch()) {
            // Insert into notifications table
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, message, is_read, created_at)
                VALUES (?, ?, 0, NOW())
            ");
            $stmt->execute([$ownerId, $message]);

            // Also log a warning in admin_warnings
            $stmt2 = $pdo->prepare("
                INSERT INTO admin_warnings (admin_id, owner_id, warning_message, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt2->execute([$adminId, $ownerId, $message]);

            $successMsg = 'Notification sent successfully!';
        } else {
            $errorMsg = 'Selected store not found.';
        }
    }
}

// fetch all greenhouse owners (with their atore name )

$stmt = $pdo->query("
    SELECT 
        u.id AS owner_id,
        u.full_name AS owner_name,
        u.city,
        s.store_name
    FROM users u
    LEFT JOIN stores s ON s.owner_id = u.id
    WHERE u.role = 'owner'
    ORDER BY u.full_name ASC
");
$owners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch recently send notifications 

$stmt2 = $pdo->query("
    SELECT 
        aw.id,
        aw.warning_message,
        aw.created_at,
        u.full_name AS owner_name,
        s.store_name
    FROM admin_warnings aw
    JOIN users u ON aw.owner_id = u.id
    LEFT JOIN stores s ON s.owner_id = u.id
    ORDER BY aw.created_at DESC
    LIMIT 10
");
$recentSent = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notifications - CropS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7f5;
            color: #1b1c1c;
            min-height: 100vh;
        }

        /* ---------- Header ---------- */
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
        .admin-header .admin-info a:hover { background: rgba(255,255,255,0.25); }

        /* ---------- Main ---------- */
        .main-wrapper {
            padding: 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #154212;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #f0f2f0;
            color: #1b1c1c;
            border: 1px solid #dce3dc;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back:hover { background: #e5e9e5; transform: translateY(-2px); }

        /* ---------- Alert Messages ---------- */
        .alert {
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert.success { background: #e8f5e9; color: #154212; border: 1px solid #a5d6a7; }
        .alert.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ---------- Form Card ---------- */
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px 32px;
            border: 1px solid #eef3ee;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            margin-bottom: 32px;
        }

        .form-title {
            font-size: 17px;
            font-weight: 700;
            color: #154212;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 22px;
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1b1c1c;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #dce3dc;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #1b1c1c;
            background: #fff;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #154212;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-send {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 34px;
            background: #154212;
            color: #fff;
            border: none;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-send:hover {
            background: #0f2f0c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 66, 18, 0.3);
        }

        /* ---------- Recent Table ---------- */
        .table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eef3ee;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px 26px 14px;
            border-bottom: 1px solid #f1f3f1;
        }

        .table-card-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #154212;
        }

        .table-card-header p {
            font-size: 13px;
            color: #6b7280;
            margin-top: 3px;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 14px 26px;
            background: #fafcfa;
            border-bottom: 1px solid #f1f3f1;
        }

        td {
            padding: 14px 26px;
            font-size: 14px;
            color: #1b1c1c;
            border-bottom: 1px solid #f1f3f1;
        }

        tr:last-child td { border-bottom: none; }

        .badge-store {
            display: inline-block;
            background: #e8f5e9;
            color: #154212;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 9999px;
        }

        .msg-preview {
            max-width: 380px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #4b5563;
        }

        .empty-row {
            text-align: center;
            color: #9ca3af;
            padding: 30px;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .main-wrapper { padding: 24px 18px; }
            .admin-header { padding: 14px 20px; }
            .form-card { padding: 22px 20px; }
            .page-title { font-size: 20px; }
            th, td { padding: 12px 16px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="admin-header">
        <h1>🌿 CropS Admin</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($adminName); ?></span>
            <a href="../logout.php">Logout</a>
        </div>
    </header>

    <div class="main-wrapper">

        <!-- Page topbar -->
        <div class="page-topbar">
            <h2 class="page-title">
                <i class="fa-solid fa-bell"></i>
                Send Notifications
            </h2>
            <a href="admin_dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Alerts -->
        <?php if ($successMsg): ?>
            <div class="alert success">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Send Notification Form -->
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-paper-plane"></i>
                New Notification
            </div>
            <p class="form-subtitle">
                Type your message, choose a store, and it will be delivered to their dashboard.
            </p>

            <form method="POST" action="">
                <!-- Message -->
                <div class="form-group">
                    <label for="message">Notification Message</label>
                    <textarea 
                        name="message" 
                        id="message" 
                        placeholder="e.g. Your GAP certificate is about to expire. Please upload a renewed certificate within 7 days." 
                        required></textarea>
                </div>

                <!-- Store Select -->
                <div class="form-group">
                    <label for="owner_id">Send To (Store)</label>
                    <select name="owner_id" id="owner_id" required>
                        <option value="">-- Select a store --</option>
                        <?php foreach ($owners as $o): ?>
                            <option value="<?php echo $o['owner_id']; ?>">
                                <?php
                                    $label = $o['store_name'] ?: $o['owner_name'];
                                    echo htmlspecialchars($label . ' — ' . $o['owner_name'] . ' (' . $o['city'] . ')');
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="send_notification" class="btn-send">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Notification
                </button>
            </form>
        </div>

        <!-- Recently Sent -->
        <div class="table-card">
            <div class="table-card-header">
                <h3>Recently Sent Notifications</h3>
                <p>Last 10 notifications sent to store owners</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Owner</th>
                        <th>Message</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentSent) > 0): ?>
                        <?php foreach ($recentSent as $row): ?>
                            <tr>
                                <td>
                                    <span class="badge-store">
                                        <?php echo htmlspecialchars($row['store_name'] ?: $row['owner_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                                <td class="msg-preview" title="<?php echo htmlspecialchars($row['warning_message']); ?>">
                                    <?php echo htmlspecialchars($row['warning_message']); ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-row">
                                No notifications sent yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>