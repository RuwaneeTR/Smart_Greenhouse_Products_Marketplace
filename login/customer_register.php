<?php
session_start();
require_once '../includes/dbConnection.php'; // adjust path

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');        // nearest city
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($password) || empty($city) || empty($address)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Check username uniqueness if provided
            if (!empty($username)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already taken.';
                }
            }

            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, city, address, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'customer', NOW())");
                $stmt->execute([$full_name, $username, $email, $phone, $city, $address, $hashed_password]);

                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_role'] = 'customer';
                $_SESSION['user_name'] = $full_name;
                $_SESSION['customer_id'] = $user_id;

                header('Location: home.php');
                exit;
            }
        }
    }
}
include '../includes/header.php';
?>

<!-- Login page specific styles (loaded after shared header) -->
<link rel="stylesheet" href="role.css">

    <main class="page-container">
        <div class="auth-card active-card" id="reg-auth-card">
            <div class="card-left-panel">
                <div class="image-overlay"></div>
                <div class="panel-bottom-text">
                    <h1 class="hero-title">Grow your network.</h1>
                    <p class="hero-subtitle">Join the premier marketplace connecting commercial</p>
                </div>
            </div>

            <div class="card-right-panel">
                <div class="panel-main-content">
                    <div class="view-content active" id="customer-form-view">
                        <div class="form-header-area">
                            <h2 class="form-title">Create an account</h2>
                            <div class="role-badge">
                                <span class="badge-dot">•</span>
                                <span class="badge-text">REGISTERING AS CUSTOMER</span>
                            </div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div style="background: #FEE2E2; color: #B91C1C; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px;">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form class="registration-form" id="customer-reg-form" method="post" action="customer_register.php">
                            <div class="form-row-2col">
                                <div class="input-field-wrapper">
                                    <label for="cust-fullname">Full Name</label>
                                    <div class="input-box">
                                        <input type="text" id="cust-fullname" name="full_name" placeholder="Jane Doe" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                </div>
                                <div class="input-field-wrapper">
                                    <label for="cust-username">Username</label>
                                    <div class="input-box">
                                        <input type="text" id="cust-username" name="username" placeholder="janedoe88" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="12" r="4"></circle>
                                            <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-1col">
                                <div class="input-field-wrapper">
                                    <label for="cust-email">Email Address</label>
                                    <div class="input-box">
                                        <input type="email" id="cust-email" name="email" placeholder="jane@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-2col">
                                <div class="input-field-wrapper">
                                    <label for="cust-phone">Telephone Number</label>
                                    <div class="input-box">
                                        <input type="tel" id="cust-phone" name="phone" placeholder="+1 (555) 000-0000" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="input-field-wrapper">
                                    <label for="cust-city">Nearest City</label>
                                    <div class="input-box">
                                        <input type="text" id="cust-city" name="city" placeholder="Seattle, WA" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                            <path d="M9 22v-4h6v4"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-1col">
                                <div class="input-field-wrapper">
                                    <label for="cust-address">Physical Address</label>
                                    <div class="input-box">
                                        <input type="text" id="cust-address" name="address" placeholder="123 Botanica Lane, Suite 4" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-2col">
                                <div class="input-field-wrapper">
                                    <label for="cust-password">Password</label>
                                    <div class="input-box">
                                        <input type="password" id="cust-password" name="password" placeholder="••••••••" required>
                                        <button type="button" class="field-icon-btn toggle-pass-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-field-wrapper">
                                    <label for="cust-confirm-password">Confirm Password</label>
                                    <div class="input-box">
                                        <input type="password" id="cust-confirm-password" name="confirm_password" placeholder="••••••••" required>
                                        <button type="button" class="field-icon-btn toggle-pass-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="checkbox-container">
                                <label class="custom-checkbox">
                                    <input type="checkbox" required>
                                    <span class="checkbox-square"></span>
                                    <span class="terms-text">I agree to the <a href="../terms.php">Terms and Conditions</a>.</span>
                                </label>
                            </div>

                            <div class="button-row-2col">
                                <a href="role.php" class="btn-outline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                        <polyline points="12 19 5 12 12 5"></polyline>
                                    </svg>
                                    BACK
                                </a>
                                <button type="reset" class="btn-outline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    CLEAR
                                </button>
                            </div>

                            <button type="submit" class="btn-create-submit">CREATE</button>

                            <div class="form-bottom-link">
                                Already have an account? <a href="login.php">Sign in</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="role.js"></script>
    <?php include '../includes/footer.php'; ?>
