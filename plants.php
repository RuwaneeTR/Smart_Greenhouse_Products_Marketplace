<?php
// ============================================================
// plants.php - Plants Listing + Plant Details in one file
// No ID = show all plants listing
// With ID = show single plant details
// ============================================================
include 'includes/header.php';
include 'includes/dbConnection.php';

// Get plant/product ID from URL
$plant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================================
// VIEW 1: PLANTS LISTING (no ID in URL)
// ============================================================
if ($plant_id === 0) {

    $search    = isset($_GET['search'])    ? trim($_GET['search'])    : '';
    $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

    // Only fetch plants category
    $sql = "SELECT products.*, stores.store_name
            FROM products
            JOIN stores ON products.store_id = stores.id
            WHERE products.category = 'plant'";

    $params = [];

    if ($search !== '') {
        $sql .= " AND products.name LIKE :search";
        $params['search'] = '%' . $search . '%';
    }
    if ($min_price !== null) {
        $sql .= " AND products.price >= :min_price";
        $params['min_price'] = $min_price;
    }
    if ($max_price !== null) {
        $sql .= " AND products.price <= :max_price";
        $params['max_price'] = $max_price;
    }

    $sql .= " ORDER BY products.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $plants = $stmt->fetchAll();
?>

<!-- ============================================================
     PLANTS LISTING VIEW
     ============================================================ -->
<div class="main-wrapper">

    <!-- Page Title -->
    <div class="plants-page-header">
        <h1>Our Plants & Produce</h1>
    </div>

    <!-- Green Banner -->
    <div class="plants-banner">
        <p>Discover our curated selection of high-yield, resilient plants and top-quality vegetables and fruits tailored for your growing environment.</p>
    </div>

    <!-- Search + Filter Row -->
    <div class="plants-controls">

        <!-- Search -->
        <form method="GET" action="plants.php" id="plantSearchForm">
            <?php if ($min_price !== null): ?>
                <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
            <?php endif; ?>
            <?php if ($max_price !== null): ?>
                <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
            <?php endif; ?>
            <div class="plants-search-wrapper">
                <div class="plants-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="plantSearchInput"
                           placeholder="Search plants..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           autocomplete="off">
                </div>
                <ul class="suggestions-dropdown" id="plantSuggestions"></ul>
            </div>
        </form>

        <!-- Price Filter -->
        <form method="GET" action="plants.php" class="plants-filter-form">
            <?php if ($search): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            <div class="price-filter-row">
                <input type="number" name="min_price" placeholder="Min price"
                       value="<?php echo $min_price !== null ? $min_price : ''; ?>" min="0">
                <span>–</span>
                <input type="number" name="max_price" placeholder="Max price"
                       value="<?php echo $max_price !== null ? $max_price : ''; ?>" min="0">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size:13px;">Filter</button>
                <?php if ($min_price !== null || $max_price !== null || $search): ?>
                    <a href="plants.php" class="clear-filters">Clear</a>
                <?php endif; ?>
            </div>
        </form>

    </div>

    <!-- Plants Grid -->
    <?php if (count($plants) > 0): ?>
        <div class="plants-grid">
            <?php foreach ($plants as $plant): ?>

                <?php
                // Stock status
                if ($plant['quantity'] <= 0) {
                    $stockLabel = 'Out of Stock';
                    $stockClass = 'out-stock';
                } elseif ($plant['quantity'] <= 10) {
                    $stockLabel = 'Low Stock';
                    $stockClass = 'low-stock';
                } else {
                    $stockLabel = 'In Stock';
                    $stockClass = 'in-stock';
                }
                ?>

                <!-- Plant Card - links to plants.php?id=X -->
                <a href="plants.php?id=<?php echo $plant['id']; ?>" class="plant-card">

                    <!-- Image -->
                    <div class="plant-card-img">
                        <?php if (!empty($plant['image'])): ?>
                            <img src="static/uploads/products/<?php echo htmlspecialchars($plant['image']); ?>"
                                 alt="<?php echo htmlspecialchars($plant['name']); ?>">
                        <?php else: ?>
                            <img src="static/images/image2.jpg" alt="Plant">
                        <?php endif; ?>
                    </div>

                    <!-- Card Info -->
                    <div class="plant-card-info">

                        <!-- Name + Price row -->
                        <div class="plant-name-price">
                            <h3 class="plant-name"><?php echo htmlspecialchars($plant['name']); ?></h3>
                            <span class="plant-price">Rs. <?php echo number_format($plant['price'], 2); ?></span>
                        </div>

                        <!-- Store name -->
                        <p class="plant-store">
                            <i class="fas fa-store"></i>
                            <?php echo htmlspecialchars($plant['store_name']); ?>
                        </p>

                        <!-- Description - trimmed -->
                        <?php if (!empty($plant['description'])): ?>
                            <div class="plant-why-fits">
                                <p class="why-fits-label">
                                    <i class="fas fa-check-circle"></i> Description
                                </p>
                                <p class="why-fits-text">
                                    <?php
                                        $desc = $plant['description'];
                                        echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100).'...' : $desc);
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Stock + Add to Cart -->
                        <div class="plant-card-bottom">
                            <span class="plant-stock <?php echo $stockClass; ?>">
                                <?php echo $stockLabel; ?>
                            </span>
                            <span class="plant-card-btn">View Plant</span>
                        </div>

                    </div>
                </a>

            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-seedling"></i>
            <h3>No plants found</h3>
            <?php if ($search !== ''): ?>
                <p>No plants matched "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
            <?php else: ?>
                <p>No plants available yet.</p>
            <?php endif; ?>
            <a href="plants.php" class="btn btn-primary" style="margin-top:16px;display:inline-block;">View All Plants</a>
        </div>
    <?php endif; ?>

</div>

<style>
    /* Page Header */
    .plants-page-header { margin-bottom: 16px; }
    .plants-page-header h1 { font-size: 1.8rem; font-weight: 700; color: var(--on-surface); }

    /* Banner */
    .plants-banner {
        background: var(--primary);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .plants-banner p { font-size: 14px; color: #fff; opacity: 0.9; line-height: 1.6; }

    /* Controls row */
    .plants-controls { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; }

    /* Search */
    .plants-search-wrapper { position: relative; min-width: 280px; }
    .plants-search-box { display: flex; align-items: center; gap: 10px; border: 1px solid var(--outline-variant); border-radius: var(--radius-full); padding: 10px 18px; background: #fff; }
    .plants-search-box i { color: var(--on-surface-variant); }
    .plants-search-box input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--on-surface); background: transparent; }

    /* Suggestions Dropdown */
    .suggestions-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); box-shadow: 0 8px 24px rgba(0,0,0,0.10); list-style: none; z-index: 999; display: none; overflow: hidden; }
    .suggestions-dropdown li { padding: 11px 18px; font-size: 14px; color: var(--on-surface); cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--outline-variant); transition: background 0.15s; }
    .suggestions-dropdown li:last-child { border-bottom: none; }
    .suggestions-dropdown li:hover, .suggestions-dropdown li.active { background: #f0f7ee; color: var(--primary); }
    .suggestions-dropdown li i { color: var(--primary); font-size: 13px; }

    /* Price filter */
    .plants-filter-form { display: flex; align-items: center; }
    .price-filter-row { display: flex; align-items: center; gap: 8px; }
    .price-filter-row input { padding: 9px 12px; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); font-size: 13px; outline: none; font-family: var(--font-family); width: 110px; }
    .price-filter-row input:focus { border-color: var(--primary); }
    .price-filter-row span { color: var(--on-surface-variant); }
    .clear-filters { font-size: 13px; color: var(--primary); font-weight: 600; }
    .clear-filters:hover { text-decoration: underline; }

    /* Plants Grid - 3 columns */
    .plants-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }

    /* Plant Card */
    .plant-card {
        background: #fff;
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid var(--outline-variant);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex; flex-direction: column;
        color: var(--on-surface);
    }
    .plant-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.09); }

    /* Plant Image */
    .plant-card-img { width: 100%; height: 200px; overflow: hidden; }
    .plant-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .plant-card:hover .plant-card-img img { transform: scale(1.05); }

    /* Plant Info */
    .plant-card-info { padding: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }

    /* Name + Price row */
    .plant-name-price { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .plant-name { font-size: 15px; font-weight: 700; color: var(--on-surface); line-height: 1.3; }
    .plant-price { font-size: 14px; font-weight: 700; color: var(--on-surface); white-space: nowrap; }

    /* Store */
    .plant-store { font-size: 12px; color: var(--on-surface-variant); display: flex; align-items: center; gap: 5px; }

    /* Why it fits */
    .plant-why-fits { background: #f0f7ee; border-radius: var(--radius-lg); padding: 10px 12px; }
    .why-fits-label { font-size: 11px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
    .why-fits-label i { font-size: 11px; }
    .why-fits-text { font-size: 12px; color: var(--primary); line-height: 1.4; }

    /* Bottom row */
    .plant-card-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
    .plant-stock { font-size: 11px; font-weight: 600; }
    .plant-stock.in-stock  { color: #2d7a1f; }
    .plant-stock.low-stock { color: #b45309; }
    .plant-stock.out-stock { color: #dc2626; }

    .plant-card-btn {
        padding: 8px 16px;
        background: var(--primary);
        color: #fff;
        border-radius: var(--radius-full);
        font-size: 12px; font-weight: 700;
        transition: opacity 0.2s;
    }
    .plant-card:hover .plant-card-btn { opacity: 0.85; }

    /* No results */
    .no-results { text-align: center; padding: 60px 20px; color: var(--on-surface-variant); }
    .no-results i { font-size: 48px; margin-bottom: 16px; color: var(--outline-variant); display: block; }
    .no-results h3 { font-size: 20px; margin-bottom: 10px; color: var(--on-surface); }
    .no-results p { font-size: 14px; }

    /* Mobile */
    @media (max-width: 768px) {
        .plants-grid { grid-template-columns: 1fr; }
        .plants-controls { flex-direction: column; }
        .plants-search-wrapper { min-width: 100%; }
    }
    @media (max-width: 480px) {
        .plants-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
const plantSearchInput = document.getElementById('plantSearchInput');
const plantSuggestions = document.getElementById('plantSuggestions');
const plantSearchForm  = document.getElementById('plantSearchForm');
let debounceTimer;
let activeIndex = -1;

plantSearchInput.addEventListener('input', function () {
    const query = this.value.trim();
    if (query.length === 0) { hideDropdown(); return; }
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchSuggestions(query), 300);
});

function fetchSuggestions(query) {
    fetch('plant_suggestions.php?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => data.length > 0 ? showDropdown(data, query) : hideDropdown())
        .catch(() => hideDropdown());
}

function showDropdown(plants, query) {
    plantSuggestions.innerHTML = '';
    plants.forEach(plant => {
        const li = document.createElement('li');
        const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        const highlighted = plant.name.replace(regex, '<strong>$1</strong>');
        li.innerHTML = `<i class="fas fa-seedling"></i><span>${highlighted}</span>`;
        li.addEventListener('click', function () {
            plantSearchInput.value = plant.name;
            hideDropdown();
            plantSearchForm.submit();
        });
        plantSuggestions.appendChild(li);
    });
    plantSuggestions.style.display = 'block';
}

function hideDropdown() { plantSuggestions.style.display = 'none'; plantSuggestions.innerHTML = ''; activeIndex = -1; }

document.addEventListener('click', function (e) {
    if (!e.target.closest('.plants-search-wrapper')) hideDropdown();
});

plantSearchInput.addEventListener('keydown', function (e) {
    const items = plantSuggestions.querySelectorAll('li');
    if (items.length === 0) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = (activeIndex + 1) % items.length; updateActive(items); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = (activeIndex - 1 + items.length) % items.length; updateActive(items); }
    else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); items[activeIndex].click(); }
    else if (e.key === 'Escape') hideDropdown();
});

function updateActive(items) {
    items.forEach(i => i.classList.remove('active'));
    if (activeIndex >= 0) { items[activeIndex].classList.add('active'); plantSearchInput.value = items[activeIndex].querySelector('span').textContent; }
}

function escapeRegex(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
</script>

<?php
include 'includes/footer.php';
exit;
} // end listing view

// ============================================================
// VIEW 2: PLANT DETAILS (ID given in URL)
// ============================================================

$stmtPlant = $pdo->prepare("
    SELECT products.*, stores.store_name, stores.city AS store_city, stores.id AS store_id
    FROM products
    JOIN stores ON products.store_id = stores.id
    WHERE products.id = :id AND products.category = 'plant'
");
$stmtPlant->execute(['id' => $plant_id]);
$plant = $stmtPlant->fetch();

// If not found or not a plant, redirect
if (!$plant) {
    header('Location: plants.php');
    exit;
}

// Stock status
if ($plant['quantity'] <= 0) {
    $stockLabel = 'Out of Stock';
    $stockClass = 'out-stock';
} elseif ($plant['quantity'] <= 10) {
    $stockLabel = 'Low Stock';
    $stockClass = 'low-stock';
} else {
    $stockLabel = 'In Stock';
    $stockClass = 'in-stock';
}
?>

<!-- ============================================================
     PLANT DETAILS VIEW
     ============================================================ -->
<div class="main-wrapper">

    <!-- Back link -->
    <a href="plants.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Plants
    </a>

    <div class="plant-detail-layout">

        <!-- LEFT: Plant Image -->
        <div class="plant-detail-img">
            <?php if (!empty($plant['image'])): ?>
                <img src="static/uploads/products/<?php echo htmlspecialchars($plant['image']); ?>"
                     alt="<?php echo htmlspecialchars($plant['name']); ?>">
            <?php else: ?>
                <img src="static/images/image2.jpg" alt="Plant">
            <?php endif; ?>
        </div>

        <!-- RIGHT: Plant Info -->
        <div class="plant-detail-info">

            <!-- Category tag -->
            <div class="plant-detail-meta">
                <span class="plant-detail-tag">PLANTS</span>
                <span class="plant-detail-store">
                    <i class="fas fa-store"></i>
                    <a href="stores.php?id=<?php echo $plant['store_id']; ?>">
                        <?php echo htmlspecialchars($plant['store_name']); ?>
                    </a>
                    &bull;
                    <i class="fas fa-location-dot"></i>
                    <?php echo htmlspecialchars($plant['store_city']); ?>
                </span>
            </div>

            <!-- Plant Name -->
            <h1 class="plant-detail-name">
                <?php echo htmlspecialchars($plant['name']); ?>
            </h1>

            <!-- Price -->
            <div class="plant-detail-price-wrap">
                <span class="plant-detail-price">
                    Rs. <?php echo number_format($plant['price'], 2); ?>
                </span>
                <span class="plant-detail-per">per plant</span>
            </div>

            <!-- Description -->
            <?php if (!empty($plant['description'])): ?>
                <div class="plant-detail-desc-box">
                    <p class="plant-detail-desc-label">Description</p>
                    <p class="plant-detail-desc">
                        <?php echo nl2br(htmlspecialchars($plant['description'])); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Stock Status -->
            <span class="plant-detail-stock <?php echo $stockClass; ?>">
                <i class="fas fa-circle"></i>
                <?php echo $stockLabel; ?> (<?php echo $plant['quantity']; ?> available)
            </span>

            <?php if ($plant['quantity'] > 0): ?>

                <!-- Quantity Selector -->
                <div class="plant-qty-wrap">
                    <p class="qty-label">Quantity: </p>
                    <div class="qty-controls">
                        <button type="button" onclick="changeQty(-1)" class="qty-btn">−</button>
                        <span id="qtyDisplay">1</span>
                        <button type="button" onclick="changeQty(1)" class="qty-btn">+</button>
                    </div>
                    <span class="qty-unit">units</span>
                </div>

                <!-- Action Buttons -->
                <div class="plant-detail-btns">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <a href="cart.php?add=<?php echo $plant['id']; ?>&qty=1"
                           id="addToCartBtn"
                           class="btn btn-outline plant-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="checkout.php?product=<?php echo $plant['id']; ?>&qty=1"
                           id="buyNowBtn"
                           class="btn btn-primary plant-buy-btn">
                            <i class="fas fa-bolt"></i> Buy Now
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline plant-cart-btn">
                            <i class="fas fa-lock"></i> Login to Purchase
                        </a>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="out-of-stock-msg">
                    <i class="fas fa-times-circle"></i> This plant is currently out of stock.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--primary); margin-bottom: 20px; }
    .back-link:hover { text-decoration: underline; }

    /* Detail Layout */
    .plant-detail-layout { display: flex; gap: 48px; align-items: flex-start; background: #fff; border-radius: var(--radius-xl); padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid var(--outline-variant); margin-bottom: 40px; }

    /* Image */
    .plant-detail-img { flex: 0 0 420px; max-width: 420px; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--outline-variant); }
    .plant-detail-img img { width: 100%; height: 400px; object-fit: cover; display: block; }

    /* Info */
    .plant-detail-info { flex: 1; display: flex; flex-direction: column; gap: 16px; }

    /* Meta */
    .plant-detail-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .plant-detail-tag { background: var(--primary); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: var(--radius-full); letter-spacing: 0.05em; }
    .plant-detail-store { font-size: 13px; color: var(--on-surface-variant); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .plant-detail-store a { color: var(--primary); font-weight: 600; }
    .plant-detail-store a:hover { text-decoration: underline; }

    /* Name */
    .plant-detail-name { font-size: 2rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; }

    /* Price */
    .plant-detail-price-wrap { display: flex; align-items: baseline; gap: 8px; }
    .plant-detail-price { font-size: 2rem; font-weight: 700; color: var(--on-surface); }
    .plant-detail-per { font-size: 14px; color: var(--on-surface-variant); }

    /* Description */
    .plant-detail-desc-box { background: #f9f9f9; border-radius: var(--radius-lg); padding: 16px; border: 1px solid var(--outline-variant); }
    .plant-detail-desc-label { font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
    .plant-detail-desc { font-size: 14px; color: var(--on-surface-variant); line-height: 1.7; }

    /* Stock */
    .plant-detail-stock { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .plant-detail-stock i { font-size: 8px; }
    .plant-detail-stock.in-stock  { color: #2d7a1f; }
    .plant-detail-stock.low-stock { color: #b45309; }
    .plant-detail-stock.out-stock { color: #dc2626; }

    /* Quantity */
    .plant-qty-wrap { display: flex; align-items: center; gap: 12px; }
    .qty-label { font-size: 14px; font-weight: 600; color: var(--on-surface); }
    .qty-controls { display: flex; align-items: center; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); overflow: hidden; }
    .qty-btn { width: 38px; height: 38px; background: #f5f5f5; border: none; font-size: 18px; cursor: pointer; color: var(--on-surface); font-family: var(--font-family); transition: background 0.15s; }
    .qty-btn:hover { background: #e8e8e8; }
    #qtyDisplay { min-width: 48px; text-align: center; font-size: 15px; font-weight: 600; color: var(--on-surface); }
    .qty-unit { font-size: 13px; color: var(--on-surface-variant); font-weight: 600; }

    /* Buttons */
    .plant-detail-btns { display: flex; gap: 12px; flex-wrap: wrap; }
    .plant-cart-btn { padding: 12px 24px; font-size: 14px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--outline-variant); }
    .plant-buy-btn  { padding: 12px 24px; font-size: 14px; display: flex; align-items: center; gap: 8px; }

    /* Out of stock */
    .out-of-stock-msg { font-size: 14px; color: #dc2626; display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #fef2f2; border-radius: var(--radius-lg); border: 1px solid #fecaca; }

    /* Mobile */
    @media (max-width: 768px) {
        .plant-detail-layout { flex-direction: column; padding: 20px; gap: 24px; }
        .plant-detail-img { flex: none; max-width: 100%; }
        .plant-detail-img img { height: 250px; }
        .plant-detail-name { font-size: 1.5rem; }
        .plant-detail-price { font-size: 1.5rem; }
    }
</style>

<script>
let qty = 1;
const maxQty = <?php echo $plant['quantity']; ?>;

function changeQty(change) {
    qty = qty + change;
    if (qty < 1) qty = 1;
    if (qty > maxQty) qty = maxQty;
    document.getElementById('qtyDisplay').textContent = qty;
    updateBtns();
}

function updateBtns() {
    const addBtn = document.getElementById('addToCartBtn');
    const buyBtn = document.getElementById('buyNowBtn');
    if (addBtn) addBtn.href = `cart.php?add=<?php echo $plant['id']; ?>&qty=${qty}`;
    if (buyBtn) buyBtn.href = `checkout.php?product=<?php echo $plant['id']; ?>&qty=${qty}`;
}
</script>

<?php include 'includes/footer.php'; ?>