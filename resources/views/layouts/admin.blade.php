<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — AquaWatch Admin</title>
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
  @stack('head')
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="admin-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="logo">
          <span class="logo-icon">💧</span>
          AquaWatch
        </a>
        <div class="logo-sub">Administration</div>
      </div>
      <nav class="admin-nav" aria-label="Admin navigation">
        <div class="admin-nav-section">Overview</div>
        <a href="{{ route('admin.dashboard') }}"><span class="nav-icon">📊</span> Dashboard</a>
        <div class="admin-nav-section">Management</div>
        <a href="{{ route('admin.reports') }}"><span class="nav-icon">📋</span> Reports</a>
        <a href="{{ route('admin.users') }}"><span class="nav-icon">👥</span> Users</a>
        <a href="{{ route('admin.monitoring') }}"><span class="nav-icon">🖥️</span> System monitoring</a>
      </nav>
      <div class="admin-sidebar-footer">
        <a href="{{ route('home') }}">← Back to public site</a>
      </div>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button class="admin-mobile-toggle" aria-label="Toggle sidebar">☰</button>
          <h1>@yield('topbar-title')</h1>
        </div>
        @yield('topbar-actions')
      </header>

      <div class="admin-content">
        @yield('content')
      </div>
    </div>
  </div>

  @yield('modals')

  <script src="{{ asset('js/api.js') }}"></script>
  <script src="{{ asset('js/main.js') }}"></script>
  <script src="{{ asset('js/admin.js') }}"></script>
  @stack('scripts')
</body>
</html>
