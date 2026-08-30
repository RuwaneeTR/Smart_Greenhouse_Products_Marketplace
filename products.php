<?php
// ============================================================
// products.php - Products Listing + Product Details in one file
// No ID = show all vegetables & fruits listing
// With ID = show single product details
// ============================================================
include 'includes/header.php';
include 'includes/dbConnection.php';

// Get product ID from URL if exists
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================================
// VIEW 1: PRODUCTS LISTING (no ID in URL)
// ============================================================
if ($product_id === 0) {

    // Get filters from URL
    $search    = isset($_GET['search'])    ? trim($_GET['search'])    : '';
    $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

    // Build SQL - only vegetables and fruits
    $sql = "SELECT products.*, stores.store_name
            FROM products
            JOIN stores ON products.store_id = stores.id
            WHERE products.category IN ('vegetable', 'fruit')";

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
    $products = $stmt->fetchAll();
?>

<!-- ============================================================
     PRODUCTS LISTING VIEW
     ============================================================ -->
<div class="main-wrapper">
    <div class="products-layout">

        <!-- Sidebar -->
        <aside class="products-sidebar">
            <div class="sidebar-section">
                <h4>Categories</h4>
                <ul class="category-list">
                    <li class="active">
                        <a href="products.php">
                            Vegetables & Fruits
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a href="plants.php">Plants</a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="filter-header">
                    <h4>Filters</h4>
                    <a href="products.php" class="clear-filters">Clear all</a>
                </div>
                <form method="GET" action="products.php">
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>
                    <p class="filter-label">PRICE RANGE</p>
                    <div class="price-range-inputs">
                        <input type="number" name="min_price" placeholder="Min"
                               value="<?php echo $min_price !== null ? $min_price : ''; ?>" min="0">
                        <span>–</span>
                        <input type="number" name="max_price" placeholder="Max"
                               value="<?php echo $max_price !== null ? $max_price : ''; ?>" min="0">
                    </div>
                    <button type="submit" class="btn-apply-filters">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="products-main">

            <!-- Green Banner -->
            <div class="products-banner">
                <h2>Fresh Produce</h2>
                <p>Discover fresh, sustainably grown produce sourced directly from local greenhouses.</p>
                <span class="result-count">Showing <?php echo count($products); ?> results for Vegetables & Fruits</span>
            </div>

            <!-- Search Bar -->
            <div class="products-search-wrap">
                <form method="GET" action="products.php" id="productSearchForm">
                    <?php if ($min_price !== null): ?>
                        <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
                    <?php endif; ?>
                    <?php if ($max_price !== null): ?>
                        <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
                    <?php endif; ?>
                    <div class="products-search-wrapper">
                        <div class="products-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="productSearchInput"
                                   placeholder="Search products..."
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   autocomplete="off">
                        </div>
                        <ul class="suggestions-dropdown" id="productSuggestions"></ul>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <?php if (count($products) > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php include 'includes/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-carrot"></i>
                    <h3>No products found</h3>
                    <?php if ($search !== ''): ?>
                        <p>No products matched "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
                    <?php else: ?>
                        <p>No products available yet.</p>
                    <?php endif; ?>
                    <a href="products.php" class="btn btn-primary" style="margin-top:16px;display:inline-block;">View All Products</a>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<style>
    .products-layout { display: flex; gap: 24px; align-items: flex-start; }
    .products-sidebar { flex: 0 0 200px; min-width: 160px; display: flex; flex-direction: column; gap: 24px; }
    .sidebar-section { background: #fff; border-radius: var(--radius-lg); padding: 16px; border: 1px solid var(--outline-variant); }
    .sidebar-section h4 { font-size: 14px; font-weight: 700; color: var(--on-surface); margin-bottom: 12px; }
    .category-list { list-style: none; }
    .category-list li { margin-bottom: 4px; }
    .category-list li a { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: var(--radius-lg); font-size: 13px; font-weight: 500; color: var(--on-surface-variant); transition: background 0.15s, color 0.15s; }
    .category-list li a:hover { background: #f0f7ee; color: var(--primary); }
    .category-list li.active a { background: #e6f4e1; color: var(--primary); font-weight: 700; }
    .filter-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .filter-header h4 { margin-bottom: 0; }
    .clear-filters { font-size: 12px; color: var(--primary); font-weight: 600; }
    .clear-filters:hover { text-decoration: underline; }
    .filter-label { font-size: 11px; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
    .price-range-inputs { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
    .price-range-inputs input { width: 100%; padding: 7px 10px; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); font-size: 13px; outline: none; font-family: var(--font-family); }
    .price-range-inputs input:focus { border-color: var(--primary); }
    .price-range-inputs span { font-size: 13px; color: var(--on-surface-variant); flex-shrink: 0; }
    .btn-apply-filters { width: 100%; padding: 10px; background: var(--primary); color: #fff; border: none; border-radius: var(--radius-lg); font-size: 13px; font-weight: 700; cursor: pointer; font-family: var(--font-family); transition: opacity 0.2s; }
    .btn-apply-filters:hover { opacity: 0.9; }
    .products-main { flex: 1; min-width: 0; }
    .products-banner { background: var(--primary); color: #fff; border-radius: var(--radius-lg); padding: 24px 28px; margin-bottom: 20px; }
    .products-banner h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 6px; }
    .products-banner p  { font-size: 14px; opacity: 0.85; margin-bottom: 8px; line-height: 1.5; }
    .result-count { font-size: 13px; opacity: 0.75; font-style: italic; }
    .products-search-wrap { margin-bottom: 20px; }
    .products-search-wrapper { position: relative; }
    .products-search-box { display: flex; align-items: center; gap: 10px; border: 1px solid var(--outline-variant); border-radius: var(--radius-full); padding: 10px 18px; background: #fff; }
    .products-search-box i { color: var(--on-surface-variant); }
    .products-search-box input { border: none; outline: none; width: 100%; font-size: 14px; color: var(--on-surface); background: transparent; }
    .suggestions-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); box-shadow: 0 8px 24px rgba(0,0,0,0.10); list-style: none; z-index: 999; display: none; overflow: hidden; }
    .suggestions-dropdown li { padding: 11px 18px; font-size: 14px; color: var(--on-surface); cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--outline-variant); transition: background 0.15s; }
    .suggestions-dropdown li:last-child { border-bottom: none; }
    .suggestions-dropdown li:hover, .suggestions-dropdown li.active { background: #f0f7ee; color: var(--primary); }
    .suggestions-dropdown li i { color: var(--primary); font-size: 13px; }
    .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .product-card { background: #fff; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--outline-variant); transition: transform 0.2s, box-shadow 0.2s; color: var(--on-surface); display: block; }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.09); }
    .product-card-img { position: relative; height: 180px; overflow: hidden; }
    .product-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .stock-badge { position: absolute; top: 8px; right: 8px; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: var(--radius-full); }
    .stock-badge.in-stock  { background: var(--primary); color: #fff; }
    .stock-badge.low-stock { background: #f59e0b; color: #fff; }
    .stock-badge.out-stock { background: #ef4444; color: #fff; }
    .product-card-info { padding: 14px; }
    .product-category-label { font-size: 10px; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .product-name { font-size: 14px; font-weight: 700; color: var(--on-surface); margin-bottom: 4px; line-height: 1.3; }
    .product-price { font-size: 14px; font-weight: 600; color: var(--primary); }
    .product-price span { font-size: 11px; font-weight: 400; color: var(--on-surface-variant); }
    .no-results { text-align: center; padding: 60px 20px; color: var(--on-surface-variant); }
    .no-results i { font-size: 48px; margin-bottom: 16px; color: var(--outline-variant); display: block; }
    .no-results h3 { font-size: 20px; margin-bottom: 10px; color: var(--on-surface); }
    .no-results p { font-size: 14px; }
    @media (max-width: 768px) {
        .products-layout { flex-direction: column; }
        .products-sidebar { flex: none; width: 100%; }
        .products-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) { .products-grid { grid-template-columns: 1fr; } }
    /* Unit Toggle */
.unit-toggle { margin-bottom: 4px; }
.unit-btns { display: flex; gap: 8px; margin-top: 8px; }
.unit-btn {
    padding: 8px 20px;
    border-radius: var(--radius-full);
    border: 1px solid var(--outline-variant);
    background: #fff;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    font-family: var(--font-family);
    color: var(--on-surface);
    transition: background 0.2s, color 0.2s;
}
.unit-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.unit-btn:hover  { background: var(--primary); color: #fff; border-color: var(--primary); }
</style>

<script>
const productSearchInput = document.getElementById('productSearchInput');
const productSuggestions = document.getElementById('productSuggestions');
const productSearchForm  = document.getElementById('productSearchForm');
let debounceTimer;
let activeIndex = -1;

productSearchInput.addEventListener('input', function () {
    const query = this.value.trim();
    if (query.length === 0) { hideDropdown(); return; }
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchSuggestions(query), 300);
});

function fetchSuggestions(query) {
    fetch('product_suggestions.php?q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => data.length > 0 ? showDropdown(data, query) : hideDropdown())
        .catch(() => hideDropdown());
}

function showDropdown(products, query) {
    productSuggestions.innerHTML = '';
    products.forEach(product => {
        const li = document.createElement('li');
        const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        const highlighted = product.name.replace(regex, '<strong>$1</strong>');
        li.innerHTML = `<i class="fas fa-seedling"></i><span>${highlighted}</span>`;
        li.addEventListener('click', function () {
            productSearchInput.value = product.name;
            hideDropdown();
            productSearchForm.submit();
        });
        productSuggestions.appendChild(li);
    });
    productSuggestions.style.display = 'block';
}

function hideDropdown() { productSuggestions.style.display = 'none'; productSuggestions.innerHTML = ''; activeIndex = -1; }

document.addEventListener('click', function (e) {
    if (!e.target.closest('.products-search-wrapper')) hideDropdown();
});

productSearchInput.addEventListener('keydown', function (e) {
    const items = productSuggestions.querySelectorAll('li');
    if (items.length === 0) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = (activeIndex + 1) % items.length; updateActive(items); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = (activeIndex - 1 + items.length) % items.length; updateActive(items); }
    else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); items[activeIndex].click(); }
    else if (e.key === 'Escape') hideDropdown();
});

function updateActive(items) {
    items.forEach(i => i.classList.remove('active'));
    if (activeIndex >= 0) { items[activeIndex].classList.add('active'); productSearchInput.value = items[activeIndex].querySelector('span').textContent; }
}

function escapeRegex(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
</script>

<?php
include 'includes/footer.php';
exit;
} // end listing view

// ============================================================
// VIEW 2: PRODUCT DETAILS (ID given in URL)
// ============================================================

// Fetch product details — JOIN stores to get store name and ID
$stmtProduct = $pdo->prepare("
    SELECT products.*, stores.store_name, stores.id AS store_id
    FROM products
    JOIN stores ON products.store_id = stores.id
    WHERE products.id = :id
");
$stmtProduct->execute(['id' => $product_id]);
$product = $stmtProduct->fetch();

// If product not found, go back to listing
if (!$product) {
    header('Location: products.php');
    exit;
}

// Determine stock status
if ($product['quantity'] <= 0) {
    $stockLabel = 'Out of Stock';
    $stockClass = 'out-stock';
} elseif ($product['quantity'] <= 10) {
    $stockLabel = 'Low Stock';
    $stockClass = 'low-stock';
} else {
    $stockLabel = 'In Stock';
    $stockClass = 'in-stock';
}
?>

<!-- ============================================================
     PRODUCT DETAILS VIEW
     ============================================================ -->
<div class="main-wrapper">

    <!-- Back link -->
    <a href="products.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>

    <div class="product-detail-layout">

        <!-- LEFT: Product Image -->
        <div class="product-detail-img">
            <?php if (!empty($product['image'])): ?>
                <img src="static/uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <img src="static/images/image3.jpg" alt="Product">
            <?php endif; ?>
        </div>

        <!-- RIGHT: Product Info -->
        <div class="product-detail-info">

            <!-- Category tag + Store name -->
            <div class="product-detail-meta">
                <span class="product-detail-tag">
                    <?php echo strtoupper($product['category']); ?>
                </span>
                <span class="product-detail-store">
                    <i class="fas fa-store"></i>
                    <!-- Link to the store -->
                    <a href="stores.php?id=<?php echo $product['store_id']; ?>">
                        <?php echo htmlspecialchars($product['store_name']); ?>
                    </a>
                </span>
            </div>

            <!-- Product Name -->
            <h1 class="product-detail-name">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <!-- Description -->
            <?php if (!empty($product['description'])): ?>
                <p class="product-detail-desc">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            <?php endif; ?>

            <!-- Stock Status -->
            <span class="product-detail-stock <?php echo $stockClass; ?>">
                <i class="fas fa-circle"></i>
                <?php echo $stockLabel; ?> (<?php echo $product['quantity']; ?> available)
            </span>

<!-- Price per 100g -->
<p class="product-detail-price">
    Rs. <?php echo number_format($product['price'] / 10, 2); ?> 
    <span>/ 100g</span>
</p>

<!-- Quantity Inputs -->
<div class="quantity-selector">
    <p class="qty-label">Quantity</p>
    <div class="qty-inputs-row">
        <div class="qty-input-wrap">
            <input type="number" id="qtyKg" min="0" value="0" placeholder="0" onchange="calcTotal()">
            <span class="qty-unit-label">kg</span>
        </div>
        <span class="qty-plus">+</span>
        <div class="qty-input-wrap">
            <input type="number" id="qtyG" min="0" value="0" placeholder="0" onchange="calcTotal()">
            <span class="qty-unit-label">g</span>
        </div>
    </div>
    <p class="total-price-label">Total: Rs. <span id="totalPrice">0.00</span></p>
</div>

<!-- Action Buttons -->
<?php if ($product['quantity'] > 0): ?>
    <div class="product-detail-btns">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
            <a href="cart.php?add=<?php echo $product['id']; ?>&qty=1"
               id="addToCartBtn"
               class="btn btn-outline add-to-cart-btn">
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </a>
            <a href="checkout.php?product=<?php echo $product['id']; ?>&qty=1"
               id="buyNowBtn"
               class="btn btn-primary buy-now-btn">
                <i class="fas fa-bolt"></i> Buy Now
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline add-to-cart-btn">
                <i class="fas fa-lock"></i> Login to Purchase
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="out-of-stock-msg">
        <i class="fas fa-times-circle"></i> This product is currently out of stock.
    </div>
<?php endif; ?>

        </div>
    </div>
</div>

<style>
    .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--primary); margin-bottom: 20px; }
    .back-link:hover { text-decoration: underline; }

    /* Detail Layout - image left, info right */
    .product-detail-layout {
        display: flex;
        gap: 48px;
        align-items: flex-start;
        background: #fff;
        border-radius: var(--radius-xl);
        padding: 32px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid var(--outline-variant);
        margin-bottom: 40px;
    }

    /* Product Image */
    .product-detail-img {
        flex: 0 0 420px;
        max-width: 420px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid var(--outline-variant);
    }
    .product-detail-img img { width: 100%; height: 360px; object-fit: cover; display: block; }

    /* Product Info */
    .product-detail-info { flex: 1; display: flex; flex-direction: column; gap: 16px; }

    /* Meta row - category tag + store */
    .product-detail-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .product-detail-tag {
        background: var(--primary);
        color: #fff;
        font-size: 11px; font-weight: 700;
        padding: 4px 12px;
        border-radius: var(--radius-full);
        letter-spacing: 0.05em;
    }
    .product-detail-store { font-size: 13px; color: var(--on-surface-variant); display: flex; align-items: center; gap: 5px; }
    .product-detail-store a { color: var(--primary); font-weight: 600; }
    .product-detail-store a:hover { text-decoration: underline; }

    /* Name */
    .product-detail-name { font-size: 2rem; font-weight: 700; color: var(--on-surface); line-height: 1.2; }

    /* Price */
    .product-detail-price { font-size: 1.8rem; font-weight: 700; color: var(--on-surface); }
    .product-detail-price span { font-size: 14px; font-weight: 400; color: var(--on-surface-variant); }

    /* Description */
    .product-detail-desc { font-size: 14px; color: var(--on-surface-variant); line-height: 1.7; }

    /* Stock status */
    .product-detail-stock { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .product-detail-stock i { font-size: 8px; }
    .product-detail-stock.in-stock  { color: #2d7a1f; }
    .product-detail-stock.low-stock { color: #b45309; }
    .product-detail-stock.out-stock { color: #dc2626; }

    /* Actions */
    .product-detail-actions { display: flex; flex-direction: column; gap: 16px; }

    /* Quantity Selector */
    .qty-label { font-size: 13px; font-weight: 600; color: var(--on-surface); margin-bottom: 8px; }
    .qty-controls {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid var(--outline-variant);
        border-radius: var(--radius-lg);
        width: fit-content;
        overflow: hidden;
    }
    .qty-btn {
        width: 38px; height: 38px;
        background: #f5f5f5;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: var(--on-surface);
        font-family: var(--font-family);
        transition: background 0.15s;
    }
    .qty-btn:hover { background: #e8e8e8; }
    #qtyDisplay { min-width: 48px; text-align: center; font-size: 15px; font-weight: 600; color: var(--on-surface); }
    .qty-inputs-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.qty-input-wrap { display: flex; align-items: center; gap: 8px; border: 1px solid var(--outline-variant); border-radius: var(--radius-lg); padding: 8px 14px; background: #fff; }
.qty-input-wrap input { border: none; outline: none; width: 60px; font-size: 15px; font-weight: 600; font-family: var(--font-family); color: var(--on-surface); }
.qty-unit-label { font-size: 14px; font-weight: 600; color: var(--on-surface-variant); }
.qty-plus { font-size: 18px; font-weight: 700; color: var(--on-surface-variant); }
.total-price-label { font-size: 15px; font-weight: 600; color: var(--on-surface); margin-top: 8px; }
.total-price-label span { color: var(--primary); font-size: 18px; }
    /* Buttons row */
    .product-detail-btns { display: flex; gap: 12px; flex-wrap: wrap; }
    .add-to-cart-btn { padding: 12px 24px; font-size: 14px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--outline-variant); }
    .buy-now-btn     { padding: 12px 24px; font-size: 14px; display: flex; align-items: center; gap: 8px; }

    /* Out of stock message */
    .out-of-stock-msg { font-size: 14px; color: #dc2626; display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #fef2f2; border-radius: var(--radius-lg); border: 1px solid #fecaca; }

    /* Mobile */
    @media (max-width: 768px) {
        .product-detail-layout { flex-direction: column; padding: 20px; gap: 24px; }
        .product-detail-img { flex: none; max-width: 100%; }
        .product-detail-img img { height: 250px; }
        .product-detail-name { font-size: 1.5rem; }
        .product-detail-price { font-size: 1.3rem; }
    }
</style>

<script>
const basePrice = <?php echo $product['price']; ?>; // price per kg

function calcTotal() {
    // Get values from both inputs
    const kg = parseFloat(document.getElementById('qtyKg').value) || 0;
    const g  = parseFloat(document.getElementById('qtyG').value)  || 0;

    // Convert everything to grams then calculate
    // 1 kg = 1000g
    const totalGrams = (kg * 1000) + g;
    const totalKg    = totalGrams / 1000;

    // Calculate total price
    const total = totalKg * basePrice;
    document.getElementById('totalPrice').textContent = total.toFixed(2);

    // Update cart/buy now button URLs
    updateBtns(totalGrams);
}

function updateBtns(totalGrams) {
    const addBtn = document.getElementById('addToCartBtn');
    const buyBtn = document.getElementById('buyNowBtn');
    if (addBtn) addBtn.href = `cart.php?add=<?php echo $product['id']; ?>&qty=${totalGrams}&unit=g`;
    if (buyBtn) buyBtn.href = `checkout.php?product=<?php echo $product['id']; ?>&qty=${totalGrams}&unit=g`;
}
</script>

<?php include 'includes/footer.php'; ?>