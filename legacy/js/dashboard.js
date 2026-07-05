/**
 * Dashboard page — charts and neighbourhood summary.
 */

(function () {
  let weeklyChart = null;
  let statusChart = null;
  let monthlyChart = null;

  const chartColors = {
    available: '#16a34a',
    low: '#ca8a04',
    none: '#dc2626',
    scheduled: '#2563eb',
  };

  function renderStats(stats) {
    document.getElementById('dashboard-stats').innerHTML = `
      <div class="stat-card">
        <div class="stat-label">Total reports</div>
        <div class="stat-value">${stats.total}</div>
        <div class="stat-change">Across all neighbourhoods</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Available</div>
        <div class="stat-value" style="color: ${chartColors.available}">${stats.byStatus.available}</div>
        <div class="stat-change">Normal supply reported</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Outages</div>
        <div class="stat-value" style="color: ${chartColors.none}">${stats.byStatus.none}</div>
        <div class="stat-change">No water reported</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Neighbourhoods</div>
        <div class="stat-value">${stats.neighborhoods}</div>
        <div class="stat-change">With active reports</div>
      </div>
    `;
  }

  function renderNeighborhoodTable(summary) {
    const tbody = document.querySelector('#neighborhood-table tbody');
    tbody.innerHTML = summary.map((n) => `
      <tr>
        <td><strong>${AquaWatch.escapeHtml(n.name)}</strong></td>
        <td>${AquaWatch.escapeHtml(n.area)}</td>
        <td>${n.latestStatus ? AquaWatch.statusBadge(n.latestStatus) : '<span class="badge badge-neutral">No data</span>'}</td>
        <td>${n.lastReport ? AquaWatch.formatDate(n.lastReport) : '—'}</td>
        <td>${n.reports7d}</td>
      </tr>
    `).join('');
  }

  function initWeeklyChart(trends) {
    const ctx = document.getElementById('weekly-chart');
    if (weeklyChart) weeklyChart.destroy();
    weeklyChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: trends.labels,
        datasets: [
          { label: 'Available', data: trends.available, borderColor: chartColors.available, backgroundColor: 'rgba(22,163,74,0.1)', fill: true, tension: 0.3 },
          { label: 'Low pressure', data: trends.low, borderColor: chartColors.low, backgroundColor: 'rgba(202,138,4,0.1)', fill: true, tension: 0.3 },
          { label: 'No water', data: trends.none, borderColor: chartColors.none, backgroundColor: 'rgba(220,38,38,0.1)', fill: true, tension: 0.3 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Reports' } } },
      },
    });
  }

  function initStatusChart(stats) {
    const ctx = document.getElementById('status-chart');
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Available', 'Low pressure', 'No water', 'Scheduled'],
        datasets: [{
          data: [stats.byStatus.available, stats.byStatus.low, stats.byStatus.none, stats.byStatus.scheduled],
          backgroundColor: [chartColors.available, chartColors.low, chartColors.none, chartColors.scheduled],
        }],
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });
  }

  function initMonthlyChart(monthly) {
    const ctx = document.getElementById('monthly-chart');
    if (monthlyChart) monthlyChart.destroy();
    monthlyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: monthly.map((m) => m.month),
        datasets: [
          { label: 'Availability %', data: monthly.map((m) => m.availablePct), backgroundColor: chartColors.available, borderRadius: 4 },
          { label: 'Outage reports', data: monthly.map((m) => m.outages), backgroundColor: chartColors.none, borderRadius: 4 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } },
      },
    });
  }

  async function loadDashboard(neighborhoodId = '') {
    const data = await API.getDashboard(neighborhoodId);
    renderStats(data.stats);
    renderNeighborhoodTable(data.neighborhoodSummary);
    initWeeklyChart(data.weeklyTrends);
    initStatusChart(data.stats);
    initMonthlyChart(data.monthlyStats);
  }

  document.addEventListener('DOMContentLoaded', async () => {
    try {
      await AquaWatch.loadNeighborhoods();
      const select = document.getElementById('chart-neighborhood');
      AppState.neighborhoods.forEach((n) => {
        const opt = document.createElement('option');
        opt.value = n.id;
        opt.textContent = n.name;
        select.appendChild(opt);
      });

      select.addEventListener('change', () => {
        loadDashboard(select.value).catch(console.error);
      });

      await loadDashboard();
    } catch (err) {
      document.querySelector('.site-main').insertAdjacentHTML('afterbegin',
        `<div class="alert alert-error">Could not load dashboard: ${err.message}. Run database/install.php first.</div>`
      );
    }
  });
})();
