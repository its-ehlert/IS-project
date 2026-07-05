<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — AquaWatch Nairobi</title>
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
  <div class="auth-page">
    <div class="auth-card">
      <a href="{{ route('home') }}" class="logo">
        <span class="logo-icon">💧</span>
        AquaWatch Nairobi
      </a>
      @yield('content')
    </div>
  </div>

  <script src="{{ asset('js/api.js') }}"></script>
  <script src="{{ asset('js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
