<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="AquaWatch Nairobi — Real-time community water supply monitoring for Nairobi residents.">
  <title>@yield('title', 'AquaWatch Nairobi') — Community Water Supply Monitor</title>
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
  @stack('head')
</head>
<body>
  <header class="site-header">
    <div class="site-header-inner">
      <a href="{{ route('home') }}" class="logo">
        <span class="logo-icon">💧</span>
        AquaWatch Nairobi
      </a>
      <button class="mobile-menu-toggle" aria-label="Toggle menu">☰</button>
      <nav class="main-nav" aria-label="Main navigation">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('water-feeds') }}">Water Feeds</a>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('notifications') }}">Notifications</a>
      </nav>
      <div class="nav-actions">
        <a href="{{ route('notifications') }}" class="notification-bell" aria-label="Notifications">
          🔔
          <span class="badge-count">0</span>
        </a>
        @auth
          <span class="nav-user-name">{{ Auth::user()->name }}</span>
          <button type="button" id="logout-btn" class="btn btn-ghost btn-sm">Log out</button>
        @endauth
        @guest
          <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
          <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
        @endguest
      </div>
    </div>
  </header>

  <main class="site-main">
    @yield('content')
  </main>

  <footer class="site-footer">
    @hasSection('footer')
      @yield('footer')
    @else
      <div class="footer-bottom" style="border: none; margin: 0; padding: 0;">
        &copy; 2026 AquaWatch Nairobi
      </div>
    @endif
  </footer>

  <script src="{{ asset('js/api.js') }}"></script>
  <script src="{{ asset('js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
