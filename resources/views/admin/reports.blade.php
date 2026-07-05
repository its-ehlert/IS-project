@extends('layouts.admin')

@section('title', 'Manage Reports')

@section('topbar-title', 'Report management')

@section('content')
        <div id="alert-container"></div>

        <div class="filter-bar">
          <select id="filter-status" class="form-control">
            <option value="">All reports</option>
            <option value="unverified">Pending verification</option>
            <option value="verified">Verified</option>
          </select>
          <select id="filter-neighborhood" class="form-control">
            <option value="">All neighbourhoods</option>
          </select>
          <input type="search" id="filter-search" class="form-control" placeholder="Search…">
        </div>

        <div class="card">
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Reporter</th>
                  <th>Neighbourhood</th>
                  <th>Status</th>
                  <th>Details</th>
                  <th>Submitted</th>
                  <th>Verified</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="reports-table"></tbody>
            </table>
          </div>
        </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    Admin.initReportsPage().catch((e) => alert(e.message));
  });
</script>
@endpush
