<?php

// payment_summary.php - Payment Summary Page

session_start();

// redirect if not logged in 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// get user info 

$user_id = (int)$_SESSION['user_id'];
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Owner';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Summary - CropS</title>

    <!-- PayHere Script -->
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>

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

        .top-bar { display: flex; justify-content: flex-end; margin-bottom: 20px; }

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
        .close-btn:hover { color: #1a2e1f; transform: rotate(90deg); }

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

        .summary-box {
            background: #f8faf8;
            border-radius: 16px;
            padding: 22px 24px;
            margin-bottom: 28px;
            border: 1px solid #eef3ee;
        }

        .summary-box h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eef3ee;
            font-size: 14px;
            color: #4b5563;
        }
        .summary-row:last-of-type { border-bottom: none; }
        .summary-row .value { font-weight: 600; color: #1a2e1f; }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 14px 0 4px 0;
            border-top: 2px solid #dce8dc;
            margin-top: 6px;
        }
        .summary-total .label { font-size: 16px; font-weight: 700; color: #1a2e1f; }
        .summary-total .value { font-size: 18px; font-weight: 800; color: #1a5f2a; }

        .btn-proceed {
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
        }
        .btn-proceed:hover {
            background: #0f4a1e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 95, 42, 0.3);
        }
        .btn-proceed:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #6b7280;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #1a5f2a; }

        .error-msg {
            color: #dc2626;
            font-size: 14px;
            margin-top: 12px;
            display: none;
            text-align: center;
        }
        .error-msg.show { display: block; }
    </style>
</head>
<body>

    <div class="popup">
        <div class="top-bar">
            <button class="close-btn" onclick="window.location.href='members.php'">&times;</button>
        </div>

        <h1 class="logo">🌿 Crops</h1>
        <h2 class="page-title">Payment Summary</h2>
        <p class="page-subtitle">Complete your CropS membership securely.</p>

        <div class="summary-box">
            <h3>Summary</h3>
            <div class="summary-row">
                <span>CropS Membership for 1 year</span>
                <span class="value">LKR 999.00</span>
            </div>
            <div class="summary-row">
                <span>Setup Fee</span>
                <span class="value">LKR 0.00</span>
            </div>
            <div class="summary-total">
                <span class="label">Total</span>
                <span class="value">LKR 999.00</span>
            </div>
        </div>

        <button class="btn-proceed" id="payBtn" onclick="proceedToPayment()">
            Proceed to Payment →
        </button>

        <div id="errorMsg" class="error-msg"></div>

        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <script>

        // proceed to payment 

        function proceedToPayment() {
            const payBtn = document.getElementById('payBtn');
            const errorMsg = document.getElementById('errorMsg');

            // Check if PayHere is loaded

            if (typeof payhere === 'undefined') {
                errorMsg.textContent = '⚠️ PayHere is still loading. Please wait a moment and try again.';
                errorMsg.classList.add('show');
                return;
            }

            payBtn.disabled = true;
            payBtn.textContent = 'Redirecting to PayHere...';
            errorMsg.classList.remove('show');

            // get payment hash from backend 

            fetch('api/payment/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    amount: 999.00
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Payment initiation failed');
                }

                // configure payhere payment 

                const payment = {
                    sandbox: data.sandbox,
                    merchant_id: data.merchant_id,
                    return_url: 'http://localhost/Smart_Greenhouse_Products_Marketplace/payment_success.php',
                    cancel_url: 'http://localhost/Smart_Greenhouse_Products_Marketplace/payment_failed.php',
                    order_id: data.order_id,
                    items: 'CropS Annual Membership',
                    amount: data.amount,
                    currency: data.currency,
                    hash: data.hash,
                    first_name: '<?php echo htmlspecialchars($user_name); ?>',
                    last_name: '',
                    email: '',
                    phone: '',
                    address: '',
                    city: '',
                    country: 'Sri Lanka',
                };

                // payhere event handlers 

                window.payhere.onCompleted = function(orderId) {
                    window.location.href = 'payment_success.php?order=' + orderId;
                };

                window.payhere.onDismissed = function() {
                    payBtn.disabled = false;
                    payBtn.textContent = 'Proceed to Payment →';
                    errorMsg.textContent = 'Payment was cancelled.';
                    errorMsg.classList.add('show');
                };

                window.payhere.onError = function(error) {
                    payBtn.disabled = false;
                    payBtn.textContent = 'Proceed to Payment →';
                    errorMsg.textContent = 'Payment failed. Please try again.';
                    errorMsg.classList.add('show');
                };

                // start payment 
                
                window.payhere.startPayment(payment);
            })
            .catch(error => {
                console.error('Error:', error);
                errorMsg.textContent = '⚠️ ' + error.message;
                errorMsg.classList.add('show');
                payBtn.disabled = false;
                payBtn.textContent = 'Proceed to Payment →';
            });
        }
    </script>

</body>
</html>