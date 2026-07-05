/**
 * API client for AquaWatch Nairobi backend.
 */

const API = {
  baseUrl: (() => {
    const path = window.location.pathname;
    if (path.includes('/admin/') || path.includes('/pages/')) return '../api';
    if (path.includes('/database/')) return '../api';
    return 'api';
  })(),

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}/${endpoint}`;
    const config = {
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
      ...options,
    };
    if (config.body && typeof config.body === 'object') {
      config.body = JSON.stringify(config.body);
    }

    const response = await fetch(url, config);
    let data;
    try {
      data = await response.json();
    } catch {
      throw new Error('Invalid server response. Is Apache/MySQL running?');
    }

    if (!response.ok || data.success === false) {
      throw new Error(data.error || `Request failed (${response.status})`);
    }
    return data;
  },

  getCurrentUser() {
    return this.request('auth.php?action=me');
  },

  login(email, password) {
    return this.request('auth.php?action=login', {
      method: 'POST',
      body: { email, password },
    });
  },

  register(data) {
    return this.request('auth.php?action=register', {
      method: 'POST',
      body: data,
    });
  },

  logout() {
    return this.request('auth.php?action=logout', { method: 'POST', body: {} });
  },

  getNeighborhoods() {
    return this.request('neighborhoods.php');
  },

  getReports(filters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v !== '' && v != null) params.set(k, v);
    });
    const qs = params.toString();
    return this.request(`reports.php${qs ? '?' + qs : ''}`);
  },

  createReport(data) {
    return this.request('reports.php', { method: 'POST', body: data });
  },

  getNotifications() {
    return this.request('notifications.php');
  },

  getUnreadCount() {
    return this.request('notifications.php?action=unread-count');
  },

  getSubscriptions() {
    return this.request('notifications.php?action=subscriptions');
  },

  markNotificationRead(id) {
    return this.request('notifications.php', {
      method: 'PUT',
      body: { action: 'mark-read', id },
    });
  },

  markAllNotificationsRead() {
    return this.request('notifications.php', {
      method: 'PUT',
      body: { action: 'mark-all-read' },
    });
  },

  saveSubscriptions(neighborhoodIds) {
    return this.request('notifications.php', {
      method: 'PUT',
      body: { action: 'save-subscriptions', neighborhoodIds },
    });
  },

  getDashboard(neighborhoodId = '') {
    const qs = neighborhoodId ? `?neighborhoodId=${neighborhoodId}` : '';
    return this.request(`dashboard.php${qs}`);
  },

  adminGetReports(filters = {}) {
    const params = new URLSearchParams(filters);
    const qs = params.toString();
    return this.request(`admin/reports.php${qs ? '?' + qs : ''}`);
  },

  adminVerifyReport(id) {
    return this.request('admin/reports.php', {
      method: 'PUT',
      body: { id, action: 'verify' },
    });
  },

  adminDeleteReport(id) {
    return this.request(`admin/reports.php?id=${id}`, { method: 'DELETE' });
  },

  adminGetUsers(filters = {}) {
    const params = new URLSearchParams(filters);
    const qs = params.toString();
    return this.request(`admin/users.php${qs ? '?' + qs : ''}`);
  },

  adminCreateUser(data) {
    return this.request('admin/users.php', { method: 'POST', body: data });
  },

  adminSetUserStatus(id, action) {
    return this.request('admin/users.php', {
      method: 'PUT',
      body: { id, action },
    });
  },

  adminGetStats() {
    return this.request('admin/stats.php');
  },
};

const STATUS_LABELS = {
  available: 'Available',
  low: 'Low Pressure',
  none: 'No Water',
  scheduled: 'Scheduled',
};

const STATUS_BADGE_CLASS = {
  available: 'badge-available',
  low: 'badge-low',
  none: 'badge-none',
  scheduled: 'badge-scheduled',
};

/** Cached app state */
const AppState = {
  user: null,
  neighborhoods: [],
};
