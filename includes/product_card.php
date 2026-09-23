<?php
// ============================================================
// includes/product_card.php - Reusable Product Card
// $product variable must be set before including this file
// Used in: stores.php, products.php, plants.php
// Automatically links to correct detail page based on category
// ============================================================

// Determine stock status
if ($product['quantity'] <= 0) {
    $stockLabel = 'OUT OF STOCK';
    $stockClass = 'out-stock';
} elseif ($product['quantity'] <= 10) {
    $stockLabel = 'LOW STOCK';
    $stockClass = 'low-stock';
} else {
    $stockLabel = 'IN STOCK';
    $stockClass = 'in-stock';
}

// Plants link to plants.php, everything else to products.php
$detailPage = $product['category'] === 'plant' ? 'plants.php' : 'products.php';
?>
<a href="<?php echo $detailPage; ?>?id=<?php echo $product['id']; ?>" class="product-card">
    <div class="product-card-img">
        <?php if (!empty($product['image'])): ?>
            <img src="static/uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                 alt="<?php echo htmlspecialchars($product['name']); ?>">
        <?php else: ?>
            <img src="static/images/image3.jpg" alt="Product">
        <?php endif; ?>
        <span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockLabel; ?></span>
    </div>
    <div class="product-card-info">
        <p class="product-category-label"><?php echo strtoupper($product['category']); ?></p>
        <p class="product-name"><?php echo htmlspecialchars($product['name']); ?></p>
        <?php if ($product['category'] === 'plant'): ?>
            <!-- Plants show price per plant -->
            <p class="product-price">Rs. <?php echo number_format($product['price'], 2); ?> <span>/ plant</span></p>
        <?php else: ?>
            <!-- Veg & fruits show price per 100g -->
            <p class="product-price">Rs. <?php echo number_format($product['price'] / 10, 2); ?> <span>/ 100g</span></p>
        <?php endif; ?>
    </div>
</a>