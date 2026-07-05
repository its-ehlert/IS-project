@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('topbar-title', 'Admin dashboard')

@section('topbar-actions')
        <span class="badge badge-scheduled">Admin</span>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
        <div class="stat-grid" id="admin-stats"></div>

        <div class="page-grid" style="margin-top: 1.5rem;">
          <div class="card">
            <div class="card-header">
              <h2>Reports pending verification</h2>
              <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-sm">Manage all</a>
            </div>
            <div id="pending-reports"></div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2>System activity (7 days)</h2>
            </div>
            <div class="card-body">
              <div class="chart-container" style="height: 240px;">
                <canvas id="activity-chart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
          <div class="card-header">
            <h2>Recent user registrations</h2>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">Manage users</a>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Neighbourhood</th>
                  <th>Status</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody id="recent-users"></tbody>
            </table>
          </div>
        </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    Admin.initDashboard().catch((e) => alert(e.message));
  });
</script>
@endpush
