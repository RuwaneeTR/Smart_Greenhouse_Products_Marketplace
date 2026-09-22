<?php
session_start();
require_once '../includes/dbConnection.php';

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch owner data
$stmt = $pdo->prepare("SELECT full_name, email, city, address, phone, gap_certificate FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Test Owner';

// Compute initials
$nameParts = array_values(array_filter(explode(' ', trim($fullName))));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
} elseif (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
} else {
    $initials = 'TO';
}

// Fetch store
$stmtStore = $pdo->prepare("SELECT * FROM stores WHERE owner_id = ? LIMIT 1");
$stmtStore->execute([$userId]);
$store = $stmtStore->fetch(PDO::FETCH_ASSOC);

// ─── Handle avatar upload ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['avatar']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/profile_pics/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext   = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $file  = 'avatar_' . $userId . '_' . time() . '.' . $ext;
    $phys  = $uploadDir . $file;
    $dbPath = 'uploads/profile_pics/' . $file;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $phys)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$dbPath, $userId]);

        // Refresh so the page shows the new image immediately
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="edit_profile.css">

<!-- Overlay backdrop for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <!-- Left Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Profile Mini-Card at Top -->
        <div class="sidebar-user-mini">
            <div class="mini-avatar-circle">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div class="mini-user-info">
                <h4 class="mini-name"><?= htmlspecialchars($fullName) ?></h4>
                <span class="mini-role">Store Owner</span>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="sidebar-menu">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Home (Dashboard)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/add_product.php" class="sidebar-link">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Add Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/order_details.php" class="sidebar-link">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Order Details</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/notifications.php" class="sidebar-link">
                        <i class="fa-solid fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/membership.php" class="sidebar-link">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Membership Fee</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/edit_profile.php" class="sidebar-link active">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/reviews.php" class="sidebar-link">
                        <i class="fa-solid fa-star"></i>
                        <span>Reviews and Ratings</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-divider"></div>

            <div class="sidebar-logout-wrapper">
                <button type="button" class="logout-link" id="logoutBtn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Breadcrumb & Page Header -->
        <div class="page-header-wrapper">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-item">Settings</span>
                <i class="fa-solid fa-chevron-right breadcrumb-separator"></i>
                <span class="breadcrumb-item active">Store Owner Profile</span>
            </nav>
            <div class="header-title-row">
                <h1 class="page-title">Edit Owner Profile</h1>
                <span class="store-id-badge">STORE ID #<?= htmlspecialchars($store['id'] ?? '164') ?></span>
            </div>
            <p class="page-subtitle">Update your store owner credentials, greenhouse verification, contact information, and business location.</p>
        </div>

        <!-- Form Wrapper -->
        <form id="editProfileForm" method="POST" action="edit_profile.php" enctype="multipart/form-data">
            <div class="two-column-layout">
                
                <!-- LEFT COLUMN (~340px) -->
                <div class="left-column">
                    <!-- Card A: Owner Identity -->
                    <div class="edit-card identity-card">
                        <div class="card-header-row">
                            <h3 class="card-title">Owner Identity</h3>
                            <span class="badge-active-operator">
                                <span class="dot-indicator"></span> Active Operator
                            </span>
                        </div>
                        
                        <div class="avatar-upload-section">
                            <div class="large-avatar-wrapper">
                                <div class="large-avatar-circle" id="avatarPreviewCircle">
    <?php if (!empty($owner['profile_image'])): ?>
        <img src="/Smart_Greenhouse_Products_Marketplace/<?= htmlspecialchars($owner['profile_image']) ?>" 
             style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
    <?php else: ?>
        <?= htmlspecialchars($initials) ?>
    <?php endif; ?>
</div>
                                <button type="button" class="avatar-camera-btn" id="changePhotoBtn" title="Change Photo">
                                    <i class="fa-solid fa-camera"></i>
                                </button>
                                <input type="file" id="avatarFileInput" name="avatar" accept="image/jpeg,image/png,image/webp" style="display: none;">
                            </div>
                            <div class="avatar-actions">
                                <button type="button" class="link-btn-green" id="changePhotoLink">Change Photo</button>
                                <button type="button" class="link-btn-red" id="removePhotoLink">Remove</button>
                            </div>
                            <span class="avatar-help-text">JPG, WebP or PNG. Max 5MB.</span>
                        </div>

                        <div class="card-section-divider"></div>

                        <div class="registered-entity-block">
                            <span class="meta-label">REGISTERED ENTITY</span>
                            <h4 class="entity-name"><?= htmlspecialchars($store['store_name'] ?? 'Green Valley Hydroponics') ?></h4>
                            <span class="entity-node">Store Node #<?= htmlspecialchars($store['id'] ?? '164') ?> · Central Region</span>
                            
                            <div class="reg-number-box">
                                <span class="reg-label">Reg No.</span>
                                <span class="reg-value">BR-2021-9941</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card B: GAP Compliance -->
                    <div class="edit-card gap-card">
                        <div class="card-header-row">
                            <div class="title-with-icon">
                                <i class="fa-solid fa-circle-check text-success"></i>
                                <h3 class="card-title">GAP Compliance</h3>
                            </div>
                            <span class="badge-verified">
                                <i class="fa-solid fa-check"></i> VERIFIED
                            </span>
                        </div>
                        
                        <p class="gap-description">
                            Good Agricultural Practices (GAP) certification authorizes direct-to-retail delivery and organic shelf designation.
                        </p>

                        <div class="gap-info-box">
                            <div class="info-row">
                                <span class="info-label">Audit Certificate ID</span>
                                <span class="info-val bold-val"><?= htmlspecialchars($owner['gap_certificate'] ?? 'GAP-LK-2024-884') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Renewal Date</span>
                                <span class="info-val">Nov 14, 2025</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Certifying Authority</span>
                                <span class="info-val">SL Dept. of Agriculture</span>
                            </div>
                        </div>

                        <div class="gap-footer-action">
                            <button type="button" class="reverify-link" id="reverifyBtn">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                <span>Re-verify / Upload Certificate</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN (Flexible) -->
                <div class="right-column">
                    <!-- Card C: Personal & Contact Details -->
                    <div class="edit-card contact-card">
                        <div class="card-header-block">
                            <div class="header-flex-row">
                                <h3 class="card-title">Personal & Contact Details</h3>
                                <span class="header-tag">Primary representative for operations</span>
                            </div>
                            <p class="card-subtitle">These details appear on commercial wholesale dispatches and regional buyer receipts.</p>
                        </div>

                        <div class="form-grid">
                            <!-- Row 1 -->
                            <div class="form-group">
                                <label for="fullNameInput">Full Name</label>
                                <div class="input-with-icon">
                                    <input type="text" id="fullNameInput" name="full_name" class="form-input" value="<?= htmlspecialchars($fullName) ?>" required>
                                    <i class="fa-regular fa-user field-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="roleInput">Role / Designation</label>
                                <input type="text" id="roleInput" name="role" class="form-input" value="Store Owner & Greenhouse Manager">
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group">
                                <label for="emailInput">Email Address</label>
                                <div class="input-with-badge">
                                    <input type="email" id="emailInput" name="email" class="form-input" value="<?= htmlspecialchars($owner['email'] ?? 'owner3@crops.com') ?>" required>
                                    <span class="inline-verified-badge"><i class="fa-solid fa-circle-check"></i> Verified</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phoneInput">Phone Number (SMS Alert Target)</label>
                                <div class="input-with-icon">
                                    <input type="text" id="phoneInput" name="phone" class="form-input" value="<?= htmlspecialchars($owner['phone'] ?? '+94 77 123 4567') ?>" required>
                                    <i class="fa-solid fa-phone field-icon"></i>
                                </div>
                            </div>

                            <!-- Row 3 (Full Width) -->
                            <div class="form-group full-width">
                                <label for="altPhoneInput">Alternate Phone / WhatsApp Hotline</label>
                                <div class="input-with-icon">
                                    <input type="text" id="altPhoneInput" name="alt_phone" class="form-input" value="+94 71 987 6543">
                                    <i class="fa-solid fa-comment-dots field-icon"></i>
                                </div>
                                <span class="field-help-text">Used by delivery drivers and cold-chain logistics couriers for delivery coordination.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card D: Store & Farm Location Details -->
                    <div class="edit-card location-card">
                        <div class="card-header-block">
                            <h3 class="card-title">Store & Farm Location Details</h3>
                            <p class="card-subtitle">Geo-location coordinates allow nearest-hub distribution for perishable greenhouse harvests.</p>
                        </div>

                        <div class="form-grid">
                            <!-- Row 1 -->
                            <div class="form-group">
                                <label for="storeNameInput">Farm / Greenhouse Name</label>
                                <input type="text" id="storeNameInput" name="store_name" class="form-input" value="<?= htmlspecialchars($store['store_name'] ?? 'CropS Kandy Greenhouse Hub') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="citySelect">City / District</label>
                                <div class="select-wrapper">
                                    <select id="citySelect" name="city" class="form-select">
                                        <?php
                                        $cities = ['Kandy', 'Colombo', 'Gampaha', 'Kurunegala', 'Nuwara Eliya', 'Galle', 'Jaffna', 'Matara'];
                                        $currentCity = $owner['city'] ?? $store['city'] ?? 'Kandy';
                                        foreach ($cities as $c):
                                            $selected = (strtolower($c) === strtolower($currentCity)) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $c ?>" <?= $selected ?>><?= $c ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fa-solid fa-chevron-down select-icon"></i>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group full-width-sm">
                                <label for="addressInput">Full Physical Address</label>
                                <input type="text" id="addressInput" name="address" class="form-input" value="<?= htmlspecialchars($owner['address'] ?? $store['address'] ?? '456 Green Farm Road, Kandy') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="postalInput">Postal / Zip Code</label>
                                <input type="text" id="postalInput" name="postal_code" class="form-input" value="20000">
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Row -->
                    <div class="bottom-action-row">
                        <button type="button" class="discard-link" id="discardBtn">
                            <i class="fa-solid fa-rotate-left"></i> Discard Unsaved Changes
                        </button>
                        <div class="action-buttons-group">
                            <button type="button" class="btn btn-cancel" id="cancelBtn">Cancel</button>
                            <button type="submit" class="btn btn-save" id="saveBtn">
                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<!-- Toast Notification -->
<div id="toastNotification" class="toast-notification"></div>

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-card">
        <div class="modal-icon text-danger">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3 class="modal-title">Confirm Logout</h3>
        <p class="modal-text">Are you sure you want to log out of your CropS account?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-cancel" id="cancelLogoutBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Logout</button>
        </div>
    </div>
</div>

<script src="edit_profile.js"></script>
<?php include '../includes/footer.php'; ?>
