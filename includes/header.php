
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CropS - Smart Greenhouse Products Marketplace</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="/Smart_Greenhouse_Products_Marketplace/static/style_v2.css">
    </head>
<body>

<header>
    <a href="/Smart_Greenhouse_Products_Marketplace/index.php" class="logo">
        <img src="/Smart_Greenhouse_Products_Marketplace/static/images/logo.png" alt="CropS" class="logo-icon">
    </a>

    <nav class="nav-links">
        <a href="/Smart_Greenhouse_Products_Marketplace/index.php">Home</a>
        <a href="/Smart_Greenhouse_Products_Marketplace/stores.php">Stores</a>
        <div class="nav-dropdown-wrap">
            <a href="/Smart_Greenhouse_Products_Marketplace/products.php" class="nav-dropdown-trigger">
                Products <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <div class="nav-dropdown">
                <a href="/Smart_Greenhouse_Products_Marketplace/products.php" class="nav-dropdown-item">
                    <i class="fas fa-carrot"></i>
                    Vegetables & Fruits
                </a>
                <a href="/Smart_Greenhouse_Products_Marketplace/plants.php" class="nav-dropdown-item">
                    <i class="fas fa-seedling"></i>
                    Plants
                </a>
            </div>
        </div>
        <a href="#">Tips</a>
        <a href="#">About Us</a>
        <a href="/Smart_Greenhouse_Products_Marketplace/cart/cart.php"><i class="fas fa-shopping-cart"></i></a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if (($_SESSION['user_role'] ?? '') === 'owner'): ?>
                <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="btn btn-outline" style="padding: 5px 15px;">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            <?php elseif (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="/Smart_Greenhouse_Products_Marketplace/admin/admin_dashboard.php" class="btn btn-outline" style="padding: 5px 15px;">
                    <i class="fas fa-user-shield"></i> Profile
                </a>
            <?php else: ?>
                <a href="/Smart_Greenhouse_Products_Marketplace/buyer/customer_dashboard.php" class="btn btn-outline" style="padding: 5px 15px;">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            <?php endif; ?>

            <a href="/Smart_Greenhouse_Products_Marketplace/logout.php" style="color:red; font-weight:600;">Logout</a>

        <?php else: ?>
            <a href="/Smart_Greenhouse_Products_Marketplace/login/login.php" class="btn btn-primary" style="padding: 5px 15px; color: white;">Login</a>
        <?php endif; ?>
    </nav>
</header>