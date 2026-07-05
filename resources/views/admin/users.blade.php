@extends('layouts.admin')

@section('title', 'User Management')

@section('topbar-title', 'Account administration')

@section('topbar-actions')
        <button class="btn btn-primary btn-sm" id="add-user-btn">Add user</button>
@endsection

@section('content')
        <div id="alert-container"></div>

        <div class="stat-grid" style="margin-bottom: 1.5rem;">
          <div class="stat-card">
            <div class="stat-label">Total users</div>
            <div class="stat-value" id="stat-total-users">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value" id="stat-active-users" style="color: var(--status-available);">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Suspended</div>
            <div class="stat-value" id="stat-suspended-users" style="color: var(--status-none);">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Administrators</div>
            <div class="stat-value" id="stat-admin-users">0</div>
          </div>
        </div>

        <div class="filter-bar">
          <select id="filter-role" class="form-control">
            <option value="">All roles</option>
            <option value="resident">Residents</option>
            <option value="admin">Administrators</option>
          </select>
          <select id="filter-status" class="form-control">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
          </select>
          <input type="search" id="filter-search" class="form-control" placeholder="Search by name or email…">
        </div>

        <div class="card">
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Neighbourhood</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="users-table"></tbody>
            </table>
          </div>
        </div>
@endsection

@section('modals')
  <div class="modal-overlay" id="user-modal">
    <div class="modal" role="dialog" aria-labelledby="modal-title">
      <div class="modal-header">
        <h3 id="modal-title">Add user</h3>
        <button class="modal-close" id="modal-close" aria-label="Close">&times;</button>
      </div>
      <form id="user-form">
        <div class="modal-body">
          <div class="form-group">
            <label for="user-name">Full name</label>
            <input type="text" id="user-name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="user-email">Email</label>
            <input type="email" id="user-email" class="form-control" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="user-role">Role</label>
              <select id="user-role" class="form-control">
                <option value="resident">Resident</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <div class="form-group">
              <label for="user-neighborhood">Neighbourhood</label>
              <select id="user-neighborhood" class="form-control">
                <option value="">None (admin)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" id="modal-cancel">Cancel</button>
          <button type="submit" class="btn btn-primary">Save user</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    Admin.initUsersPage().catch((e) => alert(e.message));
  });
</script>
@endpush
