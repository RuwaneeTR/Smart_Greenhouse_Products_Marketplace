<?php
session_start();
require_once '../includes/dbConnection.php'; // adjust path as needed

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['customer_id'] = $user['id']; // for cart/checkout

            if ($user['role'] === 'admin') {
                header('Location: #');
            } elseif ($user['role'] === 'owner') {
                header('Location: ../index.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
include '../includes/header.php';
?>

<!-- Login page specific styles (loaded after shared header) -->
<link rel="stylesheet" href="role.css">
    <main class="page-container">
        <div class="login-wrapper active-card">
            <div class="login-logo">
                <img src="/Smart_Greenhouse_Products_Marketplace/static/images/logo.png" alt="CropS" style="height: 80px; width: auto;">
            </div>
            <div class="login-header-top">
                <p class="login-brand-subtitle">Welcome back. Please sign in to your account.</p>
            </div>

            <div class="login-card-box">
                <?php if (!empty($error)): ?>
                    <div style="background: #FEE2E2; color: #B91C1C; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
    <div style="background:#e6f4e1; color:#2d7a1f; padding:12px 16px; border-radius:8px; border:1px solid #b5d9a8; margin-bottom:16px; font-size:14px;">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

                <form id="login-form" method="post" action="login.php">
                    <div class="form-row-1col">
                        <div class="input-field-wrapper">
                            <label for="login-email">Email Address</label>
                            <div class="input-box">
                                <svg class="field-icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <input type="email" id="login-email" name="email" placeholder="grower@example.com" required class="has-left-icon" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-row-1col">
                        <div class="input-field-wrapper">
                            <div class="label-row-with-link">
                                <label for="login-password">Password</label>
                            </div>
                            <div class="input-box">
                                <svg class="field-icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input type="password" id="login-password" name="password" placeholder="••••••••" required class="has-left-icon">
                                <button type="button" class="field-icon-btn toggle-pass-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-signin-submit">
                        Sign In
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

                    <div class="divider-with-text"><span>or</span></div>

                    <div class="login-bottom-link">
                        New to CropS? <a href="role.php">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="rolee.js"></script>
    <?php include '../includes/footer.php'; ?>
