/**
 * API client for AquaWatch Nairobi backend.
 */

const API = {
  baseUrl: '/api',

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}/${endpoint}`;
    const method = (options.method || 'GET').toUpperCase();
    const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
    if (method !== 'GET') {
      const token = document.querySelector('meta[name="csrf-token"]');
      if (token) headers['X-CSRF-TOKEN'] = token.content;
    }
    const config = {
      credentials: 'same-origin',
      headers,
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
      throw new Error('Invalid server response. Is the Laravel server running?');
    }

    if (!response.ok || data.success === false) {
      throw new Error(data.error || `Request failed (${response.status})`);
    }
    return data;
  },

  getCurrentUser() {
    return this.request('auth/me');
  },

  login(email, password) {
    return this.request('auth/login', {
      method: 'POST',
      body: { email, password },
    });
  },

  register(data) {
    return this.request('auth/register', {
      method: 'POST',
      body: data,
    });
  },

  logout() {
    return this.request('auth/logout', { method: 'POST', body: {} });
  },

  getNeighborhoods() {
    return this.request('neighborhoods');
  },

  getReports(filters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v !== '' && v != null) params.set(k, v);
    });
    const qs = params.toString();
    return this.request(`reports${qs ? '?' + qs : ''}`);
  },

  createReport(data) {
    return this.request('reports', { method: 'POST', body: data });
  },

  getNotifications() {
    return this.request('notifications');
  },

  getUnreadCount() {
    return this.request('notifications/unread-count');
  },

  getSubscriptions() {
    return this.request('notifications/subscriptions');
  },

  markNotificationRead(id) {
    return this.request(`notifications/${id}/read`, { method: 'PUT', body: {} });
  },

  markAllNotificationsRead() {
    return this.request('notifications/mark-all-read', { method: 'PUT', body: {} });
  },

  saveSubscriptions(neighborhoodIds) {
    return this.request('notifications/subscriptions', {
      method: 'PUT',
      body: { neighborhoodIds },
    });
  },

  getDashboard(neighborhoodId = '') {
    const qs = neighborhoodId ? `?neighborhoodId=${neighborhoodId}` : '';
    return this.request(`dashboard${qs}`);
  },

  adminGetReports(filters = {}) {
    const params = new URLSearchParams(filters);
    const qs = params.toString();
    return this.request(`admin/reports${qs ? '?' + qs : ''}`);
  },

  adminVerifyReport(id) {
    return this.request(`admin/reports/${id}/verify`, { method: 'PUT', body: {} });
  },

  adminDeleteReport(id) {
    return this.request(`admin/reports/${id}`, { method: 'DELETE' });
  },

  adminGetUsers(filters = {}) {
    const params = new URLSearchParams(filters);
    const qs = params.toString();
    return this.request(`admin/users${qs ? '?' + qs : ''}`);
  },

  adminCreateUser(data) {
    return this.request('admin/users', { method: 'POST', body: data });
  },

  adminSetUserStatus(id, action) {
    return this.request(`admin/users/${id}/${action}`, { method: 'PUT', body: {} });
  },

  adminGetStats() {
    return this.request('admin/stats');
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
