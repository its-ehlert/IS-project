/**
 * Notifications page — alert list and subscription preferences.
 */

(function () {
  const listEl = document.getElementById("notifications-list");
  const unreadEl = document.getElementById("unread-count");
  const markAllBtn = document.getElementById("mark-all-read");
  const checkboxContainer = document.getElementById("neighborhood-checkboxes");
  const subscriptionForm = document.getElementById("subscription-form");

  const NOTIFICATION_ICONS = {
    info: "ℹ️",
    success: "✅",
    warning: "⚠️",
    danger: "🚨",
  };

  function getSubscriptions() {
    const stored = localStorage.getItem("aquawatch_subscriptions");
    if (stored) return JSON.parse(stored);
    return [MOCK_CURRENT_USER.neighborhoodId];
  }

  function saveSubscriptions(ids) {
    localStorage.setItem("aquawatch_subscriptions", JSON.stringify(ids));
  }

  function renderSubscriptions() {
    const subs = getSubscriptions();
    checkboxContainer.innerHTML = MOCK_NEIGHBORHOODS.map((n) => `
      <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer; font-size: 0.9375rem;">
        <input type="checkbox" name="neighborhood" value="${n.id}" ${subs.includes(n.id) ? "checked" : ""}>
        ${n.name}
      </label>
    `).join("");
  }

  function updateUnreadBadge() {
    const count = MOCK_NOTIFICATIONS.filter((n) => !n.read).length;
    unreadEl.textContent = `${count} unread`;
    unreadEl.style.display = count > 0 ? "inline-flex" : "none";
    AquaWatch.updateNotificationBadge();
  }

  function renderNotifications() {
    const sorted = [...MOCK_NOTIFICATIONS].sort(
      (a, b) => new Date(b.createdAt) - new Date(a.createdAt)
    );

    if (sorted.length === 0) {
      listEl.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">🔔</div>
          <h3>No notifications yet</h3>
          <p>Subscribe to neighbourhoods to receive supply change alerts.</p>
        </div>
      `;
      return;
    }

    listEl.innerHTML = sorted.map((n) => `
      <div class="notification-item ${n.read ? "" : "unread"}" data-id="${n.id}" role="button" tabindex="0">
        <div class="notification-icon ${n.type}">${NOTIFICATION_ICONS[n.type] || "ℹ️"}</div>
        <div>
          <strong>${n.title}</strong>
          <p style="margin: 0.25rem 0 0; font-size: 0.9375rem; color: var(--color-text-muted);">${n.message}</p>
          <span style="font-size: 0.8125rem; color: var(--color-text-muted);">${AquaWatch.formatDate(n.createdAt)}</span>
        </div>
      </div>
    `).join("");

    listEl.querySelectorAll(".notification-item").forEach((item) => {
      item.addEventListener("click", () => {
        const id = parseInt(item.dataset.id, 10);
        const notification = MOCK_NOTIFICATIONS.find((n) => n.id === id);
        if (notification && !notification.read) {
          notification.read = true;
          item.classList.remove("unread");
          updateUnreadBadge();
        }
      });
    });

    updateUnreadBadge();
  }

  markAllBtn.addEventListener("click", () => {
    MOCK_NOTIFICATIONS.forEach((n) => { n.read = true; });
    renderNotifications();
  });

  subscriptionForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const checked = [...checkboxContainer.querySelectorAll('input[name="neighborhood"]:checked')]
      .map((cb) => parseInt(cb.value, 10));
    saveSubscriptions(checked);
    alert("Alert preferences saved! (Demo — stored in browser localStorage)");
  });

  document.addEventListener("DOMContentLoaded", () => {
    renderSubscriptions();
    renderNotifications();
  });
})();
