<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - CropS</title>
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
            background: #fee2e2;
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
            color: #991b1b;
            margin-bottom: 8px;
        }

        .message {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
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
    </style>
</head>
<body>

    <div class="popup">
        <div class="icon-circle">❌</div>
        <h1 class="logo">🌿 Crops</h1>
        <h2>Payment Failed</h2>
        <p class="message">
            Your payment could not be processed.<br>
            Please try again or contact support.
        </p>

        <a href="payment_summary.php" class="btn">Try Again</a>
        <a href="members.php" class="back-link">← Back to Membership</a>
    </div>

</body>
</html>