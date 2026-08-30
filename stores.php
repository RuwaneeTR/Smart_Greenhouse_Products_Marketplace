<?php
// ============================================================
// stores.php - Store Listing + Store Details in one file
// No ID = show all stores listing
// With ID = show single store details
// ============================================================
include 'includes/header.php';
include 'includes/dbConnection.php';

// Get store ID from URL if exists
$store_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================================
// VIEW 1: STORE LISTING (no ID in URL)
// ============================================================
if ($store_id === 0) {

    // Get search input
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $nearby = isset($_GET['nearby']) ? true : false;
    $city   = '';

    // If nearby clicked and user logged in, get their city
    if ($nearby && isset($_SESSION['user_id'])) {
        $stmtUser = $pdo->prepare("SELECT city FROM users WHERE id = :id");
        $stmtUser->execute(['id' => $_SESSION['user_id']]);
        $user = $stmtUser->fetch();
        $city = $user ? $user['city'] : '';
    }

    // Build query
    $sql = "SELECT stores.*,
                   users.full_name AS owner_name,
                   COUNT(DISTINCT products.id) AS product_count,
                   ROUND(AVG(reviews.rating), 1) AS avg_rating
            FROM stores
            JOIN users ON stores.owner_id = users.id
            LEFT JOIN products ON products.store_id = stores.id
            LEFT JOIN reviews ON reviews.store_id = stores.id
            WHERE 1=1";

    $params = [];

    if ($search !== '') {
        $sql .= " AND (stores.store_name LIKE :q1 OR stores.city LIKE :q2)";
        $params['q1'] = '%' . $search . '%';
        $params['q2'] = '%' . $search . '%';
    }

    if ($nearby && $city !== '') {
        $sql .= " AND stores.city LIKE :city";
        $params['city'] = '%' . $city . '%';
    }

    $sql .= " GROUP BY stores.id ORDER BY stores.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stores = $stmt->fetchAll();
?>

<!-- ============================================================
     STORE LISTING VIEW
     ============================================================ -->
<div class="main-wrapper">

    <div class="page-header">
        <h2>Greenhouse Stores</h2>
        <p>Browse and discover local greenhouse stores near you</p>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="stores.php" class="stores-search-form" id="searchForm">
        <div class="stores-search-row">
            <div class="stores-search-wrapper">
                <div class="stores-search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        placeholder="Search stores..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        autocomplete="off"
                    >
                </div>
                <ul class="suggestions-dropdown" id="suggestionsDropdown"></ul>
            </div>

            <?php if ($nearby): ?>
                <a href="stores.php<?php echo $search ? '?search='.urlencode($search) : ''; ?>"
                   class="btn-nearby active">
                    <i class="fas fa-check-circle"></i> Nearby Stores
                </a>
            <?php else: ?>
                <button type="submit" name="nearby" value="1" class="btn-nearby">
                    <i class="fas fa-location-dot"></i> Nearby Stores
                </button>
            <?php endif; ?>
        </div>

        <?php if ($search !== '' || $nearby): ?>
            <p class="search-result-info">
                <?php echo count($stores); ?> store(s) found
                <?php if ($search !== ''): ?>
                    for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
                <?php if ($nearby && $city): ?>
                    near <strong><?php echo htmlspecialchars($city); ?></strong>
                <?php endif; ?>
                &nbsp;|&nbsp;
                <a href="stores.php" class="clear-search">Clear</a>
            </p>
        <?php endif; ?>
    </form>

    <!-- Stores Grid -->
    <?php if (count($stores) > 0): ?>
        <div class="stores-grid" id="storesGrid">
            <?php foreach ($stores as $index => $store): ?>
                <!-- Each card links to stores.php?id=X -->
                <a href="stores.php?id=<?php echo $store['id']; ?>"
                   class="store-card <?php echo $index >= 4 ? 'hidden-card' : ''; ?>">

                    <div class="store-card-image">
                        <?php if (!empty($store['image'])): ?>
                            <img src="static/uploads/stores/<?php echo htmlspecialchars($store['image']); ?>"
                                 alt="<?php echo htmlspecialchars($store['store_name']); ?>">
                        <?php else: ?>
                            <img src="static/images/image2.jpg" alt="Store">
                        <?php endif; ?>

                        <?php if ($store['avg_rating']): ?>
                            <span class="rating-badge">
                                <i class="fas fa-star"></i>
                                <?php echo $store['avg_rating']; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="store-card-content">
                        <h3 class="store-name"><?php echo htmlspecialchars($store['store_name']); ?></h3>
                        <div class="store-meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($store['owner_name']); ?></span>
                            <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($store['city']); ?></span>
                            <span><i class="fas fa-box"></i> <?php echo $store['product_count']; ?> Products</span>
                        </div>
                        <?php if (!empty($store['description'])): ?>
                            <p class="store-description">
                                <?php
                                    $desc = $store['description'];
                                    echo htmlspecialchars(strlen($desc) > 120 ? substr($desc, 0, 120).'...' : $desc);
                                ?>
                            </p>
                        <?php endif; ?>
                        <span class="btn-view-store">View Store</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (count($stores) > 4): ?>
            <div class="load-more-wrap">
                <button class="btn-load-more" id="loadMoreBtn" onclick="loadMoreStores()">
                    Load More Growers
                </button>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-store-slash"></i>
            <h3>No stores found</h3>
            <?php if ($search !== ''): ?>
                <p>No stores matched "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
            <?php else: ?>
                <p>No stores have been registered yet.</p>
            <?php endif; ?>
            <a href="stores.php" class="btn btn-primary" style="margin-top:16px;display:inline-block;">View All Stores</a>
        </div>
    <?php endif; ?>

</div>

<?php
// ============================================================
// LISTING VIEW CSS + JS
// ============================================================
?>
<style>
    .page-header { margin-bottom: 24px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; }
    .page-header p  { font-size: 14px; color: var(--on-surface-variant); }

    .stores-search-form { margin-bottom: 28px; }
    .stores-search-row  { display: flex; gap: 12px; align-items: center; margin-bottom: 10px; }
    .stores-search-wrapper { position: relative; flex: 1; }
    .stores-search-box { display: flex; align-items: center; gap: 10px; border: 1px solid var(--outline-variant); border-radius: var(--radius-full); padding: 10px 18px; background: #fff; }
    .stores-search-box i { color: var(--on-surface-variant); }
    .stores-search-box input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--on-surface); background: transparent; }

    .suggestions-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); box-shadow: 0 8px 24px rgba(0,0,0,0.10); list-style: none; z-index: 999; display: none; overflow: hidden; }
    .suggestions-dropdown li { padding: 11px 18px; font-size: 14px; color: var(--on-surface); cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--outline-variant); transition: background 0.15s; }
    .suggestions-dropdown li:last-child { border-bottom: none; }
    .suggestions-dropdown li:hover, .suggestions-dropdown li.suggestion-active { background: #f0f7ee; color: var(--primary); }
    .suggestions-dropdown li i { color: var(--primary); font-size: 13px; }
    .suggestion-city { margin-left: auto; font-size: 12px; color: var(--on-surface-variant); }

    .btn-nearby { display: flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: var(--radius-full); border: 1px solid var(--outline-variant); background: #fff; font-size: 13px; font-weight: 600; color: var(--on-surface); cursor: pointer; white-space: nowrap; font-family: var(--font-family); text-decoration: none; }
    .btn-nearby.active, .btn-nearby:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    .search-result-info { font-size: 13px; color: var(--on-surface-variant); }
    .clear-search { color: var(--primary); font-weight: 600; }
    .clear-search:hover { text-decoration: underline; }

    .stores-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 28px; }
    .hidden-card { display: none; }

    .store-card { background: var(--bg-white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; color: var(--on-surface); border: 1px solid var(--outline-variant); }
    .store-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.10); }
    .store-card-image { position: relative; width: 100%; height: 200px; overflow: hidden; }
    .store-card-image img { width: 100%; height: 100%; object-fit: cover; }
    .rating-badge { position: absolute; top: 12px; right: 12px; background: var(--primary); color: #fff; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); display: flex; align-items: center; gap: 4px; }
    .rating-badge i { font-size: 10px; color: #f5c518; }
    .store-card-content { padding: 16px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .store-name { font-size: 15px; font-weight: 700; color: var(--on-surface); }
    .store-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; color: var(--on-surface-variant); }
    .store-meta span { display: flex; align-items: center; gap: 4px; }
    .store-description { font-size: 13px; color: var(--on-surface-variant); line-height: 1.5; }
    .btn-view-store { display: block; text-align: center; margin-top: auto; padding: 9px; border-radius: var(--radius-lg); border: 1px solid var(--outline-variant); font-size: 13px; font-weight: 600; color: var(--on-surface); background: #fff; transition: background 0.2s, color 0.2s; }
    .store-card:hover .btn-view-store { background: var(--primary); color: #fff; border-color: var(--primary); }

    .load-more-wrap { text-align: center; margin-bottom: 40px; }
    .btn-load-more { padding: 12px 32px; border-radius: var(--radius-full); border: 1px solid var(--outline-variant); background: #fff; font-size: 14px; font-weight: 600; color: var(--on-surface); cursor: pointer; font-family: var(--font-family); transition: background 0.2s, color 0.2s; }
    .btn-load-more:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    .no-results { text-align: center; padding: 60px 20px; color: var(--on-surface-variant); }
    .no-results i { font-size: 48px; margin-bottom: 16px; color: var(--outline-variant); display: block; }
    .no-results h3 { font-size: 20px; margin-bottom: 10px; color: var(--on-surface); }
    .no-results p { font-size: 14px; }

    @media (max-width: 768px) {
        .stores-grid { grid-template-columns: 1fr; }
        .stores-search-row { flex-direction: column; align-items: stretch; }
        .btn-nearby { justify-content: center; }
    }
</style>
<script>
    const searchInput  = document.getElementById('searchInput');
    const dropdown     = document.getElementById('suggestionsDropdown');
    const searchForm   = document.getElementById('searchForm');
    let debounceTimer;
    let activeIndex = -1;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        if (query.length === 0) { hideDropdown(); return; }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchSuggestions(query), 300);
    });

    function fetchSuggestions(query) {
        fetch('search_suggestions.php?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => data.length > 0 ? showDropdown(data, query) : hideDropdown())
            .catch(() => hideDropdown());
    }

    function showDropdown(stores, query) {
        dropdown.innerHTML = '';
        stores.forEach(store => {
            const li = document.createElement('li');
            const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
            const highlighted = store.store_name.replace(regex, '<strong>$1</strong>');
            li.innerHTML = `<i class="fas fa-store"></i><span>${highlighted}</span><span class="suggestion-city"><i class="fas fa-location-dot"></i> ${store.city}</span>`;
            li.addEventListener('click', function () {
                searchInput.value = store.store_name;
                hideDropdown();
                searchForm.submit();
            });
            dropdown.appendChild(li);
        });
        dropdown.style.display = 'block';
    }

    function hideDropdown() { dropdown.style.display = 'none'; dropdown.innerHTML = ''; activeIndex = -1; }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.stores-search-wrapper')) hideDropdown();
    });

    searchInput.addEventListener('keydown', function (e) {
        const items = dropdown.querySelectorAll('li');
        if (items.length === 0) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = (activeIndex + 1) % items.length; updateActive(items); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = (activeIndex - 1 + items.length) % items.length; updateActive(items); }
        else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); items[activeIndex].click(); }
        else if (e.key === 'Escape') hideDropdown();
    });

    function updateActive(items) {
        items.forEach(i => i.classList.remove('suggestion-active'));
        if (activeIndex >= 0) { items[activeIndex].classList.add('suggestion-active'); searchInput.value = items[activeIndex].querySelector('span').textContent; }
    }

    function escapeRegex(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
    function loadMoreStores() { document.querySelectorAll('.store-card.hidden-card').forEach(c => c.classList.remove('hidden-card')); document.getElementById('loadMoreBtn').style.display = 'none'; }
</script>

<?php
// End of listing view
include 'includes/footer.php';
exit;

} // end if ($store_id === 0)

// ============================================================
// VIEW 2: STORE DETAILS (ID given in URL)
// ============================================================

// Fetch store details
$stmtStore = $pdo->prepare("
    SELECT stores.*,
           users.full_name AS owner_name,
           users.email AS owner_email,
           users.gap_certificate,
           COUNT(DISTINCT products.id) AS total_products,
           COUNT(DISTINCT orders.customer_id) AS happy_customers,
           ROUND(AVG(reviews.rating), 1) AS avg_rating,
           COUNT(DISTINCT reviews.id) AS total_reviews
    FROM stores
    JOIN users ON stores.owner_id = users.id
    LEFT JOIN products ON products.store_id = stores.id
    LEFT JOIN orders ON orders.store_id = stores.id
    LEFT JOIN reviews ON reviews.store_id = stores.id
    WHERE stores.id = :id
    GROUP BY stores.id
");
$stmtStore->execute(['id' => $store_id]);
$store = $stmtStore->fetch();

// If store not found go back to listing
if (!$store) {
    header('Location: stores.php');
    exit;
}

// Fetch products
$stmtProducts = $pdo->prepare("SELECT * FROM products WHERE store_id = :id ORDER BY created_at DESC");
$stmtProducts->execute(['id' => $store_id]);
$allProducts = $stmtProducts->fetchAll();

$vegetables = array_filter($allProducts, fn($p) => $p['category'] === 'vegetable');
$fruits      = array_filter($allProducts, fn($p) => $p['category'] === 'fruit');
$plants      = array_filter($allProducts, fn($p) => $p['category'] === 'plant');

// Fetch reviews
$stmtReviews = $pdo->prepare("
    SELECT reviews.*, users.full_name AS reviewer_name
    FROM reviews
    JOIN users ON reviews.user_id = users.id
    WHERE reviews.store_id = :id AND reviews.is_hidden = FALSE
    ORDER BY reviews.created_at DESC
");
$stmtReviews->execute(['id' => $store_id]);
$reviews = $stmtReviews->fetchAll();

// Check if buyer already reviewed
$alreadyReviewed = false;
$canReview = false;

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
    $stmtCheck = $pdo->prepare("SELECT id FROM reviews WHERE store_id = :store_id AND user_id = :user_id");
    $stmtCheck->execute(['store_id' => $store_id, 'user_id' => $_SESSION['user_id']]);
    $alreadyReviewed = $stmtCheck->fetch() ? true : false;
    $canReview = !$alreadyReviewed;
}

// Handle review submission
$reviewSuccess = '';
$reviewError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
        $reviewError = 'You must be logged in as a customer to submit a review.';
    } elseif ($alreadyReviewed) {
        $reviewError = 'You have already reviewed this store.';
    } else {
        $rating  = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please select a rating between 1 and 5.';
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO reviews (store_id, user_id, rating, comment) VALUES (:store_id, :user_id, :rating, :comment)");
            $stmtInsert->execute(['store_id' => $store_id, 'user_id' => $_SESSION['user_id'], 'rating' => $rating, 'comment' => $comment]);
            $reviewSuccess   = 'Your review has been submitted successfully!';
            $alreadyReviewed = true;
            $canReview       = false;
            $stmtReviews->execute(['id' => $store_id]);
            $reviews = $stmtReviews->fetchAll();
        }
    }
}
?>

<!-- ============================================================
     STORE DETAILS VIEW
     ============================================================ -->
<div class="store-details-page">

    <!-- Back to stores link -->
    <div class="main-wrapper" style="padding-bottom:0;">
        <a href="stores.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Stores
        </a>
    </div>

    <!-- Hero Banner -->
    <div class="store-hero">
        <?php if (!empty($store['image'])): ?>
            <img src="static/uploads/stores/<?php echo htmlspecialchars($store['image']); ?>" alt="<?php echo htmlspecialchars($store['store_name']); ?>" class="store-hero-img">
        <?php else: ?>
            <img src="static/images/image1.jpg" alt="Store" class="store-hero-img">
        <?php endif; ?>
        <div class="store-hero-overlay"></div>
    </div>

    <div class="main-wrapper">

        <!-- Store Name Bar -->
        <div class="store-name-bar">
            <div class="store-logo-thumb">
                <img src="static/images/logo.png" alt="Store">
            </div>
            <div class="store-name-info">
                <div class="store-title-row">
                    <h1><?php echo htmlspecialchars($store['store_name']); ?></h1>
                    <?php if (!empty($store['gap_certificate'])): ?>
                        <span class="gap-badge"><i class="fas fa-check-circle"></i> GAP CERTIFIED</span>
                    <?php endif; ?>
                </div>
                <p class="store-meta-line">
                    <span><i class="fas fa-user"></i> Managed by <?php echo htmlspecialchars($store['owner_name']); ?></span>
                    <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($store['city']); ?></span>
                </p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="store-stats">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <span class="stat-label">TOTAL PRODUCTS</span>
                    <span class="stat-value"><?php echo number_format($store['total_products']); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <span class="stat-label">HAPPY CUSTOMERS</span>
                    <span class="stat-value"><?php $hc = $store['happy_customers']; echo $hc >= 1000 ? round($hc/1000,1).'k+' : $hc; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <span class="stat-label">STORE RATING</span>
                    <span class="stat-value"><?php echo $store['avg_rating'] ?? 'N/A'; ?> <small>(<?php echo $store['total_reviews']; ?> Reviews)</small></span>
                </div>
            </div>
        </div>

        <!-- About + Products -->
        <div class="store-main-content">
            <div class="store-about">
                <h3>About the Grower</h3>
                <p><?php echo nl2br(htmlspecialchars($store['description'] ?? 'No description provided.')); ?></p>
                <div class="store-contact-info">
                    <?php if (!empty($store['owner_email'])): ?>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($store['owner_email']); ?>"><?php echo htmlspecialchars($store['owner_email']); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="store-products">
                <div class="category-tabs">
                    <button class="tab-btn active" onclick="showTab('vegetables', this)">Vegetables <span class="tab-count"><?php echo count($vegetables); ?></span></button>
                    <button class="tab-btn" onclick="showTab('fruits', this)">Fruits <span class="tab-count"><?php echo count($fruits); ?></span></button>
                    <button class="tab-btn" onclick="showTab('plants', this)">Plants <span class="tab-count"><?php echo count($plants); ?></span></button>
                </div>

                <div class="tab-content active" id="tab-vegetables">
                    <?php if (count($vegetables) > 0): ?>
                        <div class="products-grid"><?php foreach ($vegetables as $product): ?><?php include 'includes/product_card.php'; ?><?php endforeach; ?></div>
                    <?php else: ?><p class="no-products">No vegetables listed yet.</p><?php endif; ?>
                </div>
                <div class="tab-content" id="tab-fruits">
                    <?php if (count($fruits) > 0): ?>
                        <div class="products-grid"><?php foreach ($fruits as $product): ?><?php include 'includes/product_card.php'; ?><?php endforeach; ?></div>
                    <?php else: ?><p class="no-products">No fruits listed yet.</p><?php endif; ?>
                </div>
                <div class="tab-content" id="tab-plants">
                    <?php if (count($plants) > 0): ?>
                        <div class="products-grid"><?php foreach ($plants as $product): ?><?php include 'includes/product_card.php'; ?><?php endforeach; ?></div>
                    <?php else: ?><p class="no-products">No plants listed yet.</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2>Customer Reviews</h2>
            <div class="review-summary">
                <div class="review-avg">
                    <span class="avg-number"><?php echo $store['avg_rating'] ?? '0'; ?></span>
                    <div class="avg-stars">
                        <?php $avg = round($store['avg_rating'] ?? 0); for ($i = 1; $i <= 5; $i++) echo $i <= $avg ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                    </div>
                    <span class="avg-count"><?php echo $store['total_reviews']; ?> reviews</span>
                </div>
            </div>

            <?php if ($reviewSuccess): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $reviewSuccess; ?></div><?php endif; ?>
            <?php if ($reviewError): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $reviewError; ?></div><?php endif; ?>

            <?php if ($canReview): ?>
                <div class="review-form-box">
                    <h3>Write a Review</h3>
                    <form method="POST" action="stores.php?id=<?php echo $store_id; ?>">
                        <div class="star-rating-input">
                            <p>Your Rating:</p>
                            <div class="stars-select" id="starSelect">
                                <?php for ($i = 1; $i <= 5; $i++): ?><i class="far fa-star" data-value="<?php echo $i; ?>"></i><?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                        </div>
                        <div class="form-group">
                            <label for="comment">Your Review:</label>
                            <textarea name="comment" id="comment" rows="4" placeholder="Share your experience with this greenhouse store..."></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <div class="review-login-prompt"><i class="fas fa-lock"></i><p>Please <a href="login.php">login</a> to write a review.</p></div>
            <?php elseif ($alreadyReviewed): ?>
                <div class="review-login-prompt"><i class="fas fa-check-circle"></i><p>You have already submitted a review for this store.</p></div>
            <?php endif; ?>

            <?php if (count($reviews) > 0): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar"><?php echo strtoupper(substr($review['reviewer_name'], 0, 1)); ?></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++) echo '<i class="'.($i <= $review['rating'] ? 'fas' : 'far').' fa-star"></i>'; ?>
                                </div>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews"><i class="far fa-comment-dots"></i><p>No reviews yet. Be the first to review this store!</p></div>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
    .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--primary); margin-bottom: 12px; }
    .back-link:hover { text-decoration: underline; }
    .store-hero { position: relative; width: 100%; height: 280px; overflow: hidden; }
    .store-hero-img { width: 100%; height: 100%; object-fit: cover; }
    .store-hero-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.35); }
    .store-name-bar { display: flex; align-items: center; gap: 16px; background: #fff; border-radius: var(--radius-lg); padding: 16px 24px; margin-top: -40px; position: relative; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 24px; flex-wrap: wrap; }
    .store-logo-thumb { width: 64px; height: 64px; border-radius: var(--radius-lg); overflow: hidden; border: 2px solid var(--outline-variant); flex-shrink: 0; }
    .store-logo-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .store-name-info { flex: 1; }
    .store-title-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
    .store-title-row h1 { font-size: 1.4rem; font-weight: 700; color: var(--on-surface); }
    .gap-badge { background: #e6f4e1; color: #2d7a1f; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: var(--radius-full); border: 1px solid #b5d9a8; white-space: nowrap; }
    .store-meta-line { display: flex; gap: 20px; font-size: 13px; color: var(--on-surface-variant); flex-wrap: wrap; }
    .store-meta-line span { display: flex; align-items: center; gap: 5px; }
    .store-stats { display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 160px; background: #fff; border-radius: var(--radius-lg); padding: 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--outline-variant); }
    .stat-icon { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .stat-icon.green { background: #e6f4e1; color: #2d7a1f; }
    .stat-icon.gold  { background: #fff8e1; color: #f59e0b; }
    .stat-info { display: flex; flex-direction: column; gap: 2px; }
    .stat-label { font-size: 10px; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.05em; }
    .stat-value { font-size: 1.4rem; font-weight: 700; color: var(--on-surface); }
    .stat-value small { font-size: 12px; font-weight: 400; color: var(--on-surface-variant); }
    .store-main-content { display: flex; gap: 32px; margin-bottom: 40px; align-items: flex-start; flex-wrap: wrap; }
    .store-about { flex: 0 0 220px; min-width: 180px; }
    .store-about h3 { font-size: 15px; font-weight: 700; color: var(--on-surface); margin-bottom: 12px; }
    .store-about p  { font-size: 13px; color: var(--on-surface-variant); line-height: 1.6; margin-bottom: 16px; }
    .store-contact-info p { font-size: 13px; color: var(--on-surface-variant); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .store-contact-info a { color: var(--primary); }
    .store-products { flex: 1; min-width: 0; }
    .category-tabs { display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid var(--outline-variant); }
    .tab-btn { padding: 10px 18px; font-size: 14px; font-weight: 600; color: var(--on-surface-variant); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; font-family: var(--font-family); display: flex; align-items: center; gap: 6px; transition: color 0.2s; }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    .tab-btn:hover  { color: var(--primary); }
    .tab-count { background: var(--outline-variant); color: var(--on-surface-variant); font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: var(--radius-full); }
    .tab-btn.active .tab-count { background: var(--primary); color: #fff; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .product-card { background: #fff; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--outline-variant); transition: transform 0.2s, box-shadow 0.2s; }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
    .product-card-img { position: relative; height: 140px; overflow: hidden; }
    .product-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .stock-badge { position: absolute; top: 8px; right: 8px; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: var(--radius-full); }
    .stock-badge.in-stock  { background: var(--primary); color: #fff; }
    .stock-badge.low-stock { background: #f59e0b; color: #fff; }
    .stock-badge.out-stock { background: #ef4444; color: #fff; }
    .product-card-info { padding: 12px; }
    .product-category-label { font-size: 10px; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .product-name { font-size: 14px; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; line-height: 1.3; }
    .product-price { font-size: 14px; font-weight: 600; color: var(--primary); }
    .product-price span { font-size: 11px; font-weight: 400; color: var(--on-surface-variant); }
    .no-products { font-size: 14px; color: var(--on-surface-variant); padding: 20px 0; }
    .reviews-section { margin-bottom: 40px; }
    .reviews-section h2 { font-size: 1.3rem; font-weight: 700; color: var(--on-surface); margin-bottom: 20px; }
    .review-summary { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
    .review-avg { display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .avg-number { font-size: 2.5rem; font-weight: 700; color: var(--on-surface); }
    .avg-stars i { color: #f59e0b; font-size: 16px; }
    .avg-count  { font-size: 13px; color: var(--on-surface-variant); }
    .alert { padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .alert-success { background: #e6f4e1; color: #2d7a1f; border: 1px solid #b5d9a8; }
    .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .review-form-box { background: #fff; border-radius: var(--radius-lg); padding: 24px; border: 1px solid var(--outline-variant); margin-bottom: 28px; }
    .review-form-box h3 { font-size: 15px; font-weight: 700; margin-bottom: 16px; }
    .star-rating-input { margin-bottom: 16px; }
    .star-rating-input p { font-size: 13px; color: var(--on-surface-variant); margin-bottom: 6px; }
    .stars-select i { font-size: 24px; color: #d1d5db; cursor: pointer; transition: color 0.15s; margin-right: 4px; }
    .stars-select i.selected, .stars-select i.hovered { color: #f59e0b; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--on-surface); }
    .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); font-size: 14px; font-family: var(--font-family); color: var(--on-surface); resize: vertical; outline: none; }
    .form-group textarea:focus { border-color: var(--primary); }
    .review-login-prompt { background: #f9f9f9; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); padding: 16px 20px; font-size: 14px; color: var(--on-surface-variant); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
    .review-login-prompt a { color: var(--primary); font-weight: 600; }
    .reviews-list { display: flex; flex-direction: column; gap: 16px; }
    .review-card { background: #fff; border-radius: var(--radius-lg); padding: 18px; border: 1px solid var(--outline-variant); }
    .review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
    .reviewer-info { display: flex; align-items: center; gap: 10px; }
    .reviewer-avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; flex-shrink: 0; }
    .reviewer-info strong { display: block; font-size: 14px; font-weight: 700; }
    .review-date { font-size: 12px; color: var(--on-surface-variant); }
    .review-stars i { color: #f59e0b; font-size: 13px; }
    .review-comment { font-size: 14px; color: var(--on-surface-variant); line-height: 1.6; }
    .no-reviews { text-align: center; padding: 40px; color: var(--on-surface-variant); }
    .no-reviews i { font-size: 36px; margin-bottom: 10px; display: block; }
    .no-reviews p { font-size: 14px; }
    @media (max-width: 768px) {
        .store-main-content { flex-direction: column; }
        .store-about { flex: none; width: 100%; }
        .store-stats { flex-direction: column; }
        .store-name-bar { margin-top: -20px; }
        .products-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<script>
function showTab(category, clickedBtn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + category).classList.add('active');
    clickedBtn.classList.add('active');
}

const stars = document.querySelectorAll('.stars-select i');
const ratingInput = document.getElementById('ratingInput');
if (stars.length > 0) {
    stars.forEach((star, index) => {
        star.addEventListener('mouseover', function () {
            stars.forEach((s, i) => { s.classList.toggle('hovered', i <= index); s.classList.remove('selected'); });
        });
        star.addEventListener('mouseleave', function () {
            const selected = parseInt(ratingInput.value);
            stars.forEach((s, i) => {
                s.classList.remove('hovered');
                s.className = s.className.replace('fas fa-star', 'far fa-star');
                if (i < selected) { s.classList.replace('far', 'fas'); s.classList.add('selected'); }
            });
        });
        star.addEventListener('click', function () {
            const value = index + 1;
            ratingInput.value = value;
            stars.forEach((s, i) => {
                if (i < value) { s.classList.remove('far'); s.classList.add('fas', 'selected'); }
                else { s.classList.remove('fas', 'selected'); s.classList.add('far'); }
            });
        });
    });
}
</script>

<?php include 'includes/footer.php'; ?>