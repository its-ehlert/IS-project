@extends('layouts.app')

@section('content')
    <section class="hero">
      <div class="hero-content">
        <h1>Know when water flows in your neighbourhood</h1>
        <p>
          AquaWatch Nairobi replaces unreliable WhatsApp rumours with crowdsourced,
          real-time water availability reports from your community — so you can plan
          your day with confidence.
        </p>
        <div class="hero-actions">
          <a href="{{ route('water-feeds') }}" class="btn btn-primary btn-lg">Report water status</a>
          <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">View supply trends</a>
        </div>
      </div>
      <div class="hero-visual">
        <p style="margin-bottom: 1.5rem; font-size: 1.125rem; font-weight: 600;">Live community snapshot</p>
        <div class="hero-stats" id="hero-stats">
          <div class="hero-stat">
            <span class="hero-stat-value" id="stat-available">—</span>
            <span class="hero-stat-label">Areas with supply</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-value" id="stat-none">—</span>
            <span class="hero-stat-label">Outages reported</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-value" id="stat-reports">—</span>
            <span class="hero-stat-label">Reports today</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-value" id="stat-neighborhoods">—</span>
            <span class="hero-stat-label">Neighbourhoods tracked</span>
          </div>
        </div>
      </div>
    </section>

    <section class="features">
      <h2>How AquaWatch helps Nairobi residents</h2>
      <div class="feature-grid">
        <article class="feature-card">
          <div class="feature-icon">📍</div>
          <h3>Community reporting</h3>
          <p>Submit water availability updates for your area in seconds. Every report helps neighbours plan ahead.</p>
        </article>
        <article class="feature-card">
          <div class="feature-icon">🔔</div>
          <h3>Supply alerts</h3>
          <p>Get notified when supply changes in neighbourhoods you follow — no more waiting on group chats.</p>
        </article>
        <article class="feature-card">
          <div class="feature-icon">📊</div>
          <h3>Trends & history</h3>
          <p>Explore dashboards showing historical patterns to understand when water is most likely available.</p>
        </article>
        <article class="feature-card">
          <div class="feature-icon">✅</div>
          <h3>Verified updates</h3>
          <p>Administrators review reports to reduce misinformation and keep the platform trustworthy.</p>
        </article>
      </div>
    </section>

    <section>
      <div class="page-header">
        <h2>Recent community reports</h2>
        <p>Latest water status updates from across Nairobi.</p>
      </div>
      <div class="card">
        <div id="recent-reports"></div>
        <div class="card-footer" style="text-align: center;">
          <a href="{{ route('water-feeds') }}" class="btn btn-secondary">View all reports</a>
        </div>
      </div>
    </section>
@endsection

@section('footer')
    <div class="site-footer-inner">
      <div>
        <h4>AquaWatch Nairobi</h4>
        <p>Crowdsourced water supply monitoring for Nairobi communities. Built for transparency and better household planning.</p>
      </div>
      <div>
        <h4>Quick links</h4>
        <p><a href="{{ route('water-feeds') }}">Submit a report</a></p>
        <p><a href="{{ route('dashboard') }}">Supply dashboard</a></p>
        <p><a href="{{ route('notifications') }}">Notifications</a></p>
      </div>
     <!-- <div>
        <h4>Project</h4>
        <p>ICS Project — OOAD methodology</p>
        <p>HTML · CSS · JavaScript · PHP · MySQL</p>
      </div> -->
    </div>
    <div class="footer-bottom">
      &copy; 2026 AquaWatch Nairobi. Community water supply monitoring for Nairobi.
    </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", async () => {
    try {
      const dash = await API.getDashboard();
      const stats = dash.stats;
      document.getElementById("stat-available").textContent = stats.byStatus.available;
      document.getElementById("stat-none").textContent = stats.byStatus.none;
      document.getElementById("stat-reports").textContent = stats.total;
      document.getElementById("stat-neighborhoods").textContent = stats.neighborhoods;

      const reports = await API.getReports({ limit: 5 });
      const recentContainer = document.getElementById("recent-reports");
      if (!reports.reports.length) {
        recentContainer.innerHTML = '<div class="empty-state"><p>No reports yet. Be the first to report!</p></div>';
      } else {
        recentContainer.innerHTML = reports.reports.map((r) => AquaWatch.renderReportItem(r)).join("");
      }
    } catch (e) {
      document.getElementById("recent-reports").innerHTML =
        '<div class="empty-state"><p>Could not load reports. Is the Laravel server running?</p></div>';
    }
  });
</script>
@endpush
