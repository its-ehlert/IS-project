/**
 * Dashboard page — charts and neighbourhood summary.
 */

(function () {
  let weeklyChart = null;
  let statusChart = null;
  let monthlyChart = null;

  const chartColors = {
    available: "#16a34a",
    low: "#ca8a04",
    none: "#dc2626",
    scheduled: "#2563eb",
  };

  function renderStats() {
    const reports = AquaWatch.getAllReports();
    const stats = AquaWatch.computeStats(reports);
    const container = document.getElementById("dashboard-stats");

    container.innerHTML = `
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

  function getLatestReportForNeighborhood(neighborhoodId) {
    return AquaWatch.getAllReports().find((r) => r.neighborhoodId === neighborhoodId);
  }

  function countRecentReports(neighborhoodId, days = 7) {
    const cutoff = Date.now() - days * 86400000;
    return AquaWatch.getAllReports().filter(
      (r) => r.neighborhoodId === neighborhoodId && new Date(r.reportedAt) >= cutoff
    ).length;
  }

  function renderNeighborhoodTable() {
    const tbody = document.querySelector("#neighborhood-table tbody");
    tbody.innerHTML = MOCK_NEIGHBORHOODS.map((n) => {
      const latest = getLatestReportForNeighborhood(n.id);
      const count = countRecentReports(n.id);
      return `
        <tr>
          <td><strong>${n.name}</strong></td>
          <td>${n.area}</td>
          <td>${latest ? AquaWatch.statusBadge(latest.status) : '<span class="badge badge-neutral">No data</span>'}</td>
          <td>${latest ? AquaWatch.formatDate(latest.reportedAt) : "—"}</td>
          <td>${count}</td>
        </tr>
      `;
    }).join("");
  }

  function initWeeklyChart() {
    const ctx = document.getElementById("weekly-chart");
    weeklyChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: MOCK_TREND_DATA.labels,
        datasets: [
          {
            label: "Available",
            data: MOCK_TREND_DATA.available,
            borderColor: chartColors.available,
            backgroundColor: "rgba(22, 163, 74, 0.1)",
            fill: true,
            tension: 0.3,
          },
          {
            label: "Low pressure",
            data: MOCK_TREND_DATA.low,
            borderColor: chartColors.low,
            backgroundColor: "rgba(202, 138, 4, 0.1)",
            fill: true,
            tension: 0.3,
          },
          {
            label: "No water",
            data: MOCK_TREND_DATA.none,
            borderColor: chartColors.none,
            backgroundColor: "rgba(220, 38, 38, 0.1)",
            fill: true,
            tension: 0.3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
        },
        scales: {
          y: { beginAtZero: true, title: { display: true, text: "Reports" } },
        },
      },
    });
  }

  function initStatusChart() {
    const reports = AquaWatch.getAllReports();
    const stats = AquaWatch.computeStats(reports);
    const ctx = document.getElementById("status-chart");

    statusChart = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Available", "Low pressure", "No water", "Scheduled"],
        datasets: [{
          data: [
            stats.byStatus.available,
            stats.byStatus.low,
            stats.byStatus.none,
            stats.byStatus.scheduled,
          ],
          backgroundColor: [
            chartColors.available,
            chartColors.low,
            chartColors.none,
            chartColors.scheduled,
          ],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
        },
      },
    });
  }

  function initMonthlyChart() {
    const ctx = document.getElementById("monthly-chart");
    monthlyChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: MOCK_MONTHLY_STATS.map((m) => m.month),
        datasets: [
          {
            label: "Availability %",
            data: MOCK_MONTHLY_STATS.map((m) => m.availablePct),
            backgroundColor: chartColors.available,
            borderRadius: 4,
          },
          {
            label: "Outage reports",
            data: MOCK_MONTHLY_STATS.map((m) => m.outages),
            backgroundColor: chartColors.none,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
        },
        scales: {
          y: { beginAtZero: true },
        },
      },
    });
  }

  function initNeighborhoodFilter() {
    const select = document.getElementById("chart-neighborhood");
    MOCK_NEIGHBORHOODS.forEach((n) => {
      const opt = document.createElement("option");
      opt.value = n.id;
      opt.textContent = n.name;
      select.appendChild(opt);
    });

    select.addEventListener("change", () => {
      if (!weeklyChart) return;
      const factor = select.value ? 0.6 + Math.random() * 0.4 : 1;
      weeklyChart.data.datasets.forEach((ds, i) => {
        const key = ["available", "low", "none"][i];
        ds.data = MOCK_TREND_DATA[key].map((v) => Math.round(v * factor));
      });
      weeklyChart.update();
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    renderStats();
    renderNeighborhoodTable();
    initWeeklyChart();
    initStatusChart();
    initMonthlyChart();
    initNeighborhoodFilter();
  });
})();
