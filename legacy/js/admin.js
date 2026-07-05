/**
 * Admin panel logic — report management, users, monitoring.
 */

const Admin = {
  async requireAdmin() {
    const user = await AquaWatch.loadCurrentUser();
    if (!user || user.role !== 'admin') {
      window.location.href = '../pages/login.html';
      throw new Error('Admin access required');
    }
    return user;
  },

  async initDashboard() {
    await this.requireAdmin();
    const data = await API.adminGetStats();
    const s = data.stats;

    document.getElementById('admin-stats').innerHTML = `
      <div class="stat-card">
        <div class="stat-label">Total reports</div>
        <div class="stat-value">${s.totalReports}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pending verification</div>
        <div class="stat-value" style="color: var(--status-low);">${s.pendingReports}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Registered users</div>
        <div class="stat-value">${s.totalUsers}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Active users</div>
        <div class="stat-value" style="color: var(--status-available);">${s.activeUsers}</div>
      </div>
    `;

    const pendingEl = document.getElementById('pending-reports');
    if (!data.pendingReports.length) {
      pendingEl.innerHTML = '<div class="empty-state"><p>All reports verified.</p></div>';
    } else {
      pendingEl.innerHTML = data.pendingReports.map((r) => AquaWatch.renderReportItem(r)).join('');
    }

    document.getElementById('recent-users').innerHTML = data.recentUsers.map((u) => `
      <tr>
        <td><strong>${AquaWatch.escapeHtml(u.name)}</strong></td>
        <td>${AquaWatch.escapeHtml(u.email)}</td>
        <td>${u.neighborhoodId ? AquaWatch.getNeighborhoodName(u.neighborhoodId) : '—'}</td>
        <td><span class="badge ${u.status === 'active' ? 'badge-available' : 'badge-none'}">${u.status}</span></td>
        <td>${u.joinedAt}</td>
      </tr>
    `).join('');

    const ctx = document.getElementById('activity-chart');
    if (ctx) {
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.activity.labels,
          datasets: [{
            label: 'Reports',
            data: data.activity.counts,
            backgroundColor: '#0d6e8a',
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

  async initReportsPage() {
    await this.requireAdmin();
    await AquaWatch.loadNeighborhoods();

    const tbody = document.getElementById('reports-table');
    const filterStatus = document.getElementById('filter-status');
    const filterNeighborhood = document.getElementById('filter-neighborhood');
    const filterSearch = document.getElementById('filter-search');
    const alertContainer = document.getElementById('alert-container');

    AppState.neighborhoods.forEach((n) => {
      const opt = document.createElement('option');
      opt.value = n.id;
      opt.textContent = n.name;
      filterNeighborhood.appendChild(opt);
    });

    const render = async () => {
      const filters = {};
      if (filterStatus.value === 'verified') filters.verifiedFilter = 'verified';
      if (filterStatus.value === 'unverified') filters.verifiedFilter = 'unverified';
      if (filterNeighborhood.value) filters.neighborhoodId = filterNeighborhood.value;
      if (filterSearch.value.trim()) filters.search = filterSearch.value.trim();

      const data = await API.adminGetReports(filters);
      const reports = data.reports;

      tbody.innerHTML = reports.map((r) => `
        <tr data-id="${r.id}">
          <td>#${r.id}</td>
          <td>${AquaWatch.escapeHtml(r.userName)}</td>
          <td>${AquaWatch.escapeHtml(r.neighborhood)}</td>
          <td>${AquaWatch.statusBadge(r.status)}</td>
          <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${AquaWatch.escapeHtml(r.notes)}">${AquaWatch.escapeHtml(r.notes)}</td>
          <td>${AquaWatch.formatDate(r.reportedAt)}</td>
          <td>${r.verified ? '<span class="badge badge-available">Yes</span>' : '<span class="badge badge-low">Pending</span>'}</td>
          <td>
            ${!r.verified ? `<button class="btn btn-sm btn-primary verify-btn" data-id="${r.id}">Verify</button>` : ''}
            <button class="btn btn-sm btn-danger delete-btn" data-id="${r.id}">Delete</button>
          </td>
        </tr>
      `).join('');

      tbody.querySelectorAll('.verify-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          try {
            await API.adminVerifyReport(parseInt(btn.dataset.id, 10));
            AquaWatch.showAlert(alertContainer, `Report #${btn.dataset.id} verified.`, 'success');
            render();
          } catch (e) {
            AquaWatch.showAlert(alertContainer, e.message, 'error');
          }
        });
      });

      tbody.querySelectorAll('.delete-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          if (!confirm('Delete this report?')) return;
          try {
            await API.adminDeleteReport(parseInt(btn.dataset.id, 10));
            AquaWatch.showAlert(alertContainer, `Report #${btn.dataset.id} deleted.`, 'info');
            render();
          } catch (e) {
            AquaWatch.showAlert(alertContainer, e.message, 'error');
          }
        });
      });
    };

    [filterStatus, filterNeighborhood].forEach((el) => el.addEventListener('change', () => render().catch(console.error)));
    filterSearch.addEventListener('input', () => setTimeout(() => render().catch(console.error), 300));
    await render();
  },

  async initUsersPage() {
    await this.requireAdmin();
    await AquaWatch.loadNeighborhoods();

    const tbody = document.getElementById('users-table');
    const filterRole = document.getElementById('filter-role');
    const filterStatus = document.getElementById('filter-status');
    const filterSearch = document.getElementById('filter-search');
    const modal = document.getElementById('user-modal');
    const alertContainer = document.getElementById('alert-container');

    AquaWatch.populateNeighborhoodSelect(document.getElementById('user-neighborhood'));

    const updateStats = (stats) => {
      document.getElementById('stat-total-users').textContent = stats.total;
      document.getElementById('stat-active-users').textContent = stats.active;
      document.getElementById('stat-suspended-users').textContent = stats.suspended;
      document.getElementById('stat-admin-users').textContent = stats.admins;
    };

    const openModal = () => {
      document.getElementById('user-form').reset();
      modal.classList.add('active');
    };
    const closeModal = () => modal.classList.remove('active');

    document.getElementById('add-user-btn').addEventListener('click', openModal);
    document.getElementById('modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-cancel').addEventListener('click', closeModal);

    document.getElementById('user-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      try {
        await API.adminCreateUser({
          name: document.getElementById('user-name').value.trim(),
          email: document.getElementById('user-email').value.trim(),
          role: document.getElementById('user-role').value,
          neighborhoodId: document.getElementById('user-neighborhood').value,
        });
        closeModal();
        AquaWatch.showAlert(alertContainer, 'User added. Default password: changeme123', 'success');
        render();
      } catch (err) {
        AquaWatch.showAlert(alertContainer, err.message, 'error');
      }
    });

    const render = async () => {
      const filters = {};
      if (filterRole.value) filters.role = filterRole.value;
      if (filterStatus.value) filters.status = filterStatus.value;
      if (filterSearch.value.trim()) filters.search = filterSearch.value.trim();

      const data = await API.adminGetUsers(filters);
      updateStats(data.stats);

      tbody.innerHTML = data.users.map((u) => `
        <tr>
          <td>#${u.id}</td>
          <td><strong>${AquaWatch.escapeHtml(u.name)}</strong></td>
          <td>${AquaWatch.escapeHtml(u.email)}</td>
          <td><span class="badge ${u.role === 'admin' ? 'badge-scheduled' : 'badge-neutral'}">${u.role}</span></td>
          <td>${u.neighborhoodId ? AquaWatch.getNeighborhoodName(u.neighborhoodId) : '—'}</td>
          <td><span class="badge ${u.status === 'active' ? 'badge-available' : 'badge-none'}">${u.status}</span></td>
          <td>${u.joinedAt}</td>
          <td>
            ${u.status === 'active'
              ? `<button class="btn btn-sm btn-secondary suspend-btn" data-id="${u.id}">Suspend</button>`
              : `<button class="btn btn-sm btn-primary activate-btn" data-id="${u.id}">Activate</button>`}
          </td>
        </tr>
      `).join('');

      tbody.querySelectorAll('.suspend-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          await API.adminSetUserStatus(parseInt(btn.dataset.id, 10), 'suspend');
          render();
        });
      });

      tbody.querySelectorAll('.activate-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          await API.adminSetUserStatus(parseInt(btn.dataset.id, 10), 'activate');
          render();
        });
      });
    };

    [filterRole, filterStatus].forEach((el) => el.addEventListener('change', () => render().catch(console.error)));
    filterSearch.addEventListener('input', () => setTimeout(() => render().catch(console.error), 300));
    await render();
  },

  async initMonitoringPage() {
    await this.requireAdmin();
    const data = await API.adminGetStats();

    document.getElementById('mon-reports-today').textContent = data.stats.reportsToday;

    const components = [
      { name: 'Web server (Apache)', status: 'operational' },
      { name: 'PHP application', status: 'operational' },
      { name: 'MySQL database', status: 'operational' },
      { name: 'Notification service', status: 'operational' },
      { name: 'Report API', status: 'operational' },
    ];

    document.getElementById('component-status').innerHTML = components.map((c) => `
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--color-border);">
        <span>${c.name}</span>
        <span class="badge badge-available">${c.status}</span>
      </div>
    `).join('');

    const levelBadge = { info: 'badge-scheduled', warning: 'badge-low', error: 'badge-none', success: 'badge-available' };

    document.getElementById('event-log').innerHTML = data.events.map((e) => `
      <tr>
        <td>${AquaWatch.formatDate(e.time)}</td>
        <td><span class="badge ${levelBadge[e.level] || 'badge-neutral'}">${e.level}</span></td>
        <td>${AquaWatch.escapeHtml(e.component)}</td>
        <td>${AquaWatch.escapeHtml(e.message)}</td>
      </tr>
    `).join('');

    const ctx = document.getElementById('volume-chart');
    if (ctx) {
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.activity.labels,
          datasets: [{
            label: 'Reports (7 days)',
            data: data.activity.counts,
            borderColor: '#0d6e8a',
            backgroundColor: 'rgba(13, 110, 138, 0.1)',
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
