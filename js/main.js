/**
 * Shared utilities for AquaWatch Nairobi frontend.
 */

const AquaWatch = {
  formatDate(isoString) {
    const date = new Date(isoString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;

    return date.toLocaleDateString("en-KE", {
      day: "numeric",
      month: "short",
      year: date.getFullYear() !== now.getFullYear() ? "numeric" : undefined,
      hour: "2-digit",
      minute: "2-digit",
    });
  },

  getInitials(name) {
    return name
      .split(" ")
      .map((part) => part[0])
      .join("")
      .toUpperCase()
      .slice(0, 2);
  },

  statusBadge(status) {
    const label = STATUS_LABELS[status] || status;
    const cls = STATUS_BADGE_CLASS[status] || "badge-neutral";
    return `<span class="badge ${cls}">${label}</span>`;
  },

  showAlert(container, message, type = "success") {
    if (!container) return;
    const alert = document.createElement("div");
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<span>${message}</span>`;
    alert.setAttribute("role", "alert");
    container.prepend(alert);
    setTimeout(() => alert.remove(), 5000);
  },

  initMobileNav() {
    const toggle = document.querySelector(".mobile-menu-toggle");
    const nav = document.querySelector(".main-nav");
    if (toggle && nav) {
      toggle.addEventListener("click", () => nav.classList.toggle("open"));
    }
  },

  initAdminSidebar() {
    const toggle = document.querySelector(".admin-mobile-toggle");
    const sidebar = document.querySelector(".admin-sidebar");
    if (toggle && sidebar) {
      toggle.addEventListener("click", () => sidebar.classList.toggle("open"));
    }
  },

  setActiveNav() {
    const path = window.location.pathname;
    const filename = path.split("/").pop() || "index.html";

    document.querySelectorAll(".main-nav a, .admin-nav a").forEach((link) => {
      const href = link.getAttribute("href");
      if (!href) return;
      const linkFile = href.split("/").pop();
      if (linkFile === filename || (filename === "" && linkFile === "index.html")) {
        link.classList.add("active");
      }
    });
  },

  initTabs(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const tabs = container.querySelectorAll(".tab");
    const panels = container.querySelectorAll(".tab-panel");

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const target = tab.dataset.tab;
        tabs.forEach((t) => t.classList.remove("active"));
        panels.forEach((p) => p.classList.remove("active"));
        tab.classList.add("active");
        const panel = container.querySelector(`#${target}`);
        if (panel) panel.classList.add("active");
      });
    });
  },

  getUnreadCount() {
    return MOCK_NOTIFICATIONS.filter((n) => !n.read).length;
  },

  updateNotificationBadge() {
    const badge = document.querySelector(".notification-bell .badge-count");
    const count = this.getUnreadCount();
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? "flex" : "none";
    }
  },

  populateNeighborhoodSelect(selectEl, selectedId = null) {
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">Select neighbourhood</option>';
    MOCK_NEIGHBORHOODS.forEach((n) => {
      const opt = document.createElement("option");
      opt.value = n.id;
      opt.textContent = `${n.name} (${n.area})`;
      if (selectedId && n.id === selectedId) opt.selected = true;
      selectEl.appendChild(opt);
    });
  },

  renderReportItem(report) {
    return `
      <article class="report-item" data-report-id="${report.id}">
        <div class="report-item-avatar">${this.getInitials(report.userName)}</div>
        <div class="report-item-content">
          <div class="report-item-header">
            <strong>${report.userName}</strong>
            ${this.statusBadge(report.status)}
            ${report.verified ? '<span class="badge badge-neutral">Verified</span>' : ""}
          </div>
          <div class="report-item-meta">${report.neighborhood} · ${this.formatDate(report.reportedAt)}</div>
          <p class="report-item-text">${report.notes}</p>
        </div>
      </article>
    `;
  },

  filterReports(reports, { neighborhoodId, status, search } = {}) {
    return reports.filter((r) => {
      if (neighborhoodId && r.neighborhoodId !== parseInt(neighborhoodId, 10)) return false;
      if (status && r.status !== status) return false;
      if (search) {
        const q = search.toLowerCase();
        const match =
          r.neighborhood.toLowerCase().includes(q) ||
          r.notes.toLowerCase().includes(q) ||
          r.userName.toLowerCase().includes(q);
        if (!match) return false;
      }
      return true;
    });
  },

  sortReportsByDate(reports) {
    return [...reports].sort((a, b) => new Date(b.reportedAt) - new Date(a.reportedAt));
  },

  computeStats(reports) {
    const total = reports.length;
    const byStatus = { available: 0, low: 0, none: 0, scheduled: 0 };
    reports.forEach((r) => {
      if (byStatus[r.status] !== undefined) byStatus[r.status]++;
    });
    const neighborhoods = new Set(reports.map((r) => r.neighborhoodId)).size;
    return { total, byStatus, neighborhoods };
  },

  addReport(report) {
    const neighborhood = MOCK_NEIGHBORHOODS.find((n) => n.id === parseInt(report.neighborhoodId, 10));
    const newReport = {
      ...report,
      id: Date.now(),
      userId: MOCK_CURRENT_USER.id,
      userName: MOCK_CURRENT_USER.name,
      neighborhood: neighborhood ? neighborhood.name : "Unknown",
      neighborhoodId: parseInt(report.neighborhoodId, 10),
      reportedAt: new Date().toISOString(),
      verified: false,
    };
    MOCK_REPORTS.unshift(newReport);
    return newReport;
  },

  getAllReports() {
    return this.sortReportsByDate(MOCK_REPORTS);
  },

  init() {
    this.initMobileNav();
    this.initAdminSidebar();
    this.setActiveNav();
    this.updateNotificationBadge();
  },
};

document.addEventListener("DOMContentLoaded", () => AquaWatch.init());
