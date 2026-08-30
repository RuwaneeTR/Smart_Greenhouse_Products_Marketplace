<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> CropS - Smart Greenhouse Products Marketplace </title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel ="stylesheet" href="static/style.css">
    </head>
    <body>
    <!-- Navigation Bar -->
    <header>
        <a href="index.php" class="logo">
            <img src="static/images/logo.png" alt="CropS" class="logo-icon">
        </a>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="stores.php">Stores</a>

            <!-- -----------------------------------------------
                 PRODUCTS DROPDOWN
                 position:relative on the wrapper so the dropdown
                 positions itself below the Products link
                 ----------------------------------------------- -->
            <div class="nav-dropdown-wrap">
                <a href="#" class="nav-dropdown-trigger">
                    Products <i class="fas fa-chevron-down nav-chevron"></i>
                </a>
                <div class="nav-dropdown">
                    <a href="products.php" class="nav-dropdown-item">
                        <i class="fas fa-carrot"></i>
                        Vegetables & Fruits
                    </a>
                    <a href="plants.php" class="nav-dropdown-item">
                        <i class="fas fa-seedling"></i>
                        Plants
                    </a>
                </div>
            </div>

            <a href="#">Tips</a>
            <a href="#">About Us</a>
            <a href="#"><i class="fas fa-shopping-cart"></i></a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" class="btn btn-outline" style="padding: 5px 15px;">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <a href="logout.php" style="color:red; font-weight:600;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="padding: 5px 15px; color: white;">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Dropdown CSS + JS injected here so it's available on every page -->
    <style>
        /* Dropdown Wrapper */
        .nav-dropdown-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Trigger link */
        .nav-dropdown-trigger {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.05em;
            color: var(--on-surface);
        }

        /* Chevron icon rotates when dropdown is open */
        .nav-chevron {
            font-size: 10px;
            transition: transform 0.2s;
        }
        .nav-dropdown-wrap:hover .nav-chevron {
            transform: rotate(180deg);
        }

        /* Dropdown Menu */
        .nav-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border: 1px solid var(--outline-variant);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
            min-width: 180px;
            z-index: 1000;
            overflow: hidden;
            /* Hidden by default */
            opacity: 0;
            visibility: hidden;
            transform: translateX(-50%) translateY(-8px);
            transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
        }

        /* Show dropdown on hover */
        .nav-dropdown-wrap:hover .nav-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* Dropdown Items */
        .nav-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: var(--on-surface);
            transition: background 0.15s, color 0.15s;
            border-bottom: 1px solid var(--outline-variant);
        }
        .nav-dropdown-item:last-child { border-bottom: none; }
        .nav-dropdown-item:hover {
            background: #f0f7ee;
            color: var(--primary);
        }
        .nav-dropdown-item i {
            color: var(--primary);
            font-size: 14px;
            width: 16px;
        }
    </style>