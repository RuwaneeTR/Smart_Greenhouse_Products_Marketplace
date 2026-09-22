<?php

// members.php - Membership Page
// Shows membership status for the LOGGED-IN user

session_start();

// redirect if not logged in 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// get user ID from session 

$user_id = (int)$_SESSION['user_id'];
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Owner';

// connect to database 

include 'includes/dbConnection.php';

// check membership status for this user 

$membership_status = false;
$expiry_date = null;

try {
    $stmt = $pdo->prepare("
        SELECT status, expiry_date 
        FROM memberships 
        WHERE user_id = ? 
          AND status = 'active' 
          AND expiry_date > NOW()
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $membership = $stmt->fetch();

    if ($membership) {
        $membership_status = true;
        $expiry_date = date('M d, Y', strtotime($membership['expiry_date']));
    }
} catch (PDOException $e) {
    $membership_status = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership - CropS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .popup {
            background: #ffffff;
            max-width: 520px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px 35px;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            position: relative;
            animation: fadeSlide 0.4s ease;
        }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .popup::-webkit-scrollbar { width: 6px; }
        .popup::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .popup::-webkit-scrollbar-thumb { background: #c8e6c9; border-radius: 10px; }
        .popup::-webkit-scrollbar-thumb:hover { background: #a5d6a7; }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .close-btn {
            font-size: 26px;
            color: #ccc;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.25s, transform 0.2s;
            padding: 4px 8px;
            line-height: 1;
        }
        .close-btn:hover {
            color: #1a2e1f;
            transform: rotate(90deg);
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #1a5f2a;
            text-align: center;
            margin-bottom: 4px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a2e1f;
            text-align: center;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 28px;
        }

        .membership-card {
            background: #f8faf8;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #eef3ee;
            text-align: center;
            margin-bottom: 22px;
            transition: border-color 0.25s;
        }

        .membership-card:hover { border-color: #c8e6c9; }

        .membership-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        .membership-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 4px;
        }

        .membership-desc {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .membership-price {
            font-size: 28px;
            font-weight: 800;
            color: #1a5f2a;
            display: block;
            margin-bottom: 4px;
        }

        .membership-duration {
            font-size: 13px;
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
        }

        .status-badge.active {
            background: #e8f5e9;
            color: #1a5f2a;
            border: 1px solid #a5d6a7;
        }

        .status-badge.inactive {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .expiry-info {
            font-size: 13px;
            color: #6b7280;
            margin-top: 8px;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 16px 0 22px 0;
            text-align: left;
        }

        .features-list li {
            padding: 8px 0;
            font-size: 14px;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f3f1;
        }

        .features-list li:last-child { border-bottom: none; }

        .features-list li::before {
            content: "✓";
            color: #1a5f2a;
            font-weight: 700;
            font-size: 16px;
        }

        .btn-pay {
            width: 100%;
            padding: 14px;
            background: #1a5f2a;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.25s, transform 0.1s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-pay:hover {
            background: #0f4a1e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 95, 42, 0.3);
        }

        .btn-pay:active { transform: scale(0.98); }
        .btn-pay:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            width: 100%;
            padding: 12px;
            background: #f1f3f1;
            color: #4b5563;
            border: 1px solid #d1d5d1;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.25s;
            font-family: inherit;
            margin-top: 10px;
        }

        .btn-secondary:hover { background: #e5e7e5; }

        @media (max-width: 520px) {
            .popup { padding: 28px 20px; border-radius: 18px; }
            .logo { font-size: 24px; }
            .page-title { font-size: 17px; }
            .membership-price { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="popup">

        <div class="top-bar">
            <button class="close-btn" onclick="window.location.href='index.php'">&times;</button>
        </div>

        <h1 class="logo">🌿 Crops</h1>
        <h2 class="page-title">Membership</h2>
        <p class="page-subtitle">
            Welcome, <?php echo htmlspecialchars($user_name); ?>!<br>
            Unlock premium features and grow your greenhouse business.
        </p>

        <div class="membership-card">
            <span class="membership-icon">🏷️</span>
            <h3 class="membership-name">Annual Membership</h3>
            <p class="membership-desc">Get access to all premium features for one full year.</p>
            <span class="membership-price">LKR 999.00</span>
            <span class="membership-duration">Valid for 1 year from purchase</span>

            <?php if ($membership_status): ?>
                <span class="status-badge active">✅ Active</span>
                <?php if ($expiry_date): ?>
                    <p class="expiry-info">Expires on: <?php echo $expiry_date; ?></p>
                <?php endif; ?>
            <?php else: ?>
                <span class="status-badge inactive">⏳ Not Active</span>
            <?php endif; ?>
        </div>

        <ul class="features-list">
            <li>List unlimited products in your store</li>
            <li>Access to customer reviews and analytics</li>
            <li>Priority customer support</li>
            <li>Featured store placement</li>
        </ul>

        <?php if ($membership_status): ?>
            <button class="btn-pay" disabled>
                ✅ Membership Active
            </button>
            <button class="btn-secondary" onclick="window.location.href='payment_summary.php'">
                Renew Membership
            </button>
        <?php else: ?>
            <button class="btn-pay" onclick="window.location.href='payment_summary.php'">
                💳 Pay Membership Fee
            </button>
        <?php endif; ?>

    </div>

</body>
</html>