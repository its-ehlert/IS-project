/**
 * Reports page — feed rendering and form submission.
 */

(function () {
  const feedEl = document.getElementById("reports-feed");
  const formEl = document.getElementById("report-form");
  const filterNeighborhood = document.getElementById("filter-neighborhood");
  const filterStatus = document.getElementById("filter-status");
  const filterSearch = document.getElementById("filter-search");
  const alertContainer = document.getElementById("alert-container");

  function renderFeed() {
    const reports = AquaWatch.filterReports(AquaWatch.getAllReports(), {
      neighborhoodId: filterNeighborhood.value,
      status: filterStatus.value,
      search: filterSearch.value.trim(),
    });

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

    feedEl.innerHTML = reports.map((r) => AquaWatch.renderReportItem(r)).join("");
  }

  function initFilters() {
    filterNeighborhood.innerHTML = '<option value="">All neighbourhoods</option>';
    MOCK_NEIGHBORHOODS.forEach((n) => {
      const opt = document.createElement("option");
      opt.value = n.id;
      opt.textContent = `${n.name} (${n.area})`;
      filterNeighborhood.appendChild(opt);
    });

    AquaWatch.populateNeighborhoodSelect(
      document.getElementById("report-neighborhood"),
      MOCK_CURRENT_USER.neighborhoodId
    );

    [filterNeighborhood, filterStatus].forEach((el) => {
      el.addEventListener("change", renderFeed);
    });

    let searchTimeout;
    filterSearch.addEventListener("input", () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(renderFeed, 300);
    });
  }

  formEl.addEventListener("submit", (e) => {
    e.preventDefault();

    const neighborhoodId = document.getElementById("report-neighborhood").value;
    const status = document.getElementById("report-status").value;
    const notes = document.getElementById("report-notes").value.trim();

    if (!neighborhoodId || !status || notes.length < 10) {
      AquaWatch.showAlert(alertContainer, "Please fill in all fields. Details need at least 10 characters.", "error");
      return;
    }

    AquaWatch.addReport({ neighborhoodId, status, notes });
    formEl.reset();
    AquaWatch.populateNeighborhoodSelect(
      document.getElementById("report-neighborhood"),
      MOCK_CURRENT_USER.neighborhoodId
    );
    AquaWatch.showAlert(alertContainer, "Report submitted successfully! It will appear in the community feed.", "success");
    renderFeed();
  });

  document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    renderFeed();
  });
})();
