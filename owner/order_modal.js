/**
 * CropS – Incoming Order Confirmation Modal Script
 */

document.addEventListener('DOMContentLoaded', () => {
    const deliveryFeeInput = document.getElementById('deliveryFeeInput');
    const deliveryFeeDisplay = document.getElementById('deliveryFeeDisplay');
    const totalPayableDisplay = document.getElementById('totalPayableDisplay');
    const btnTotalPill = document.getElementById('btnTotalPill');
    const hiddenDeliveryFee = document.getElementById('hiddenDeliveryFee');
    const hiddenNewTotalInput = document.getElementById('hiddenNewTotalInput');
    const closeOrderModalBtn = document.getElementById('closeOrderModalBtn');
    const incomingOrderModalOverlay = document.getElementById('incomingOrderModalOverlay');
    const contactBuyerBtn = document.getElementById('contactBuyerBtn');

    if (deliveryFeeInput) {
        deliveryFeeInput.addEventListener('input', () => {
            const fee = parseFloat(deliveryFeeInput.value) || 0;
            const subtotal = window.PENDING_ORDER_SUBTOTAL || 0;
            const total = subtotal + fee;

            const feeFormatted = `$${fee.toFixed(2)}`;
            const totalFormatted = `$${total.toFixed(2)}`;

            if (deliveryFeeDisplay) deliveryFeeDisplay.textContent = feeFormatted;
            if (totalPayableDisplay) totalPayableDisplay.textContent = totalFormatted;
            if (btnTotalPill) btnTotalPill.textContent = totalFormatted;
            if (hiddenDeliveryFee) hiddenDeliveryFee.value = fee.toFixed(2);
            if (hiddenNewTotalInput) hiddenNewTotalInput.value = total.toFixed(2);
        });
    }

    if (contactBuyerBtn) {
        contactBuyerBtn.addEventListener('click', () => {
            alert('Initiating call to customer...');
        });
    }
});
