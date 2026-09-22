/**
 * CropS – Owner Dashboard Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar & Drawer Controls
    const mobileMenuBtn = document.getElementById('mobileMenuBtn'); // Hamburger button in navbar (if present)
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function toggleSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.toggle('active');
            sidebarBackdrop.classList.toggle('active');
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', toggleSidebar);
    }

    // 2. Sidebar Link Active Highlighting
    const sidebarLinks = document.querySelectorAll('.sidebar-link:not(.logout-link)');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            sidebarLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Close sidebar on mobile after clicking link
            if (window.innerWidth <= 992 && sidebar && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    });

    // 3. Logout Modal Functionality
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

    if (logoutBtn && logoutModal) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logoutModal.classList.add('active');
        });
    }

    if (cancelLogoutBtn && logoutModal) {
        cancelLogoutBtn.addEventListener('click', () => {
            logoutModal.classList.remove('active');
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                logoutModal.classList.remove('active');
            }
        });
    }

    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', () => {
            window.location.href = '/Smart_Greenhouse_Products_Marketplace/logout.php';
        });
    }

    // 4. Quick Add Product Button Navigation
    const quickAddProductBtn = document.getElementById('quickAddProductBtn');
    if (quickAddProductBtn && quickAddProductBtn.tagName === 'BUTTON') {
        quickAddProductBtn.addEventListener('click', () => {
            window.location.href = '/Smart_Greenhouse_Products_Marketplace/owner/add_product.php';
        });
    }
});
