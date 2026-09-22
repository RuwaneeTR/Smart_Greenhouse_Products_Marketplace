/**
 * CropS – Smart Greenhouse Products Marketplace (VerdantHub)
 * Customer Dashboard Script (customer_dashboard.js)
 */


// ─── Read data injected by PHP ───
const CROPS = window.CROPS_DATA || {};
const buyerData           = CROPS.buyer || {};
const recommendedProducts = CROPS.recommendedProducts || [];
const recentOrders        = CROPS.recentOrders || [];
const updatesData         = CROPS.updates || [];

document.addEventListener("DOMContentLoaded", () => {
  // 1. Sync User Profile Data (from localStorage if available)
  loadUserProfile();

  // 2. Render Recommended Products
  renderRecommendedProducts();

  // 3. Render Recent Orders Table
  renderRecentOrders();

  // 4. Render Updates Panel
  renderUpdates();

  // 5. Setup Interactions
  setupInteractions();
});

function loadUserProfile() {
  const savedProfile = localStorage.getItem("verdantHubBuyer");
  const user = savedProfile ? JSON.parse(savedProfile) : buyerData;

  const headerName = document.getElementById("headerUserName");
  const sidebarName = document.getElementById("sidebarUserName");
  const welcomeName = document.getElementById("welcomeUserName");
  const sidebarSub = document.getElementById("sidebarUserSubtitle");
  const sidebarAvatar = document.getElementById("sidebarAvatar");

  if (headerName) headerName.textContent = user.name;
  if (sidebarName) sidebarName.textContent = user.name;
  if (welcomeName) welcomeName.textContent = user.name.split(" ")[0];
  if (sidebarSub) sidebarSub.textContent = `${user.role} • ${user.zone}`;
  if (sidebarAvatar) sidebarAvatar.textContent = user.initials || "AM";
}

function renderRecommendedProducts() {
  const container = document.getElementById("productsGrid");
  if (!container) return;

  if (!recommendedProducts.length) {
    container.innerHTML = '<p style="color:#8a948c;">No products available yet.</p>';
    return;
  }

  container.innerHTML = recommendedProducts.map(product => `
    <div class="product-card">
      <div class="product-image-wrap">
        <img src="/Smart_Greenhouse_Products_Marketplace/uploads/${product.image}" 
             alt="${product.name}"
             onerror="this.src='https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400'">
        <span class="product-badge">
          <i class="fas fa-certificate"></i> ${product.category}
        </span>
      </div>
      <div class="product-body">
        <div class="product-title-row">
          <h4 class="product-title">${product.name}</h4>
          <span class="product-price">Rs. ${parseFloat(product.price).toFixed(2)}</span>
        </div>
        <p class="product-desc">${product.description || ''}</p>
        <div class="product-footer">
          <span class="store-info">
            <i class="fas fa-store"></i> ${product.store || 'Greenhouse'}
          </span>
          <button class="btn-add" onclick="addToCart(${product.id}, '${product.name}')">
            <i class="fas fa-shopping-bag"></i> Add
          </button>
        </div>
      </div>
    </div>
  `).join("");
}

function renderRecentOrders() {
  const tbody = document.getElementById("ordersTbody");
  if (!tbody) return;

  tbody.innerHTML = recentOrders.map(order => {
    const isTransit = order.status === "In Transit";
    const statusClass = isTransit ? "status-transit" : "status-delivered";
    const statusIcon = isTransit ? '<span class="status-dot"></span>' : '<i class="fas fa-check"></i>';

    return `
      <tr>
        <td class="order-id">${order.id}</td>
        <td class="order-items">${order.items}</td>
        <td>${order.date}</td>
        <td>
          <span class="status-pill ${statusClass}">
            ${statusIcon} ${order.status}
          </span>
        </td>
        <td class="order-total">${order.total}</td>
      </tr>
    `;
  }).join("");
}

function renderUpdates() {
  const list = document.getElementById("updatesList");
  if (!list) return;

  list.innerHTML = updatesData.map(update => `
    <div class="update-item">
      <div class="update-icon"><i class="fas ${update.icon}"></i></div>
      <div class="update-content">
        <p class="update-text">${update.text}</p>
        <span class="update-time">${update.time}</span>
      </div>
    </div>
  `).join("");
}

function setupInteractions() {
  const markReadBtn = document.getElementById("markAllReadLink");
  if (markReadBtn) {
    markReadBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const badge = document.getElementById("sidebarUnreadBadge");
      if (badge) {
        badge.textContent = "0";
        badge.style.opacity = "0.5";
      }
      alert("All updates marked as read!");
    });
  }
}

function addToCart(productId, productName) {
  alert(`"${productName}" (ID: ${productId}) will be added to your cart.`);
  // Later this will redirect to:
  // window.location.href = 'cart/cart.php?add=' + productId;
}