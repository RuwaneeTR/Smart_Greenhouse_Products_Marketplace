<?php

// terms.php - Terms & Conditions Page

session_start();

// optional: show user's name if logged in, otherwise treat as guest

$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms &amp; Conditions - CropS</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f7f4;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 40px 20px 60px 20px;
            color: #1a2e1f;
        }

        .container {
            background: #ffffff;
            max-width: 760px;
            width: 100%;
            padding: 45px 50px 50px 50px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            animation: fadeSlide 0.4s ease;
        }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #1a5f2a;
            text-align: center;
            margin-bottom: 4px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a2e1f;
            text-align: center;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 34px;
        }

        .intro {
            font-size: 14.5px;
            line-height: 1.7;
            color: #4b5563;
            background: #f8faf8;
            border: 1px solid #eef3ee;
            border-left: 4px solid #1a5f2a;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 32px;
        }

        h2.section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a5f2a;
            margin: 30px 0 14px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #dce8dc;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        h3.sub-title {
            font-size: 14px;
            font-weight: 700;
            color: #1a2e1f;
            margin: 20px 0 10px 0;
        }

        ul.terms-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul.terms-list li {
            position: relative;
            padding: 9px 0 9px 26px;
            font-size: 14px;
            line-height: 1.65;
            color: #4b5563;
            border-bottom: 1px solid #f1f5f1;
        }
        ul.terms-list li:last-child { border-bottom: none; }

        ul.terms-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 9px;
            color: #1a5f2a;
            font-weight: 700;
            font-size: 13px;
        }

        .highlight-box {
            background: #f8faf8;
            border: 1px solid #eef3ee;
            border-radius: 12px;
            padding: 14px 20px;
            margin: 18px 0;
            font-size: 14px;
            color: #4b5563;
            line-height: 1.65;
        }

        .highlight-box strong { color: #1a2e1f; }

        .fee-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #1a5f2a;
            font-weight: 700;
            font-size: 13px;
            padding: 3px 10px;
            border-radius: 8px;
            margin-left: 4px;
        }

        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 40px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-family: inherit;
            border: none;
            transition: background 0.25s, transform 0.1s, box-shadow 0.25s;
        }

        .btn-primary {
            background: #1a5f2a;
            color: #fff;
        }
        .btn-primary:hover {
            background: #0f4a1e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 95, 42, 0.3);
        }

        .btn-secondary {
            background: #ffffff;
            color: #1a2e1f;
            border: 1.5px solid #dce8dc;
        }
        .btn-secondary:hover {
            background: #f4f7f4;
            transform: translateY(-2px);
        }

        .footer-note {
            text-align: center;
            font-size: 12.5px;
            color: #9ca3af;
            margin-top: 26px;
        }

        @media (max-width: 560px) {
            .container { padding: 32px 24px 36px 24px; }
            .btn-row { flex-direction: column; }
        }
    </style>
</head>
<body>

    <div class="container">

        <h1 class="logo">🌿 Crops</h1>
        <h2 class="page-title">Terms &amp; Conditions</h2>
        <p class="page-subtitle">Please read these terms carefully before using the CropS platform.</p>

        <div class="intro">
            Welcome to <strong>CropS</strong>. These Terms &amp; Conditions govern your access to and use of the CropS platform.
            CropS is a <strong>marketplace</strong> and is not directly responsible for delivery logistics.
            By registering an account or using the Platform, you agree to be bound by these Terms.
        </div>

        <!-- GENERAL ACCOUNT TERMS -->
        <h2 class="section-title">👤 General Account Terms</h2>
        <ul class="terms-list">
            <li>Provide accurate, current, and complete information during registration.</li>
            <li>Maintain the security of your account credentials.</li>
        </ul>

        <!-- GREENHOUSE OWNER -->
        <h2 class="section-title">🌱 As a Greenhouse Owner, you agree to:</h2>
        <ul class="terms-list">
            <li>Upload a valid Good Agricultural Practices (GAP) certificate during registration.</li>
            <li>Ensure all product listings are accurate, including descriptions, prices, and available quantities.</li>
            <li>Maintain product quality and freshness at the time of delivery.</li>
            <li>Update stock levels promptly to avoid overselling.</li>
            <li>Fulfil orders in a timely manner and update the delivery status.</li>
            <li>Pay the annual membership fee of <strong>LKR 999.00</strong><span class="fee-badge">Annual</span> to keep your store active.</li>
            <li>Comply with all health and safety standards applicable to agricultural produce.</li>
        </ul>

        <!-- MEMBERSHIP -->
        <h2 class="section-title">💳 Membership</h2>
        <ul class="terms-list">
            <li>Membership is valid for <strong>1 year</strong> from the date of payment.</li>
            <li>Membership fees are <strong>non-refundable</strong> once paid.</li>
            <li>We reserve the right to change membership fees with prior notice.</li>
        </ul>

        <!-- CUSTOMER -->
        <h2 class="section-title">🛒 As a Customer, you agree to:</h2>
        <ul class="terms-list">
            <li>Provide accurate delivery information at checkout.</li>
            <li>Not misuse the review and rating system.</li>
        </ul>

        <!-- MARKETPLACE DISCLAIMER -->
        <div class="highlight-box">
            <strong>Marketplace Disclaimer:</strong> CropS is a marketplace and is not directly responsible for delivery logistics.
            Delivery of products is handled by the respective Greenhouse Owners or their designated couriers.
        </div>

        <div class="btn-row">
            <a href="index.php" class="btn btn-secondary">← Back to Home</a>
            <?php if (!$user_name): ?>
                <a href="javascript:history.back()" class="btn btn-primary">I Agree & Continue →</a>
            <?php else: ?>
                <a href="members.php" class="btn btn-primary">Go to Membership →</a>
            <?php endif; ?>
        </div>

        <p class="footer-note">
            Last updated: <?php echo date('F Y'); ?>
        </p>

    </div>

</body>
</html>