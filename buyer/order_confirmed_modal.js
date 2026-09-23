document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('confirmedModalOverlay');
    const closeBtn = document.getElementById('closeConfirmedModal');
    const viewBtn = document.getElementById('viewOrderDetailsBtn');

    if (!overlay) return;

    function markAsReadAndClose() {
        const notifId = window.CONFIRMED_NOTIF_ID || 0;
        if (notifId > 0) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: notifId })
            }).catch(() => {});
        }
        overlay.style.display = 'none';
    }

    closeBtn.addEventListener('click', markAsReadAndClose);

    viewBtn.addEventListener('click', () => {
        markAsReadAndClose();
        window.location.href = 'customer_dashboard.php';
    });
});