<?php
// owner/order_modal.php - Standalone Component for Incoming Order Confirmation Popup

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/dbConnection.php';

$userId = $_SESSION['user_id'] ?? 0;

// Fetch owner's store ID
$stmtStore = $pdo->prepare("SELECT id FROM stores WHERE owner_id = ? LIMIT 1");
$stmtStore->execute([$userId]);
$storeRow = $stmtStore->fetch(PDO::FETCH_ASSOC);
$modalStoreId = $storeRow['id'] ?? 0;

// Handle POST actions for confirming or declining an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_action']) && $modalStoreId > 0) {
    $orderIdToUpdate = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['order_action'];

    if ($action === 'confirm_order' && $orderIdToUpdate > 0) {
        $deliveryFee = (float)($_POST['delivery_fee'] ?? 0);
        $newTotal = (float)($_POST['new_total'] ?? 0);

        $stmtUpdate = $pdo->prepare("
            UPDATE orders 
            SET status = 'confirmed', delivery_fee = ?, total_amount = ? 
            WHERE id = ? AND store_id = ?
        ");
        $stmtUpdate->execute([$deliveryFee, $newTotal, $orderIdToUpdate, $modalStoreId]);
        
        // ─── Notify the CUSTOMER that order is confirmed ───
        $stmtCustomerInfo = $pdo->prepare("
            SELECT o.customer_id, o.order_reference, o.delivery_fee, o.total_amount, o.delivery_address,
                   s.store_name
            FROM orders o
            JOIN stores s ON s.id = o.store_id
            WHERE o.id = ?
        ");
        $stmtCustomerInfo->execute([$orderIdToUpdate]);
        $orderInfo = $stmtCustomerInfo->fetch(PDO::FETCH_ASSOC);

        if ($orderInfo) {
            $custNotifTitle = "Order Approved & Confirmed!";
            $custNotifMsg   = "Your order #{$orderInfo['order_reference']} from {$orderInfo['store_name']} has been approved. "
                            . "Total: Rs. " . number_format($orderInfo['total_amount'], 2)
                            . ". We are preparing your fresh delivery.";

            $stmtCustNotif = $pdo->prepare("
                INSERT INTO notifications 
                    (user_id, title, priority, category, icon, message,
                     action_label, action_link, is_read, created_at)
                VALUES (?, ?, 'success', 'orders', 'fa-circle-check', ?,
                        'View Order Details', '/Smart_Greenhouse_Products_Marketplace/buyer/customer_dashboard.php', 0, NOW())
            ");
            $stmtCustNotif->execute([
                $orderInfo['customer_id'],
                $custNotifTitle,
                $custNotifMsg
            ]);
        }

        // Mark related notifications as read
        $stmtRef = $pdo->prepare("SELECT order_reference FROM orders WHERE id = ?");
        $stmtRef->execute([$orderIdToUpdate]);
        $orderRefVal = $stmtRef->fetchColumn();

        if ($orderRefVal) {
            $stmtNotifRead = $pdo->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? AND (title LIKE ? OR message LIKE ?)
            ");
            $likeParam = '%' . $orderRefVal . '%';
            $stmtNotifRead->execute([$userId, $likeParam, $likeParam]);
        }

        $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $redirectUrl);
        exit;

    } elseif ($action === 'decline_order' && $orderIdToUpdate > 0) {
        $stmtUpdate = $pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled' 
            WHERE id = ? AND store_id = ?
        ");
        $stmtUpdate->execute([$orderIdToUpdate, $modalStoreId]);

        $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Fetch targeted order: if $_GET['order_id'] is passed, fetch that order. Otherwise, fetch latest pending order.
$pendingOrder = null;
$pendingOrderItems = [];
$pendingSubtotal = 0;

if ($modalStoreId > 0) {
    $requestedOrderId = (int)($_GET['order_id'] ?? 0);

    if ($requestedOrderId > 0) {
        $stmtPendingOrder = $pdo->prepare("
            SELECT o.id, o.order_reference, o.created_at, o.delivery_fee, o.total_amount, o.delivery_address, o.status,
                   u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
            FROM orders o
            JOIN users u ON u.id = o.customer_id
            WHERE o.id = ? AND o.store_id = ?
            LIMIT 1
        ");
        $stmtPendingOrder->execute([$requestedOrderId, $modalStoreId]);
        $pendingOrder = $stmtPendingOrder->fetch(PDO::FETCH_ASSOC);
    } else {
        // Get dismissed order IDs from session
        $dismissedIds = $_SESSION['dismissed_orders'] ?? [];

        // Build the exclusion clause safely
        $excludeSql = '';
        if (!empty($dismissedIds)) {
            $placeholders = implode(',', array_fill(0, count($dismissedIds), '?'));
            $excludeSql = " AND o.id NOT IN ($placeholders)";
        }

        $stmtPendingOrder = $pdo->prepare("
            SELECT o.id, o.order_reference, o.created_at, o.delivery_fee, o.total_amount, o.delivery_address, o.status,
                   u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
            FROM orders o
            JOIN users u ON u.id = o.customer_id
            WHERE o.store_id = ? AND o.status = 'pending'" . $excludeSql . "
            ORDER BY o.created_at DESC
            LIMIT 1
        ");
        $params = array_merge([$modalStoreId], $dismissedIds);
        $stmtPendingOrder->execute($params);
        $pendingOrder = $stmtPendingOrder->fetch(PDO::FETCH_ASSOC);
    }

    if ($pendingOrder) {
        $stmtItems = $pdo->prepare("
            SELECT oi.quantity, oi.price_at_purchase, p.name, p.image, p.category
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$pendingOrder['id']]);
        $pendingOrderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pendingOrderItems as $item) {
            $pendingSubtotal += ($item['quantity'] * $item['price_at_purchase']);
        }
    }
}

// TEMP: fill dummy data if empty for design preview

if ($pendingOrder):
    $defaultFee = ($pendingOrder['delivery_fee'] > 0) ? (float)$pendingOrder['delivery_fee'] : 3.50;
    $initialTotal = $pendingSubtotal + $defaultFee;
?>

<!-- Order Modal Dedicated Stylesheet -->
<link rel="stylesheet" href="order_modal.css">

<!-- Incoming Order Confirmation Modal Component -->
<div class="incoming-order-overlay" id="incomingOrderModalOverlay">
    <div class="incoming-order-modal">
        <!-- Close X Button -->
        <form method="POST" action="" style="position:absolute; top:16px; right:18px; z-index:2;">
            <input type="hidden" name="order_id" value="<?= $pendingOrder['id'] ?>">
            <input type="hidden" name="order_action" value="dismiss_order">
            <button type="submit" class="modal-close-x" aria-label="Close Modal">&times;</button>
        </form>

        <!-- Top Badges Row -->
        <div class="incoming-modal-top-badges">
            <span class="badge-new-order">
                <span class="dot-pulse"></span> NEW CUSTOMER ORDER
            </span>
        </div>

        <h2 class="incoming-modal-title">Incoming Order Confirmation</h2>

        <!-- Customer Profile Card -->
        <div class="customer-info-box">
            <div class="customer-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="customer-details">
                <div class="customer-name-row">
                    <strong class="customer-name"><?= htmlspecialchars($pendingOrder['customer_name']) ?></strong>
                </div>
                <div class="customer-contact">
                    <span><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($pendingOrder['customer_phone'] ?? '+94 77 456 7890') ?></span>
                    <span>&nbsp;•&nbsp;</span>
                    <span><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($pendingOrder['customer_email']) ?></span>
                </div>
            </div>
        </div>

        <!-- Order Basket Section -->
        <div class="basket-section">
            <div class="basket-header-row">
                <span class="basket-header-title">
                    <i class="fa-solid fa-basket-shopping"></i> Order Basket Items (<?= count($pendingOrderItems) ?>)
                </span>
                <span class="gap-certified-badge">GAP CERTIFIED STOCK</span>
            </div>

            <div class="basket-items-list">
                <?php foreach ($pendingOrderItems as $item): ?>
                    <div class="basket-item-row">
                        <div class="basket-item-left">
                            <img src="<?= !empty($item['image']) ? '/Smart_Greenhouse_Products_Marketplace/' . htmlspecialchars($item['image']) : 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=100' ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" class="item-thumb">
                            <div class="item-info">
                                <strong class="item-name"><?= htmlspecialchars($item['name']) ?></strong>
                                <span class="item-qty-sub">Quantity: <?= (float)$item['quantity'] ?> × Rs. <?= number_format($item['price_at_purchase'], 2) ?></span>
                            </div>
                        </div>
                        <div class="basket-item-right">
                            <span class="item-line-total">Rs. <?= number_format($item['quantity'] * $item['price_at_purchase'], 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="subtotal-bar">
                <span>BASKET SUBTOTAL</span>
                <strong class="subtotal-amount">Rs. <?= number_format($pendingSubtotal, 2) ?></strong>
            </div>
        </div>

        <!-- Set Delivery Charge Section -->
        <div class="delivery-charge-card">
            <div class="delivery-card-title">
                <i class="fa-solid fa-truck"></i> Set Delivery Charge
            </div>

            <div class="form-group-delivery">
                <label for="deliveryFeeInput" class="delivery-label">Add Delivery Fee (Rs.)</label>
                <div class="delivery-input-group">
                    <span class="currency-symbol">Rs. </span>
                    <input type="number" step="0.01" min="0" id="deliveryFeeInput" name="delivery_fee" 
                           value="<?= number_format($defaultFee, 2, '.', '') ?>" class="delivery-fee-input">
                </div>
                <span class="delivery-help-text">Enter the custom delivery fee amount for this order.</span>
            </div>

            <div class="delivery-summary-box">
                <div class="summary-row">
                    <span>Items Subtotal</span>
                    <span>Rs. <?= number_format($pendingSubtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery & Packaging</span>
                    <span id="deliveryFeeDisplay">Rs. <?= number_format($defaultFee, 2) ?></span>
                </div>
                <div class="summary-row total-payable-row">
                    <strong>Total Payable by Customer</strong>
                    <strong id="totalPayableDisplay" class="total-payable-amount">Rs. <?= number_format($initialTotal, 2) ?></strong>
                </div>
            </div>
        </div>

        <!-- Bottom Actions Row -->
        <form method="POST" action="" id="orderConfirmForm">
            <input type="hidden" name="order_id" value="<?= $pendingOrder['id'] ?>">
            <input type="hidden" id="hiddenDeliveryFee" name="delivery_fee" value="<?= number_format($defaultFee, 2, '.', '') ?>">
            <input type="hidden" id="hiddenNewTotalInput" name="new_total" value="<?= number_format($initialTotal, 2, '.', '') ?>">
            <input type="hidden" name="order_action" id="orderActionInput" value="confirm_order">

            <div class="modal-action-row">
                <button type="submit" class="btn-decline-order" onclick="document.getElementById('orderActionInput').value='decline_order';">cancel Order</button>
                <button type="submit" class="btn-confirm-send" onclick="document.getElementById('orderActionInput').value='confirm_order';">
                    <i class="fa-solid fa-circle-check"></i> Confirm Order 
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.PENDING_ORDER_SUBTOTAL = <?= (float)$pendingSubtotal ?>;
</script>
<script src="order_modal.js"></script>

<?php endif; ?>
