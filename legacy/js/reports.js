/**
 * Reports page — feed rendering and form submission.
 */

(function () {
  const feedEl = document.getElementById('reports-feed');
  const formEl = document.getElementById('report-form');
  const filterNeighborhood = document.getElementById('filter-neighborhood');
  const filterStatus = document.getElementById('filter-status');
  const filterSearch = document.getElementById('filter-search');
  const alertContainer = document.getElementById('alert-container');

  let allReports = [];

  async function loadReports() {
    const filters = {
      neighborhoodId: filterNeighborhood.value,
      status: filterStatus.value,
      search: filterSearch.value.trim(),
    };
    const data = await API.getReports(filters);
    allReports = data.reports;
    renderFeed(allReports);
  }

  function renderFeed(reports) {
    if (reports.length === 0) {
      feedEl.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">💧</div>
          <h3>No reports found</h3>
          <p>Try adjusting your filters or submit the first report for this area.</p>
        </div>
      `;
      return;
    }
    feedEl.innerHTML = reports.map((r) => AquaWatch.renderReportItem(r)).join('');
  }

  async function initFilters() {
    await AquaWatch.loadNeighborhoods();

    filterNeighborhood.innerHTML = '<option value="">All neighbourhoods</option>';
    AppState.neighborhoods.forEach((n) => {
      const opt = document.createElement('option');
      opt.value = n.id;
      opt.textContent = `${n.name} (${n.area})`;
      filterNeighborhood.appendChild(opt);
    });

    const user = await AquaWatch.loadCurrentUser();
    AquaWatch.populateNeighborhoodSelect(
      document.getElementById('report-neighborhood'),
      user ? user.neighborhoodId : null
    );

    [filterNeighborhood, filterStatus].forEach((el) => {
      el.addEventListener('change', () => loadReports().catch(showError));
    });

    let searchTimeout;
    filterSearch.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => loadReports().catch(showError), 300);
    });
  }

  function showError(err) {
    AquaWatch.showAlert(alertContainer, err.message, 'error');
  }

  formEl.addEventListener('submit', async (e) => {
    e.preventDefault();

    const neighborhoodId = document.getElementById('report-neighborhood').value;
    const status = document.getElementById('report-status').value;
    const notes = document.getElementById('report-notes').value.trim();

    if (!neighborhoodId || !status || notes.length < 10) {
      AquaWatch.showAlert(alertContainer, 'Please fill in all fields. Details need at least 10 characters.', 'error');
      return;
    }

    try {
      const user = await AquaWatch.loadCurrentUser();
      if (!user) {
        AquaWatch.showAlert(alertContainer, 'Please log in to submit a report.', 'warning');
        setTimeout(() => { window.location.href = 'login.html'; }, 1500);
        return;
      }

      await API.createReport({ neighborhoodId: parseInt(neighborhoodId, 10), status, notes });
      formEl.reset();
      AquaWatch.populateNeighborhoodSelect(
        document.getElementById('report-neighborhood'),
        user.neighborhoodId
      );
      AquaWatch.showAlert(alertContainer, 'Report submitted successfully!', 'success');
      await loadReports();
      AquaWatch.updateNotificationBadge();
    } catch (err) {
      showError(err);
    }
  });

  document.addEventListener('DOMContentLoaded', async () => {
    try {
      await initFilters();
      await loadReports();
    } catch (err) {
      feedEl.innerHTML = `<div class="empty-state"><p>Could not load reports. Run <a href="../database/install.php">database/install.php</a> first.</p></div>`;
      showError(err);
    }
  });
})();
