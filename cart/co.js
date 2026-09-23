/**
 * VerdantHub Single-Page Checkout Interactive Script
 * Pure Vanilla JavaScript (No Frameworks)
 */

document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const checkoutForm = document.getElementById('checkout-form');
    const formInputs = checkoutForm.querySelectorAll('input');
    const editAddressBtn = document.getElementById('edit-address-btn');
    
    const cartContainer = document.getElementById('cart-items-container');
    const subtotalEl = document.getElementById('subtotal-val');
    const deliveryEl = document.getElementById('delivery-val');
    const grandTotalEl = document.getElementById('grand-total-val');
    
    const toggleDeliveryBtn = document.getElementById('toggle-owner-delivery');
    const ownerDeliveryPanel = document.getElementById('owner-delivery-panel');
    const ownerDeliveryInput = document.getElementById('owner-delivery-input');
    const applyDeliveryBtn = document.getElementById('apply-delivery-btn');
    
    const placeOrderBtn = document.getElementById('place-order-btn');
    const orderModal = document.getElementById('order-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const modalDoneBtn = document.getElementById('modal-done-btn');
    const receiptOrderId = document.getElementById('receipt-order-id');
    const receiptAddress = document.getElementById('receipt-address');
    const receiptTotal = document.getElementById('receipt-total');

    let currentDeliveryCharge = 0.00;
    let isEditingAddress = true; // Enabled by default

    // ==========================================
    // 1. Calculation Functions
    // ==========================================

    /**
     * Recalculates Subtotal, Delivery Fee, and Grand Total
     */
    function updateTotals() {
        let subtotal = 0;
        const cartItems = cartContainer.querySelectorAll('.cart-item');

        cartItems.forEach(item => {
            const unitPrice = parseFloat(item.dataset.price);
            const qtyVal = parseInt(item.querySelector('.qty-val').textContent, 10);
            const itemTotalPrice = unitPrice * qtyVal;

            // Update individual item total display
            const itemTotalDisplay = item.querySelector('.item-total-price');
            if (itemTotalDisplay) {
                itemTotalDisplay.textContent = itemTotalPrice.toFixed(2);
            }

            subtotal += itemTotalPrice;
        });

        const grandTotal = subtotal + currentDeliveryCharge;

        // Update DOM displays
        subtotalEl.textContent = subtotal.toFixed(2);
        deliveryEl.textContent = currentDeliveryCharge.toFixed(2);
        grandTotalEl.textContent = grandTotal.toFixed(2);
    }

    // ==========================================
    // 2. Quantity Buttons (+ / -)
    // ==========================================

    cartContainer.addEventListener('click', (e) => {
        const qtyBtn = e.target.closest('.qty-btn');
        if (!qtyBtn) return;

        const action = qtyBtn.dataset.action;
        const cartItem = qtyBtn.closest('.cart-item');
        const qtyValSpan = cartItem.querySelector('.qty-val');
        let currentQty = parseInt(qtyValSpan.textContent, 10);

        if (action === 'increase') {
            currentQty += 1;
        } else if (action === 'decrease') {
            if (currentQty > 1) {
                currentQty -= 1;
            } else {
                // If 1, ask confirmation before removing or keep minimum 1
                const confirmRemove = confirm(`Remove ${cartItem.querySelector('.item-name').textContent} from your order?`);
                if (confirmRemove) {
                    cartItem.remove();
                }
            }
        }

        if (qtyValSpan && currentQty >= 1) {
            qtyValSpan.textContent = currentQty;
        }

        updateTotals();
    });

    // ==========================================
    // 3. Edit Address Toggle
    // ==========================================

    editAddressBtn.addEventListener('click', () => {
        isEditingAddress = !isEditingAddress;

        formInputs.forEach(input => {
            input.disabled = !isEditingAddress;
        });

        const btnText = editAddressBtn.querySelector('span');
        if (isEditingAddress) {
            btnText.textContent = 'Save';
            editAddressBtn.style.color = '#1B361B';
            formInputs[0].focus();
        } else {
            btnText.textContent = 'Edit';
            editAddressBtn.style.color = '#4B5563';
        }
    });

    // ==========================================
    // 4. Owner Custom Delivery Charge Panel
    // ==========================================

    toggleDeliveryBtn.addEventListener('click', () => {
        ownerDeliveryPanel.classList.toggle('hidden');
        if (!ownerDeliveryPanel.classList.contains('hidden')) {
            ownerDeliveryInput.focus();
        }
    });

    applyDeliveryBtn.addEventListener('click', () => {
        const customFee = parseFloat(ownerDeliveryInput.value);
        if (!isNaN(customFee) && customFee >= 0) {
            currentDeliveryCharge = customFee;
            updateTotals();
            ownerDeliveryPanel.classList.add('hidden');
        } else {
            alert('Please enter a valid delivery charge amount.');
        }
    });

    // Allow pressing "Enter" in delivery charge input
    ownerDeliveryInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyDeliveryBtn.click();
        }
    });

    // ==========================================
    // 5. Order Confirmation & Modal Logic
    // ==========================================

    placeOrderBtn.addEventListener('click', () => {
        // Validate form inputs
        let isValid = true;
        let firstInvalidInput = null;

        formInputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#EF4444';
                if (!firstInvalidInput) firstInvalidInput = input;
            } else {
                input.style.borderColor = 'transparent';
            }
        });

        if (!isValid) {
            alert('Please fill in all required delivery address fields.');
            if (firstInvalidInput) {
                // Ensure inputs are editable if disabled
                if (!isEditingAddress) {
                    editAddressBtn.click();
                }
                firstInvalidInput.focus();
            }
            return;
        }

        // Generate Random Order ID
        const randomOrderNum = Math.floor(10000 + Math.random() * 90000);
        receiptOrderId.textContent = `#VH-${randomOrderNum}`;

        // Get Address String
        const street = document.getElementById('address1').value.trim();
        const city = document.getElementById('city').value.trim();
        receiptAddress.textContent = `${street}, ${city}`;

        // Get Total
        receiptTotal.textContent = `Rs. ${grandTotalEl.textContent}`;

        // Show Modal
        orderModal.classList.remove('hidden');
    });

    // Close Modal Event Listeners
    closeModalBtn.addEventListener('click', () => {
        orderModal.classList.add('hidden');
    });

    modalDoneBtn.addEventListener('click', () => {
        orderModal.classList.add('hidden');
        // Smooth refresh/reset state if desired
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Close modal on outside click
    orderModal.addEventListener('click', (e) => {
        if (e.target === orderModal) {
            orderModal.classList.add('hidden');
        }
    });

    // Initialize Totals on Load
    updateTotals();
});
