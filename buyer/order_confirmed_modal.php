<?php
// buyer/order_confirmed_modal.php - Popup shown when owner confirms an order

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/dbConnection.php';

$userId = $_SESSION['user_id'] ?? 0;
$modalOrder = null;
$modalItems = [];
$modalSubtotal = 0;

if ($userId > 0) {
    // Find latest UNREAD order confirmation notification
    $stmt = $pdo->prepare("
        SELECT id AS notif_id, message
        FROM notifications
        WHERE user_id = ?
          AND category = 'orders'
          AND is_read = 0
          AND title LIKE '%Approved%'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($notif) {
        // Extract order reference from message (pattern: #XXX-XXX)
        if (preg_match('/#([A-Z0-9\-]+)/i', $notif['message'], $m)) {
            $orderRef = $m[1];

            $stmtOrder = $pdo->prepare("
                SELECT o.id, o.order_reference, o.delivery_fee, o.total_amount, o.delivery_address,
                       s.store_name
                FROM orders o
                JOIN stores s ON s.id = o.store_id
                WHERE o.order_reference = ?
                LIMIT 1
            ");
            $stmtOrder->execute([$orderRef]);
            $modalOrder = $stmtOrder->fetch(PDO::FETCH_ASSOC);
            $modalOrder['notif_id'] = $notif['notif_id'];

            if ($modalOrder) {
                $stmtItems = $pdo->prepare("
                    SELECT oi.quantity, oi.price_at_purchase, p.name, p.image
                    FROM order_items oi
                    JOIN products p ON p.id = oi.product_id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$modalOrder['id']]);
                $modalItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                foreach ($modalItems as $item) {
                    $modalSubtotal += ($item['quantity'] * $item['price_at_purchase']);
                }
            }
        }
    }
}

if ($modalOrder):
?>
<link rel="stylesheet" href="order_confirmed_modal.css">

<div class="confirmed-overlay" id="confirmedModalOverlay">
    <div class="confirmed-modal">

        <button type="button" class="confirmed-close-x" id="closeConfirmedModal" aria-label="Close">&times;</button>

        <div class="confirmed-icon-wrap">
            <div class="confirmed-icon-circle">
                <i class="fa-solid fa-check"></i>
                <span class="sparkle sparkle-1"></span>
                <span class="sparkle sparkle-2"></span>
            </div>
        </div>

        <div class="confirmed-badge-row">
            <span class="confirmed-badge">
                <span class="badge-dot"></span> STORE REVIEW FINALIZED
            </span>
        </div>

        <h2 class="confirmed-title">Order Approved &amp; Confirmed!</h2>
        <p class="confirmed-sub">
            Order <strong>#<?= htmlspecialchars($modalOrder['order_reference']) ?></strong>
            · <?= htmlspecialchars($modalOrder['store_name'] ?? 'Greenhouse Store') ?>
        </p>

        <div class="confirmed-summary-card">
            <div class="confirmed-row">
                <span class="row-label">Items Subtotal (<?= count($modalItems) ?> items)</span>
                <span class="row-value">$<?= number_format($modalSubtotal, 2) ?></span>
            </div>
            <div class="confirmed-row">
                <span class="row-label">
                    Confirmed Delivery Fee
                    <span class="owner-added-tag">OWNER ADDED</span>
                </span>
                <span class="row-value">$<?= number_format($modalOrder['delivery_fee'], 2) ?></span>
            </div>
            <div class="confirmed-total-box">
                <div class="total-left">
                    <span class="total-label">TOTAL PAYABLE</span>
                    <span class="total-note">Includes local agronomic delivery &amp; tax</span>
                </div>
                <span class="total-amount">$<?= number_format($modalOrder['total_amount'], 2) ?></span>
            </div>
        </div>

        <div class="confirmed-address-box">
            <i class="fa-solid fa-location-dot address-icon"></i>
            <div>
                <span class="address-label">DELIVERY ADDRESS</span>
                <strong class="address-value"><?= htmlspecialchars($modalOrder['delivery_address'] ?? 'Your registered address') ?></strong>
            </div>
        </div>

        <button type="button" class="confirmed-view-btn" id="viewOrderDetailsBtn">
            <i class="fa-solid fa-list-ul"></i> View Order Details
        </button>

        <div class="confirmed-footer-row">
            <span><i class="fa-solid fa-shield-halved"></i> Encrypted Ag Escrow</span>
            <span><i class="fa-solid fa-leaf"></i> Freshness Guarantee Policy</span>
        </div>

    </div>
</div>

<script>
    window.CONFIRMED_NOTIF_ID = <?= (int)$modalOrder['notif_id'] ?>;
</script>
<script src="order_confirmed_modal.js"></script>

<?php endif; ?>