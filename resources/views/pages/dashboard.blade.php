@extends('layouts.app')

@section('title', 'Supply Dashboard')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    <div class="page-header">
      <h1>Supply dashboard</h1>
      <p>Historical trends and current supply overview across Nairobi neighbourhoods.</p>
    </div>

    <div class="stat-grid" id="dashboard-stats"></div>

    <div class="page-grid" style="margin-top: 1.5rem;">
      <div class="card">
        <div class="card-header">
          <h2>Weekly supply trends</h2>
          <select id="chart-neighborhood" class="form-control" aria-label="Filter chart by neighbourhood">
            <option value="">All Nairobi</option>
          </select>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="weekly-chart" aria-label="Weekly water supply trends chart"></canvas>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Current status by area</h2>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="status-chart" aria-label="Current status distribution chart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
      <div class="card-header">
        <h2>6-month availability trend</h2>
      </div>
      <div class="card-body">
        <div class="chart-container" style="height: 280px;">
          <canvas id="monthly-chart" aria-label="Monthly availability percentage chart"></canvas>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
      <div class="card-header">
        <h2>Neighbourhood summary</h2>
      </div>
      <div class="table-wrapper">
        <table id="neighborhood-table">
          <thead>
            <tr>
              <th>Neighbourhood</th>
              <th>Area</th>
              <th>Latest status</th>
              <th>Last report</th>
              <th>Reports (7d)</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
