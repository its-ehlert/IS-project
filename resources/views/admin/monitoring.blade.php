@extends('layouts.admin')

@section('title', 'System Monitoring')

@section('topbar-title', 'System monitoring')

@section('topbar-actions')
        <span class="badge badge-available" id="system-status">Operational</span>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
        <div class="stat-grid">
          <div class="stat-card">
            <div class="stat-label">Uptime</div>
            <div class="stat-value" style="font-size: 1.5rem;">99.2%</div>
            <div class="stat-change">Last 30 days (mock)</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Reports today</div>
            <div class="stat-value" id="mon-reports-today">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Active sessions</div>
            <div class="stat-value">24</div>
            <div class="stat-change">Simulated metric</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">API response</div>
            <div class="stat-value" style="font-size: 1.5rem; color: var(--status-available);">142ms</div>
          </div>
        </div>

        <div class="page-grid" style="margin-top: 1.5rem;">
          <div class="card">
            <div class="card-header">
              <h2>Report volume (24h)</h2>
            </div>
            <div class="card-body">
              <div class="chart-container" style="height: 260px;">
                <canvas id="volume-chart"></canvas>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2>Component status</h2>
            </div>
            <div class="card-body" id="component-status"></div>
          </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
          <div class="card-header">
            <h2>System event log</h2>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Timestamp</th>
                  <th>Level</th>
                  <th>Component</th>
                  <th>Message</th>
                </tr>
              </thead>
              <tbody id="event-log"></tbody>
            </table>
          </div>
        </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    Admin.initMonitoringPage().catch((e) => alert(e.message));
  });
</script>
@endpush
