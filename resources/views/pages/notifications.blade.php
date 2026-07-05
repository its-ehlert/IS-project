@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-header" style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
      <div>
        <h1>Notifications</h1>
        <p>Alerts about water supply changes in neighbourhoods you follow.</p>
      </div>
      <button id="mark-all-read" class="btn btn-secondary btn-sm">Mark all as read</button>
    </div>

    <div class="page-grid-reverse">
      <aside>
        <div class="card">
          <div class="card-header">
            <h3>Alert preferences</h3>
          </div>
          <div class="card-body">
            <p style="font-size: 0.9375rem; color: var(--color-text-muted); margin-bottom: 1rem;">
              Choose which neighbourhoods to receive alerts for. (Frontend demo — saved locally.)
            </p>
            <form id="subscription-form">
              <div id="neighborhood-checkboxes"></div>
              <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">Save preferences</button>
            </form>
          </div>
        </div>
      </aside>

      <section>
        <div class="card">
          <div class="card-header">
            <h2>Your alerts</h2>
            <span id="unread-count" class="badge badge-scheduled">0 unread</span>
          </div>
          <div id="notifications-list"></div>
        </div>
      </section>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/notifications.js') }}"></script>
@endpush
