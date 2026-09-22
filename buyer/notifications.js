/**
 * CropS – Smart Greenhouse Products Marketplace (VerdantHub)
 * Notifications Script (notifications.js)
 */

// ─── Read data injected by PHP ───
const RAW_NOTIFICATIONS = window.CROPS_NOTIFICATIONS || [];
const CROPS_UNREAD      = window.CROPS_UNREAD || 0;

// Map DB fields → UI fields
const notificationsData = RAW_NOTIFICATIONS.map(n => ({
  id:          n.id,
  title:       n.title || 'Notification',
  desc:        n.message || '',
  time:        formatTime(n.created_at),
  category:    n.category || 'system',
  read:        parseInt(n.is_read) === 1,
  icon:        n.icon || 'fa-bell',
  iconTheme:   getIconTheme(n.priority || 'normal'),
  actions:     buildActions(n)
}));

// Format the timestamp like "2 hours ago" or "Oct 22, 2023"
function formatTime(dateStr) {
  if (!dateStr) return '';
  const d   = new Date(dateStr);
  const now = new Date();
  const diff = Math.floor((now - d) / 1000);

  if (diff < 60)      return 'Just now';
  if (diff < 3600)    return Math.floor(diff / 60) + ' min ago';
  if (diff < 86400)   return Math.floor(diff / 3600) + ' hrs ago';
  if (diff < 172800)  return 'Yesterday';
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Map DB priority → icon theme class
function getIconTheme(priority) {
  return {
    high:    'icon-delivery',
    normal:  'icon-store',
    info:    'icon-harvest',
    success: 'icon-completed'
  }[priority] || 'icon-delivery';
}

// Build the action buttons from DB action_label / action_link columns
function buildActions(n) {
  const actions = [];
  if (n.action_label && n.action_link) {
    actions.push({ text: n.action_label, type: 'primary', link: n.action_link });
  }
  if (n.action_label_2 && n.action_link_2) {
    actions.push({ text: n.action_label_2, type: 'outline', link: n.action_link_2 });
  }
  return actions;
}

let currentFilter = "all";


function renderNotifications() {
  const container = document.getElementById("notificationsList");
  if (!container) return;

  // Filter list according to active tab
  const filtered = notificationsData.filter(item => {
    if (currentFilter === "all") return true;
    if (currentFilter === "unread") return !item.read;
    if (currentFilter === "orders") return item.category === "orders";
    if (currentFilter === "harvests") return item.category === "harvests";
    if (currentFilter === "system") return item.category === "system";
    return true;
  });

  if (filtered.length === 0) {
    container.innerHTML = `
      <div style="background:#fff; padding:40px; border-radius:14px; text-align:center; color:#6b7280; border:1px solid #eaefe9;">
        <i class="far fa-bell-slash" style="font-size:32px; margin-bottom:10px; color:#9ca3af;"></i>
        <p style="font-weight:600;">No notifications found in this view.</p>
      </div>
    `;
  } else {
    container.innerHTML = filtered.map(item => `
      <div class="notification-card ${item.read ? 'read' : 'unread'}">
        <div class="notification-icon-wrapper ${item.iconTheme}">
          <i class="fas ${item.icon}"></i>
        </div>
        <div class="notification-body">
          <div class="notification-top-line">
            <div class="notification-title-group">
              ${!item.read ? '<span class="unread-dot"></span>' : ''}
              <h4 class="notification-title">${item.title}</h4>
            </div>
            <span class="notification-timestamp">${item.time}</span>
          </div>
          <p class="notification-desc">${item.desc}</p>
          <div class="notification-actions">
            ${item.actions.map(act => renderActionBtn(act)).join("")}
          </div>
        </div>
      </div>
    `).join("");
  }

  // Update showing counter text
  const countText = document.getElementById("showingCountText");
  if (countText) {
    countText.textContent = `Showing ${filtered.length} of ${notificationsData.length} notifications`;
  }

  // Update unread badges
  updateUnreadCount();
}

function renderActionBtn(act) {
  if (!act.link) return '';

  if (act.type === "primary") {
    return `<a href="${act.link}" class="btn-notif-primary">${act.text}</a>`;
  } else if (act.type === "outline") {
    return `<a href="${act.link}" class="btn-notif-outline">${act.text}</a>`;
  } else if (act.type === "gray") {
    return `<a href="${act.link}" class="btn-notif-gray">${act.text}</a>`;
  } else if (act.type === "link") {
    return `<a href="${act.link}" class="btn-notif-link">${act.text}</a>`;
  }
  return "";
}

function setupFilterTabs() {
  const tabsContainer = document.getElementById("filterTabs");
  if (!tabsContainer) return;

  const tabs = tabsContainer.querySelectorAll(".tab-pill");
  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      currentFilter = tab.getAttribute("data-filter");
      renderNotifications();
    });
  });
}



function updateUnreadCount() {
  const unreadCount = notificationsData.filter(i => !i.read).length;
  
  const headerBadge = document.getElementById("unreadBadgeHeader");
  const sidebarBadge = document.getElementById("sidebarUnreadBadge");

  if (headerBadge) {
    headerBadge.textContent = `${unreadCount} unread`;
    if (unreadCount === 0) {
      headerBadge.style.opacity = "0.6";
    }
  }

  if (sidebarBadge) {
    sidebarBadge.textContent = unreadCount;
    if (unreadCount === 0) {
      sidebarBadge.style.opacity = "0.5";
    }
  }
}
