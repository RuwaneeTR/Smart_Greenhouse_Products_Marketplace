<?php include 'includes/header.php'; ?>

<div class="main-wrapper">

    <!-- =====================================================
         HERO SECTION
         Search bar connects to stores.php
         Buttons connect to login/register pages
         ===================================================== -->
    <section class="hero">
        <div class="hero-content">
            <h1>Cultivating Life, <br> One Seed at a Time.</h1>
            <p>Join our thriving greenhouse marketplace. Discover premium plants, expert agronomy tips, and connect with local growers dedicated to sustainable vitality.</p>

            <!-- Search connects to stores.php with search term -->
            <form method="GET" action="stores.php" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search stores, plants, tips...">
            </form>

            <div class="hero-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- If logged in show browse buttons -->
                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                    <a href="stores.php" class="btn btn-outline">Find Stores</a>
                <?php else: ?>
                    <!-- If not logged in show login/signup -->
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-outline">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="static/images/image1.jpg" alt="Greenhouse">
        </div>
    </section>

    <!-- =====================================================
         EXPLORE SECTION (top row)
         Cards link to products and plants pages
         ===================================================== -->
    <section class="explore-section">
        <h2>Explore the Greenhouse</h2>
        <div class="card-grid">
            <!-- Links to plants page -->
            <a href="plants.php" class="card">
                <img src="static/images/image2.jpg" alt="Seed Germination">
                <div class="card-content">
                    <span class="card-tag">Featured Guide</span>
                    <h3>The Art of Seed Germination</h3>
                    <p>Master the basics for a thriving greenhouse harvest.</p>
                </div>
            </a>
            <!-- Links to products page -->
            <a href="products.php" class="card">
                <img src="static/images/image3.jpg" alt="Soil pH">
                <div class="card-content">
                    <h3>Managing Soil pH</h3>
                    <p>Optimize your soil for healthier, thriving greenhouse crops.</p>
                </div>
            </a>
        </div>
    </section>

    <!-- =====================================================
         CONNECT SECTION (bottom row)
         Find Stores links to stores.php
         ===================================================== -->
    <section class="connect-section">
        <a href="plants.php" class="card connect-card">
            <img src="static/images/image4.jpg" alt="Easy Composting">
            <div class="card-content">
                <h3>Easy Composting</h3>
                <p>Turn local kitchen waste into rich, organic fertilizer.</p>
            </div>
        </a>

        <div class="connect-text-box">
            <h2>Connect with Local Growers</h2>
            <p>Discover small-batch, sustainably grown plants directly from passionate agronomists in your area.</p>
            <a href="stores.php" class="link-arrow">Find Stores &rarr;</a>
        </div>

        <a href="products.php" class="card connect-card">
            <img src="static/images/image5.jpg" alt="Plant in hand">
        </a>
    </section><br>

</div>

<?php include 'includes/footer.php'; ?>