<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../login/login.php');
    exit;
}

$userId  = $_SESSION['user_id'];
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if ($fullName === '' || $email === '') {
        $error = 'Full Name and Email are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
            $stmt->execute([$fullName, $phone, $email, $address, $userId]);
            $_SESSION['user_name'] = $fullName;
            $success = true;
        } catch (PDOException $e) {
            $error = 'Update failed. Email may already be in use.';
        }
    }
}

$stmt = $pdo->prepare("SELECT full_name, email, city, address, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$nameParts = explode(' ', $user['full_name']);
$initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

include '../includes/header.php';
?>

<link rel="stylesheet" href="edit_profile.css">

  <div class="app-layout">
    <aside class="sidebar">
      <div class="profile-card">
        <div class="avatar-large-wrapper">
          <div class="avatar-circle-lg"><?= htmlspecialchars($initials) ?></div>
          <span class="online-indicator"></span>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($user['full_name']) ?></h3>
        <p class="profile-subtitle">Verified Buyer • <?= htmlspecialchars($user['city']) ?></p>
      </div>

      <nav class="sidebar-nav">
        <a href="customer_dashboard.php" class="sidebar-link">
          <i class="fas fa-home"></i>
          <span>Home(Dashboard)</span>
        </a>
        <a href="notifications.php" class="sidebar-link">
          <i class="far fa-bell"></i>
          <span>Notifications</span>
          <span class="nav-badge badge-unread">3</span>
        </a>
        <a href="edit_profile.php" class="sidebar-link active">
          <i class="far fa-user-circle"></i>
          <span>Edit Profile</span>
          <span class="active-dot"></span>
        </a>
      </nav>
    </aside>

    <main class="main-content">
      <div class="profile-edit-card">

        <?php if ($success): ?>
          <div style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;">
            ✅ Profile updated successfully!
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div style="background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;">
            ⚠️ <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="card-header">
          <div class="title-with-icon">
            <div class="header-icon-pill">
              <i class="far fa-edit"></i>
            </div>
            <div>
              <h2 class="card-title">Edit Profile</h2>
              <p class="card-subtitle">Update your buyer personal info and greenhouse preferences</p>
            </div>
          </div>
          <button class="close-card-btn" title="Close" onclick="window.location.href='customer_dashboard.php'">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <hr class="divider">

        <form id="editProfileForm" class="edit-form" method="POST">

          <div class="avatar-edit-section">
            <div class="avatar-large-wrapper">
              <div class="avatar-circle-lg" id="avatarPreview">
                <?php if (!empty($user['profile_image'])): ?>
                  <img src="/Smart_Greenhouse_Products_Marketplace/<?= htmlspecialchars($user['profile_image']) ?>" 
                    style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                <?= htmlspecialchars($initials) ?>
                <?php endif; ?>
              </div>
              <span class="online-indicator"></span>
           </div>
          <div class="avatar-meta">
            <h4 class="avatar-name"><?= htmlspecialchars($user['full_name']) ?></h4>
            <p class="avatar-sub">Commercial Buyer • <?= htmlspecialchars($user['city']) ?></p>
            <button type="button" class="btn btn-change-photo" id="changePhotoBtn">
              <i class="far fa-image"></i> Change Photo
            </button>
            <!-- Hidden file input -->
            <input type="file" id="avatarFileInput" name="avatar" 
               accept="image/jpeg,image/png,image/webp" style="display:none;">
          </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="fullName">Full Name</label>
              <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
            </div>

            <div class="form-group full-width">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group full-width">
              <label for="address">Delivery Address</label>
              <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-cancel" onclick="window.location.href='customer_dashboard.php'">
              Cancel
            </button>
            <button type="submit" class="btn btn-save">
              <i class="fas fa-check"></i> Save Changes
            </button>
          </div>

        </form>

      </div>
    </main>
  </div>

  <script src="edit_profile.js"></script>
  <?php include '../includes/footer.php'; ?>
