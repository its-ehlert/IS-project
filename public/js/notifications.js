/**
 * Notifications page — alert list and subscription preferences.
 */

(function () {
  const listEl = document.getElementById('notifications-list');
  const unreadEl = document.getElementById('unread-count');
  const markAllBtn = document.getElementById('mark-all-read');
  const checkboxContainer = document.getElementById('neighborhood-checkboxes');
  const subscriptionForm = document.getElementById('subscription-form');

  const NOTIFICATION_ICONS = {
    info: 'ℹ️',
    success: '✅',
    warning: '⚠️',
    danger: '🚨',
  };

  function renderNotifications(notifications) {
    if (notifications.length === 0) {
      listEl.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">🔔</div>
          <h3>No notifications yet</h3>
          <p>Subscribe to neighbourhoods to receive supply change alerts.</p>
        </div>
      `;
      return;
    }

    listEl.innerHTML = notifications.map((n) => `
      <div class="notification-item ${n.read ? '' : 'unread'}" data-id="${n.id}" role="button" tabindex="0">
        <div class="notification-icon ${n.type}">${NOTIFICATION_ICONS[n.type] || 'ℹ️'}</div>
        <div>
          <strong>${AquaWatch.escapeHtml(n.title)}</strong>
          <p style="margin: 0.25rem 0 0; font-size: 0.9375rem; color: var(--color-text-muted);">${AquaWatch.escapeHtml(n.message)}</p>
          <span style="font-size: 0.8125rem; color: var(--color-text-muted);">${AquaWatch.formatDate(n.createdAt)}</span>
        </div>
      </div>
    `).join('');

    listEl.querySelectorAll('.notification-item').forEach((item) => {
      item.addEventListener('click', async () => {
        if (item.classList.contains('unread')) {
          try {
            await API.markNotificationRead(parseInt(item.dataset.id, 10));
            item.classList.remove('unread');
            updateUnreadBadge();
          } catch (e) {
            console.error(e);
          }
        }
      });
    });
  }

  async function updateUnreadBadge() {
    try {
      const data = await API.getUnreadCount();
      unreadEl.textContent = `${data.count} unread`;
      unreadEl.style.display = data.count > 0 ? 'inline-flex' : 'none';
      AquaWatch.updateNotificationBadge();
    } catch {
      unreadEl.style.display = 'none';
    }
  }

  async function renderSubscriptions() {
    await AquaWatch.loadNeighborhoods();
    let subs = [];
    try {
      const data = await API.getSubscriptions();
      subs = data.subscriptions;
    } catch {
      subs = [];
    }

    checkboxContainer.innerHTML = AppState.neighborhoods.map((n) => `
      <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer; font-size: 0.9375rem;">
        <input type="checkbox" name="neighborhood" value="${n.id}" ${subs.includes(n.id) ? 'checked' : ''}>
        ${AquaWatch.escapeHtml(n.name)}
      </label>
    `).join('');
  }

  markAllBtn.addEventListener('click', async () => {
    try {
      await API.markAllNotificationsRead();
      document.querySelectorAll('.notification-item.unread').forEach((el) => el.classList.remove('unread'));
      updateUnreadBadge();
    } catch (e) {
      alert(e.message);
    }
  });

  subscriptionForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const user = await AquaWatch.loadCurrentUser();
    if (!user) {
      alert('Please log in to save alert preferences.');
      window.location.href = 'login.html';
      return;
    }
    const checked = [...checkboxContainer.querySelectorAll('input[name="neighborhood"]:checked')]
      .map((cb) => parseInt(cb.value, 10));
    try {
      await API.saveSubscriptions(checked);
      alert('Alert preferences saved!');
    } catch (err) {
      alert(err.message);
    }
  });

  document.addEventListener('DOMContentLoaded', async () => {
    try {
      const user = await AquaWatch.loadCurrentUser();
      if (!user) {
        listEl.innerHTML = '<div class="empty-state"><p>Please <a href="login.html">log in</a> to view notifications.</p></div>';
        return;
      }
      await renderSubscriptions();
      const data = await API.getNotifications();
      renderNotifications(data.notifications);
      updateUnreadBadge();
    } catch (err) {
      listEl.innerHTML = `<div class="empty-state"><p>${err.message}</p></div>`;
    }
  });
})();
