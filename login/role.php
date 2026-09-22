<?php
session_start();

include '../includes/header.php';
?>

<!-- Role page specific styles -->
<link rel="stylesheet" href="role.css">

    <main class="page-container">
        <div class="auth-card active-card" id="reg-auth-card">
            <div class="card-left-panel">
                <div class="image-overlay"></div>
                <div class="panel-bottom-text">
                    <h1 class="hero-title">Grow your network.</h1>
                    <p class="hero-subtitle">Join the premier marketplace connecting commercial</p>
                </div>
            </div>

            <div class="card-right-panel">
                <div class="panel-main-content">
                    <div class="view-content active" id="role-selection-view">
                        <h2 class="form-title">Create Account</h2>
                        <p class="form-subtitle">Select your role to get started.</p>

                        <div class="role-cards-grid">
                            <a href="customer_register.php" class="role-card" id="card-customer">
                                <div class="card-header-row">
                                    <div class="icon-wrapper">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                            <line x1="3" y1="6" x2="21" y2="6"></line>
                                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                                        </svg>
                                    </div>
                                    <div class="radio-indicator"><div class="radio-dot"></div></div>
                                </div>
                                <h3 class="role-name">CUSTOMER / BUYER</h3>
                                <p class="role-description">I want to purchase organic products and supplies.</p>
                            </a>

                            <a href="owner_register.php" class="role-card" id="card-owner">
                                <div class="card-header-row">
                                    <div class="icon-wrapper">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 20h10"></path>
                                            <path d="M12 20v-8"></path>
                                            <path d="M12 12a5 5 0 0 1 5-5c0 4.5-3 8-5 8z"></path>
                                            <path d="M12 12a5 5 0 0 0-5-5c0 4.5 3 8 5 8z"></path>
                                        </svg>
                                    </div>
                                    <div class="radio-indicator"><div class="radio-dot"></div></div>
                                </div>
                                <h3 class="role-name">GREENHOUSE OWNER</h3>
                                <p class="role-description">I want to sell products and manage my inventory.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="rolee.js"></script>
    <?php include '../includes/footer.php'; ?>
