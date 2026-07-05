@extends('layouts.app')

@section('title', 'Water Feeds')

@section('content')
    <div class="page-header">
      <h1>Water feeds</h1>
      <p>Submit a report for your neighbourhood or browse updates from the community.</p>
    </div>

    <div id="alert-container"></div>

    <div class="page-grid">
      <section>
        <div class="card">
          <div class="card-header">
            <h2>Community feed</h2>
          </div>
          <div class="filter-bar" style="padding: 1rem 1.5rem 0;">
            <select id="filter-neighborhood" class="form-control" aria-label="Filter by neighbourhood">
              <option value="">All neighbourhoods</option>
            </select>
            <select id="filter-status" class="form-control" aria-label="Filter by status">
              <option value="">All statuses</option>
              <option value="available">Available</option>
              <option value="low">Low pressure</option>
              <option value="none">No water</option>
              <option value="scheduled">Scheduled</option>
            </select>
            <input type="search" id="filter-search" class="form-control" placeholder="Search reports…" aria-label="Search reports">
          </div>
          <div id="reports-feed"></div>
        </div>
      </section>

      <aside>
        <div class="card">
          <div class="card-header">
            <h2>Submit report</h2>
          </div>
          <div class="card-body">
            <form id="report-form" novalidate>
              <div class="form-group">
                <label for="report-neighborhood">Neighbourhood</label>
                <select id="report-neighborhood" class="form-control" required>
                  <option value="">Select neighbourhood</option>
                </select>
              </div>
              <div class="form-group">
                <label for="report-status">Water status</label>
                <select id="report-status" class="form-control" required>
                  <option value="">Select status</option>
                  <option value="available">Available — normal flow</option>
                  <option value="low">Low pressure — trickle or weak</option>
                  <option value="none">No water — dry taps</option>
                  <option value="scheduled">Scheduled — announced restoration</option>
                </select>
              </div>
              <div class="form-group">
                <label for="report-notes">Details</label>
                <textarea id="report-notes" class="form-control" placeholder="Describe what you're seeing — time, pressure, affected streets…" required minlength="10"></textarea>
                <span class="hint">Minimum 10 characters. Be specific to help neighbours.</span>
              </div>
              <button type="submit" class="btn btn-primary btn-block">Submit report</button>
            </form>
          </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
          <div class="card-header">
            <h3>Status guide</h3>
          </div>
          <div class="card-body" style="font-size: 0.9375rem;">
            <p><span class="badge badge-available">Available</span> Normal flow at taps</p>
            <p><span class="badge badge-low">Low Pressure</span> Weak or intermittent supply</p>
            <p><span class="badge badge-none">No Water</span> Dry taps, no supply</p>
            <p style="margin-bottom: 0;"><span class="badge badge-scheduled">Scheduled</span> Official or expected restoration window</p>
          </div>
        </div>
      </aside>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/reports.js') }}"></script>
@endpush
