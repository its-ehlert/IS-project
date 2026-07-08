@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
      <h1>Welcome back</h1>
      <p class="subtitle">Log in to submit reports and manage your alerts.</p>

      <div id="alert-container"></div>

      <form id="login-form" novalidate>
        <div class="form-group">
          <label for="email">Email address</label>
          <input type="email" id="email" class="form-control" placeholder="you@example.com" required autocomplete="email">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" class="form-control" placeholder="Your password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
      </form>

      <p class="auth-footer-link">
        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
      </p>
     <!-- <p class="auth-footer-link" style="margin-top: 0.75rem; font-size: 0.8125rem;">
        Demo: <strong>demo@aquawatch.ke</strong> / <strong>demo123</strong> · Admin: <strong>admin@aquawatch.ke</strong> / <strong>admin123</strong>
      </p> -->
@endsection

@push('scripts')
<script>
  document.getElementById("login-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const alertContainer = document.getElementById("alert-container");

    try {
      const data = await API.login(email, password);
      AppState.user = data.user;
      if (data.user.role === "admin") {
        window.location.href = "{{ route('admin.dashboard') }}";
      } else {
        window.location.href = "{{ route('water-feeds') }}";
      }
    } catch (err) {
      AquaWatch.showAlert(alertContainer, err.message, "error");
    }
  });
</script>
@endpush
