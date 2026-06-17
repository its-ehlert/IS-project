/**
 * Admin panel logic — report management, users, monitoring.
 */

const Admin = {
  getNeighborhoodName(id) {
    const n = MOCK_NEIGHBORHOODS.find((nb) => nb.id === id);
    return n ? n.name : "—";
  },

  initDashboard() {
    const reports = AquaWatch.getAllReports();
    const pending = reports.filter((r) => !r.verified);
    const activeUsers = MOCK_USERS.filter((u) => u.status === "active").length;

    document.getElementById("admin-stats").innerHTML = `
      <div class="stat-card">
        <div class="stat-label">Total reports</div>
        <div class="stat-value">${reports.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pending verification</div>
        <div class="stat-value" style="color: var(--status-low);">${pending.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Registered users</div>
        <div class="stat-value">${MOCK_USERS.length}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Active users</div>
        <div class="stat-value" style="color: var(--status-available);">${activeUsers}</div>
      </div>
    `;

    const pendingEl = document.getElementById("pending-reports");
    if (pending.length === 0) {
      pendingEl.innerHTML = '<div class="empty-state"><p>All reports verified.</p></div>';
    } else {
      pendingEl.innerHTML = pending.slice(0, 5).map((r) => AquaWatch.renderReportItem(r)).join("");
    }

    const recentUsers = document.getElementById("recent-users");
    recentUsers.innerHTML = MOCK_USERS.slice(0, 5).map((u) => `
      <tr>
        <td><strong>${u.name}</strong></td>
        <td>${u.email}</td>
        <td>${u.neighborhoodId ? this.getNeighborhoodName(u.neighborhoodId) : "—"}</td>
        <td><span class="badge ${u.status === "active" ? "badge-available" : "badge-none"}">${u.status}</span></td>
        <td>${u.joinedAt}</td>
      </tr>
    `).join("");

    const ctx = document.getElementById("activity-chart");
    if (ctx) {
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: MOCK_TREND_DATA.labels,
          datasets: [{
            label: "Reports",
            data: MOCK_TREND_DATA.available.map((a, i) => a + MOCK_TREND_DATA.low[i] + MOCK_TREND_DATA.none[i]),
            backgroundColor: "#0d6e8a",
            borderRadius: 4,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      });
    }
  },

  initReportsPage() {
    const tbody = document.getElementById("reports-table");
    const filterStatus = document.getElementById("filter-status");
    const filterNeighborhood = document.getElementById("filter-neighborhood");
    const filterSearch = document.getElementById("filter-search");
    const alertContainer = document.getElementById("alert-container");

    MOCK_NEIGHBORHOODS.forEach((n) => {
      const opt = document.createElement("option");
      opt.value = n.id;
      opt.textContent = n.name;
      filterNeighborhood.appendChild(opt);
    });

    const render = () => {
      let reports = AquaWatch.getAllReports();

      if (filterStatus.value === "verified") {
        reports = reports.filter((r) => r.verified);
      } else if (filterStatus.value === "unverified") {
        reports = reports.filter((r) => !r.verified);
      }

      if (filterNeighborhood.value) {
        reports = reports.filter((r) => r.neighborhoodId === parseInt(filterNeighborhood.value, 10));
      }

      const q = filterSearch.value.trim().toLowerCase();
      if (q) {
        reports = reports.filter(
          (r) =>
            r.userName.toLowerCase().includes(q) ||
            r.neighborhood.toLowerCase().includes(q) ||
            r.notes.toLowerCase().includes(q)
        );
      }

      tbody.innerHTML = reports.map((r) => `
        <tr data-id="${r.id}">
          <td>#${r.id}</td>
          <td>${r.userName}</td>
          <td>${r.neighborhood}</td>
          <td>${AquaWatch.statusBadge(r.status)}</td>
          <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${r.notes}">${r.notes}</td>
          <td>${AquaWatch.formatDate(r.reportedAt)}</td>
          <td>${r.verified ? '<span class="badge badge-available">Yes</span>' : '<span class="badge badge-low">Pending</span>'}</td>
          <td>
            ${!r.verified ? `<button class="btn btn-sm btn-primary verify-btn" data-id="${r.id}">Verify</button>` : ""}
            <button class="btn btn-sm btn-danger delete-btn" data-id="${r.id}">Delete</button>
          </td>
        </tr>
      `).join("");

      tbody.querySelectorAll(".verify-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          const report = MOCK_REPORTS.find((r) => r.id === parseInt(btn.dataset.id, 10));
          if (report) {
            report.verified = true;
            AquaWatch.showAlert(alertContainer, `Report #${report.id} verified.`, "success");
            render();
          }
        });
      });

      tbody.querySelectorAll(".delete-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          if (!confirm("Delete this report?")) return;
          const id = parseInt(btn.dataset.id, 10);
          const idx = MOCK_REPORTS.findIndex((r) => r.id === id);
          if (idx !== -1) MOCK_REPORTS.splice(idx, 1);
          AquaWatch.showAlert(alertContainer, `Report #${id} deleted.`, "info");
          render();
        });
      });
    };

    [filterStatus, filterNeighborhood].forEach((el) => el.addEventListener("change", render));
    filterSearch.addEventListener("input", () => setTimeout(render, 300));
    render();
  },

  initUsersPage() {
    const tbody = document.getElementById("users-table");
    const filterRole = document.getElementById("filter-role");
    const filterStatus = document.getElementById("filter-status");
    const filterSearch = document.getElementById("filter-search");
    const modal = document.getElementById("user-modal");
    const alertContainer = document.getElementById("alert-container");

    AquaWatch.populateNeighborhoodSelect(document.getElementById("user-neighborhood"));

    const updateStats = () => {
      document.getElementById("stat-total-users").textContent = MOCK_USERS.length;
      document.getElementById("stat-active-users").textContent = MOCK_USERS.filter((u) => u.status === "active").length;
      document.getElementById("stat-suspended-users").textContent = MOCK_USERS.filter((u) => u.status === "suspended").length;
      document.getElementById("stat-admin-users").textContent = MOCK_USERS.filter((u) => u.role === "admin").length;
    };

    const openModal = () => {
      document.getElementById("user-form").reset();
      modal.classList.add("active");
    };

    const closeModal = () => modal.classList.remove("active");

    document.getElementById("add-user-btn").addEventListener("click", openModal);
    document.getElementById("modal-close").addEventListener("click", closeModal);
    document.getElementById("modal-cancel").addEventListener("click", closeModal);

    document.getElementById("user-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const name = document.getElementById("user-name").value.trim();
      const email = document.getElementById("user-email").value.trim();
      const role = document.getElementById("user-role").value;
      const neighborhoodId = document.getElementById("user-neighborhood").value;

      MOCK_USERS.push({
        id: Date.now(),
        name,
        email,
        role,
        neighborhoodId: neighborhoodId ? parseInt(neighborhoodId, 10) : null,
        status: "active",
        joinedAt: new Date().toISOString().slice(0, 10),
      });

      closeModal();
      AquaWatch.showAlert(alertContainer, `User ${name} added.`, "success");
      render();
    });

    const render = () => {
      let users = [...MOCK_USERS];

      if (filterRole.value) users = users.filter((u) => u.role === filterRole.value);
      if (filterStatus.value) users = users.filter((u) => u.status === filterStatus.value);

      const q = filterSearch.value.trim().toLowerCase();
      if (q) {
        users = users.filter(
          (u) => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
        );
      }

      tbody.innerHTML = users.map((u) => `
        <tr>
          <td>#${u.id}</td>
          <td><strong>${u.name}</strong></td>
          <td>${u.email}</td>
          <td><span class="badge ${u.role === "admin" ? "badge-scheduled" : "badge-neutral"}">${u.role}</span></td>
          <td>${u.neighborhoodId ? this.getNeighborhoodName(u.neighborhoodId) : "—"}</td>
          <td><span class="badge ${u.status === "active" ? "badge-available" : "badge-none"}">${u.status}</span></td>
          <td>${u.joinedAt}</td>
          <td>
            ${u.status === "active"
              ? `<button class="btn btn-sm btn-secondary suspend-btn" data-id="${u.id}">Suspend</button>`
              : `<button class="btn btn-sm btn-primary activate-btn" data-id="${u.id}">Activate</button>`}
          </td>
        </tr>
      `).join("");

      tbody.querySelectorAll(".suspend-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          const user = MOCK_USERS.find((u) => u.id === parseInt(btn.dataset.id, 10));
          if (user) { user.status = "suspended"; render(); updateStats(); }
        });
      });

      tbody.querySelectorAll(".activate-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          const user = MOCK_USERS.find((u) => u.id === parseInt(btn.dataset.id, 10));
          if (user) { user.status = "active"; render(); updateStats(); }
        });
      });

      updateStats();
    };

    [filterRole, filterStatus].forEach((el) => el.addEventListener("change", render));
    filterSearch.addEventListener("input", () => setTimeout(render, 300));
    render();
  },

  initMonitoringPage() {
    const today = AquaWatch.getAllReports().filter((r) => {
      const d = new Date(r.reportedAt);
      const now = new Date();
      return d.toDateString() === now.toDateString();
    }).length;

    document.getElementById("mon-reports-today").textContent = today;

    const components = [
      { name: "Web server (Apache)", status: "operational" },
      { name: "PHP application", status: "operational" },
      { name: "MySQL database", status: "operational" },
      { name: "Notification service", status: "degraded" },
      { name: "Report API", status: "pending" },
    ];

    document.getElementById("component-status").innerHTML = components.map((c) => {
      const badgeClass =
        c.status === "operational" ? "badge-available"
        : c.status === "degraded" ? "badge-low"
        : "badge-scheduled";
      return `
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--color-border);">
          <span>${c.name}</span>
          <span class="badge ${badgeClass}">${c.status}</span>
        </div>
      `;
    }).join("");

    const events = [
      { time: "2026-06-14T09:00:00", level: "info", component: "Auth", message: "User login successful — demo@aquawatch.ke" },
      { time: "2026-06-14T08:45:00", level: "info", component: "Reports", message: "New report submitted for Westlands" },
      { time: "2026-06-14T07:30:00", level: "warning", component: "Notifications", message: "Notification queue delayed — backend not connected" },
      { time: "2026-06-14T06:00:00", level: "info", component: "System", message: "Daily health check completed" },
      { time: "2026-06-13T22:15:00", level: "error", component: "Database", message: "Connection failed — using mock data (expected in frontend-only mode)" },
    ];

    const levelBadge = { info: "badge-scheduled", warning: "badge-low", error: "badge-none" };

    document.getElementById("event-log").innerHTML = events.map((e) => `
      <tr>
        <td>${AquaWatch.formatDate(e.time)}</td>
        <td><span class="badge ${levelBadge[e.level]}">${e.level}</span></td>
        <td>${e.component}</td>
        <td>${e.message}</td>
      </tr>
    `).join("");

    const ctx = document.getElementById("volume-chart");
    if (ctx) {
      new Chart(ctx, {
        type: "line",
        data: {
          labels: ["00:00", "04:00", "08:00", "12:00", "16:00", "20:00"],
          datasets: [{
            label: "Reports per hour",
            data: [2, 1, 8, 12, 15, 6],
            borderColor: "#0d6e8a",
            backgroundColor: "rgba(13, 110, 138, 0.1)",
            fill: true,
            tension: 0.3,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      });
    }
  },
};
