/**
 * CropS – Owner Notifications Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Filter Tabs Functionality
    const filterTabs = document.querySelectorAll('.filter-tab');
    const notifCards = document.querySelectorAll('.notif-card');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Active tab styling
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filterValue = tab.getAttribute('data-filter');

            notifCards.forEach(card => {
                const category = card.getAttribute('data-category');
                const isRead = card.getAttribute('data-read');

                if (filterValue === 'all') {
                    card.style.display = 'flex';
                } else if (filterValue === 'unread') {
                    card.style.display = (isRead === '0') ? 'flex' : 'none';
                } else {
                    card.style.display = (category === filterValue) ? 'flex' : 'none';
                }
            });
        });
    });

    // 2. Click to Mark Single Notification as Read
    notifCards.forEach(card => {
        card.addEventListener('click', (e) => {
            // Ignore click if clicking an action button link
            if (e.target.closest('a') || e.target.closest('button')) return;

            if (card.getAttribute('data-read') === '0') {
                card.setAttribute('data-read', '1');
                card.classList.remove('unread-card');

                const unreadDot = card.querySelector('.unread-indicator-dot');
                if (unreadDot) {
                    unreadDot.remove();
                }
            }
        });
    });

    // 3. Mobile Sidebar Drawer Controls
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
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

    // 4. Logout Modal Controls
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
});
