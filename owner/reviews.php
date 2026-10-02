<?php
session_start();
require_once '../includes/dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch owner for sidebar
$stmt = $pdo->prepare("SELECT full_name, city, profile_image FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Store Owner';

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

// Owner's store
$stmt = $pdo->prepare("SELECT id, store_name FROM stores WHERE owner_id = ? LIMIT 1");
$stmt->execute([$userId]);
$store = $stmt->fetch(PDO::FETCH_ASSOC);
$storeId = $store['id'] ?? 0;

$responseSuccess = '';
$responseError   = '';

// Handle owner response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['owner_response'])) {
    $reviewId = (int)$_POST['review_id'];
    $responseText = trim($_POST['owner_response']);

    if ($responseText === '') {
        $responseError = 'Response cannot be empty.';
    } else {
        // Make sure this review actually belongs to this owner's store
        $stmtCheck = $pdo->prepare("SELECT id FROM reviews WHERE id = ? AND store_id = ?");
        $stmtCheck->execute([$reviewId, $storeId]);
        if ($stmtCheck->fetch()) {
            $stmtUpdate = $pdo->prepare("UPDATE reviews SET owner_response = ?, owner_response_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$responseText, $reviewId]);
            $responseSuccess = 'Your response has been posted.';
        } else {
            $responseError = 'Review not found.';
        }
    }
}

// Fetch all reviews for this store
$reviews = [];
$avgRating = 0;
$totalReviews = 0;
$ratingCounts = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];

if ($storeId) {
    $stmt = $pdo->prepare("
        SELECT reviews.*, users.full_name AS reviewer_name
        FROM reviews
        JOIN users ON reviews.user_id = users.id
        WHERE reviews.store_id = ? AND reviews.is_hidden = FALSE
        ORDER BY reviews.created_at DESC
    ");
    $stmt->execute([$storeId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalReviews = count($reviews);
    if ($totalReviews > 0) {
        $sum = 0;
        foreach ($reviews as $r) {
            $sum += (int)$r['rating'];
            if (isset($ratingCounts[$r['rating']])) $ratingCounts[$r['rating']]++;
        }
        $avgRating = round($sum / $totalReviews, 1);
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="owner_dashboard_v2.css">
<link rel="stylesheet" href="reviews.css">

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-user-mini">
            <div class="mini-avatar-circle">
                <?php if (!empty($owner['profile_image'])): ?>
                    <img src="/Smart_Greenhouse_Products_Marketplace/<?= htmlspecialchars($owner['profile_image']) ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <?= htmlspecialchars($initials) ?>
                <?php endif; ?>
            </div>
            <div class="mini-user-info">
                <h4 class="mini-name"><?= htmlspecialchars($fullName) ?></h4>
                <span class="mini-role">Store Owner</span>
            </div>
        </div>

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
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/edit_profile.php" class="sidebar-link">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/reviews.php" class="sidebar-link active">
                        <i class="fa-solid fa-star"></i>
                        <span>Reviews and Ratings</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-divider"></div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header-wrapper">
            <h1 class="page-title">Reviews and Ratings</h1>
            <p class="page-subtitle">See what customers are saying about <?= htmlspecialchars($store['store_name'] ?? 'your store') ?>, and respond to their feedback.</p>
        </div>

        <?php if ($responseSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($responseSuccess) ?></div>
        <?php endif; ?>
        <?php if ($responseError): ?>
            <div class="alert alert-error"><?= htmlspecialchars($responseError) ?></div>
        <?php endif; ?>

        <!-- Rating Summary -->
        <div class="rating-summary-card">
            <div class="rating-summary-left">
                <span class="rating-big-number"><?= $totalReviews > 0 ? $avgRating : '0.0' ?></span>
                <div class="rating-stars-row">
                    <?php $r = round($avgRating); for ($i = 1; $i <= 5; $i++): ?>
                        <i class="<?= $i <= $r ? 'fas' : 'far' ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="rating-count-text"><?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?></span>
            </div>
            <div class="rating-summary-bars">
                <?php for ($star = 5; $star >= 1; $star--): 
                    $count = $ratingCounts[$star];
                    $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                ?>
                    <div class="rating-bar-row">
                        <span class="bar-label"><?= $star ?> <i class="fas fa-star"></i></span>
                        <div class="bar-track"><div class="bar-fill" style="width: <?= $pct ?>%;"></div></div>
                        <span class="bar-count"><?= $count ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
            <div class="no-reviews">
                <i class="far fa-comment-dots"></i>
                <h3>No reviews yet</h3>
                <p>Customer reviews for your store will appear here.</p>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar"><?= strtoupper(substr($review['reviewer_name'], 0, 1)) ?></div>
                                <div>
                                    <strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>
                                    <span class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <?php if (!empty($review['comment'])): ?>
                            <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($review['owner_response'])): ?>
                            <div class="owner-response-box">
                                <div class="owner-response-header">
                                    <i class="fa-solid fa-store"></i>
                                    <strong>Response from <?= htmlspecialchars($store['store_name'] ?? 'owner') ?></strong>
                                    <span class="response-date"><?= date('M d, Y', strtotime($review['owner_response_at'])) ?></span>
                                </div>
                                <p><?= nl2br(htmlspecialchars($review['owner_response'])) ?></p>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="reviews.php" class="response-form">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <textarea name="owner_response" rows="2" placeholder="Write a public response to this review..." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Post Response</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-card">
        <div class="modal-icon text-danger">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3 class="modal-title">Confirm Logout</h3>
        <p class="modal-text">Are you sure you want to log out of your CropS account?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelLogoutBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Logout</button>
        </div>
    </div>
</div>

<script>
document.getElementById('confirmLogoutBtn')?.addEventListener('click', () => {
    window.location.href = '/Smart_Greenhouse_Products_Marketplace/logout.php';
});
document.getElementById('cancelLogoutBtn')?.addEventListener('click', () => {
    document.getElementById('logoutModal').classList.remove('active');
});
</script>

<?php include '../includes/footer.php'; ?>