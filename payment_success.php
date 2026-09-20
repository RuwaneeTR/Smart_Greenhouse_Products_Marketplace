<?php

// payment_success.php - Payment Success Page
// Updates membership for the LOGGED-IN user only

session_start();

// redirect if not logged in 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// get user ID from session 

$user_id = (int)$_SESSION['user_id'];

// connect to the database

include 'includes/dbConnection.php';

// get order ID from url 

$order_id = isset($_GET['order']) ? trim($_GET['order']) : '';

// update membership status for this user 

$update_success = false;

if (!empty($order_id)) {
    try {

        // Verify this transaction belongs to the logged-in user
        
        $verify = $pdo->prepare("
            SELECT user_id 
            FROM payment_transactions 
            WHERE order_id = ?
        ");
        $verify->execute([$order_id]);
        $txn = $verify->fetch();

        // Only proceed if transaction exists AND belongs to this user

        if ($txn && (int)$txn['user_id'] === $user_id) {

            // Mark the transaction as completed
           
            $sql = "UPDATE payment_transactions 
                    SET status = 'completed' 
                    WHERE order_id = ?";
            $pdo->prepare($sql)->execute([$order_id]);

            // Insert or update membership for this user

            $check = $pdo->prepare("
                SELECT id 
                FROM memberships 
                WHERE user_id = ?
            ");
            $check->execute([$user_id]);
            $exists = $check->fetch();

            if ($exists) {
                // Update existing membership
                $sql2 = "UPDATE memberships 
                         SET status = 'active',
                             start_date = NOW(),
                             expiry_date = DATE_ADD(NOW(), INTERVAL 1 YEAR),
                             order_id = ?,
                             updated_at = NOW()
                         WHERE user_id = ?";
                $pdo->prepare($sql2)->execute([$order_id, $user_id]);
            } else {
                // Create new membership
                $sql2 = "INSERT INTO memberships 
                         (user_id, plan_name, amount, currency, status, order_id, start_date, expiry_date) 
                         VALUES (?, 'Annual Plan', 999.00, 'LKR', 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))";
                $pdo->prepare($sql2)->execute([$user_id, $order_id]);
            }

            $update_success = true;
        }
    } catch (PDOException $e) {
        // Log the error silently
        error_log("payment_success error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - CropS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
            max-width: 480px;
            width: 100%;
            padding: 45px 35px;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            text-align: center;
            animation: fadeSlide 0.4s ease;
        }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 48px;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #1a5f2a;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 8px;
        }

        .message {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .order-box {
            background: #f8faf8;
            border: 1px solid #eef3ee;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 24px;
            text-align: left;
        }

        .order-box .label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .order-box .value {
            font-size: 13px;
            color: #1a2e1f;
            word-break: break-all;
            font-family: monospace;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #1a5f2a;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.25s, transform 0.1s;
            font-family: inherit;
        }
        .btn:hover {
            background: #0f4a1e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 95, 42, 0.3);
        }

        .back-link {
            display: block;
            margin-top: 16px;
            color: #6b7280;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #1a5f2a; }

        @media (max-width: 520px) {
            .popup { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <div class="popup">
        <div class="icon-circle">✅</div>
        <h1 class="logo">🌿 Crops</h1>
        <h2>Payment Successful!</h2>
        <p class="message">
            Thank you for your payment.<br>
            Your CropS membership is now active for 1 year.
        </p>

        <?php if ($order_id): ?>
        <div class="order-box">
            <div class="label">Order Reference</div>
            <div class="value"><?php echo htmlspecialchars($order_id); ?></div>
        </div>
        <?php endif; ?>

        <a href="members.php" class="btn">Go to Membership</a>
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

</body>
</html>