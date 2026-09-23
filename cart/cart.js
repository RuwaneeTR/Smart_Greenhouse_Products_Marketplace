// VerdantHub Cart Application Logic
const initialCartStores = [
  {
    storeId: 'oakridge',
    storeName: 'Oakridge Botanicals',
    shipLocation: 'SHIPS FROM PORTLAND, OR',
    items: [
      {
        id: 'item-1',
        name: 'Organic Vine-Ripened Tomatoes',
        description: 'Fresh organic vine-ripened tomatoes in a small wooden crate.',
        category: 'Vegetable',
        unit: '1 lb',
        inStock: true,
        sku: 'ORG-TOM-01',
        price: 12.50,
        quantity: 1,
        image: 'assets/tomatoes.jpg'
      }
    ]
  },
  {
    storeId: 'valley',
    storeName: 'Valley Hydroponics',
    shipLocation: 'SHIPS FROM DENVER, CO',
    items: [
      {
        id: 'item-2',
        name: 'Sugar Pie Pumpkin',
        description: 'A whole organic sugar pie pumpkin on a rustic wooden surface.',
        category: 'Vegetable',
        unit: '1 item',
        inStock: true,
        sku: 'PUM-SUG-01',
        price: 8.00,
        quantity: 1,
        image: 'assets/pumpkin.jpg'
      },
      {
        id: 'item-3',
        name: 'Fresh Basil Pot',
        description: 'A lush, vibrant green basil plant in a simple terracotta pot.',
        category: 'Plant',
        unit: '1 pot',
        inStock: true,
        sku: 'HERB-BAS-01',
        price: 15.00,
        quantity: 1,
        image: 'assets/basil.jpg'
      }
    ]
  }
];
// Deep clone initial data for mutable cart state
let cartStores = JSON.parse(JSON.stringify(initialCartStores));
// DOM Elements
const cartItemsContainer = document.getElementById('cart-items-container');
const cartGrid = document.getElementById('cart-grid');
const emptyCartState = document.getElementById('empty-cart-state');
const headerItemCount = document.getElementById('header-item-count');
const summarySubtotalLabel = document.getElementById('summary-subtotal-label');
const summarySubtotalValue = document.getElementById('summary-subtotal-value');
const summaryTotalPrice = document.getElementById('summary-total-price');
const checkoutBtn = document.getElementById('checkout-btn');
const resetCartBtn = document.getElementById('reset-cart-btn');
const toastContainer = document.getElementById('toast-container');

// SVG Icons
const icons = {
  store: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`,
  tag: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>`,
  check: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
  trash: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`
};
// Initialize Application
function initApp() {
  renderCart();
  setupEventListeners();
}
// Render entire cart interface
function renderCart() {
  const activeStores = cartStores.filter(store => store.items.length > 0);

  if (activeStores.length === 0) {
    cartGrid.style.display = 'none';
    emptyCartState.style.display = 'block';
    headerItemCount.textContent = '0 items';
    updateSummary(0, 0);
    return;
  }
  cartGrid.style.display = 'grid';
  emptyCartState.style.display = 'none';

  let html = '';
  let totalItemQuantity = 0;
  let subtotalAmount = 0;
  
  activeStores.forEach(store => {
    html += `
      <div class="store-card" id="store-card-${store.storeId}">
        <div class="store-header">
          <div class="store-icon-wrap">
            ${icons.store}
          </div>
          <div class="store-info">
            <span class="store-name">${escapeHtml(store.storeName)}</span>
            <span class="store-shipping">${escapeHtml(store.shipLocation)}</span>
          </div>
        </div>
        <div class="store-items-list">
    `;
    store.items.forEach(item => {
      const itemTotal = item.price * item.quantity;
      subtotalAmount += itemTotal;
      totalItemQuantity += item.quantity;
    

       html += `
        <div class="cart-item-row" id="cart-item-${item.id}">
          <div class="item-img-container">
            <img src="${item.image}" alt="${escapeHtml(item.name)}" class="item-img" onerror="this.src='https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=200&auto=format&fit=crop&q=80'">
          </div>
          <div class="item-details">
            <div>
              <div class="item-top-row">
                <h3 class="item-name">${escapeHtml(item.name)}</h3>
                <div class="item-price-unit">Rs. ${item.price.toFixed(2)}</div>
              </div>
              <p class="item-desc">${escapeHtml(item.description)}</p>
              
              <div class="item-badges">
                <span class="badge badge-unit">${icons.tag} ${escapeHtml(item.unit)}</span>
                ${item.inStock ? `<span class="badge badge-stock">${icons.check} In Stock</span>` : ''}
                <span class="badge badge-category">${escapeHtml(item.category)}</span>
              </div>
              
              <div class="item-sku">SKU: ${escapeHtml(item.sku)}</div>
            </div>
            <div class="item-bottom-row">
              <div class="quantity-control">
                <button class="qty-btn" onclick="updateQuantity('${item.id}', -1)" ${item.quantity <= 1 ? 'disabled' : ''} aria-label="Decrease quantity">-</button>
                <span class="qty-display">${item.quantity}</span>
                <button class="qty-btn" onclick="updateQuantity('${item.id}', 1)" aria-label="Increase quantity">+</button>
              </div>
              <button class="remove-btn" onclick="removeItem('${item.id}')" aria-label="Remove item">
                ${icons.trash}
                <span>Remove</span>
              </button>
            </div>
          </div>
        </div>
      `;
    });
    html += `
        </div>
      </div>
    `;
  });
  cartItemsContainer.innerHTML = html;
  
  // Update totals and header counts
  const itemText = totalItemQuantity === 1 ? '1 item' : `${totalItemQuantity} items`;
  headerItemCount.textContent = itemText;
  updateSummary(totalItemQuantity, subtotalAmount);
}
// Update Order Summary card values
function updateSummary(totalQuantity, subtotal) {
  const itemText = totalQuantity === 1 ? '1 item' : `${totalQuantity} items`;
  summarySubtotalLabel.textContent = `Subtotal (${itemText})`;
  summarySubtotalValue.textContent = `Rs. ${subtotal.toFixed(2)}`;
  summaryTotalPrice.textContent = `Rs. ${subtotal.toFixed(2)}`;
}
// Update item quantity
function updateQuantity(itemId, change) {
  for (let store of cartStores) {
    let item = store.items.find(i => i.id === itemId);
    if (item) {
      const newQty = item.quantity + change;
      if (newQty >= 1) {
        item.quantity = newQty;
        renderCart();
      }
      break;
    }
  }
}
// Remove item from cart
function removeItem(itemId) {
  let removedItemName = '';
  cartStores.forEach(store => {
    const itemIndex = store.items.findIndex(i => i.id === itemId);
    if (itemIndex > -1) {
      removedItemName = store.items[itemIndex].name;
      store.items.splice(itemIndex, 1);
    }
  });
  renderCart();
  showToast(`Removed "${removedItemName}" from cart`);
}
// Reset cart to initial state
function resetCart() {
  cartStores = JSON.parse(JSON.stringify(initialCartStores));
  renderCart();
  showToast('Cart items restored');
}
// Show Toast message
function showToast(message) {
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
    <span>${escapeHtml(message)}</span>
  `;
  toastContainer.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}
// Event Listeners
function setupEventListeners() {
  checkoutBtn.addEventListener('click', () => {
    let totalAmount = summaryTotalPrice.textContent;
    showToast(`Proceeding to checkout (${totalAmount})...`);
  });
  if (resetCartBtn) {
    resetCartBtn.addEventListener('click', resetCart);
  }
}
// Utility: HTML Escaping
function escapeHtml(str) {
  return str.replace(/[&<>"']/g, match => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  })[match]);
}
// Global functions attached to window for HTML onclick access
window.updateQuantity = updateQuantity;
window.removeItem = removeItem;
// Run on DOM ready
document.addEventListener('DOMContentLoaded', initApp);
