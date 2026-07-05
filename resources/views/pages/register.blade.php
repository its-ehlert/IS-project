@extends('layouts.auth')

@section('title', 'Sign up')

@section('content')
      <h1>Create your account</h1>
      <p class="subtitle">Join your community in monitoring Nairobi's water supply.</p>

      <div id="alert-container"></div>

      <form id="register-form" novalidate>
        <div class="form-group">
          <label for="name">Full name</label>
          <input type="text" id="name" class="form-control" placeholder="Jane Mwangi" required autocomplete="name">
        </div>
        <div class="form-group">
          <label for="email">Email address</label>
          <input type="email" id="email" class="form-control" placeholder="you@example.com" required autocomplete="email">
        </div>
        <div class="form-group">
          <label for="neighborhood">Your neighbourhood</label>
          <select id="neighborhood" class="form-control" required>
            <option value="">Select neighbourhood</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" class="form-control" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
          </div>
          <div class="form-group">
            <label for="confirm-password">Confirm password</label>
            <input type="password" id="confirm-password" class="form-control" placeholder="Repeat password" required autocomplete="new-password">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Create account</button>
      </form>

      <p class="auth-footer-link">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
      </p>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", async () => {
    try {
      await AquaWatch.loadNeighborhoods();
      AquaWatch.populateNeighborhoodSelect(document.getElementById("neighborhood"));
    } catch (e) {
      console.error(e);
    }
  });

  document.getElementById("register-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const alertContainer = document.getElementById("alert-container");
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const neighborhoodId = parseInt(document.getElementById("neighborhood").value, 10);
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm-password").value;

    if (password !== confirm) {
      AquaWatch.showAlert(alertContainer, "Passwords do not match.", "error");
      return;
    }

    try {
      await API.register({ name, email, password, neighborhoodId });
      AquaWatch.showAlert(alertContainer, "Account created! Redirecting…", "success");
      setTimeout(() => { window.location.href = "{{ route('water-feeds') }}"; }, 1500);
    } catch (err) {
      AquaWatch.showAlert(alertContainer, err.message, "error");
    }
  });
</script>
@endpush
