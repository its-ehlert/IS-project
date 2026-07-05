# AquaWatch Nairobi

Crowdsourced water supply monitoring system for Nairobi residents. Built with Laravel, Blade, Eloquent, and MySQL/MariaDB.

## Features

- **Community reporting** — Residents submit water status (Available, Low Pressure, No Water, Scheduled)
- **Live feed** — Browse and filter reports by neighbourhood and status
- **Notifications** — Subscribe to neighbourhoods and receive supply change alerts
- **Dashboard** — Weekly trends, status charts, and neighbourhood summaries
- **Admin panel** — Verify/delete reports, manage users, system monitoring

## Setup

1. Install PHP dependencies: `composer install`
2. Copy `.env.example` to `.env` (if not already present) and set your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aquawatch
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Generate an app key: `php artisan key:generate`
4. Create the `aquawatch` database, then run migrations and seed demo data:
   ```
   php artisan migrate --seed
   ```
5. Start the app: `php artisan serve`
6. Visit `http://127.0.0.1:8000`

No Apache/XAMPP required — everything runs through Laravel's built-in server.

## Demo accounts

| Role     | Email                 | Password   |
|----------|-----------------------|------------|
| Resident | demo@aquawatch.ke     | demo123    |
| Admin    | admin@aquawatch.ke    | admin123   |

## Project structure

```
├── app/
│   ├── Http/Controllers/    # Auth, Report, Notification, Dashboard, Neighborhood, Admin/*
│   ├── Http/Middleware/     # EnsureAdmin, EnsureApiAuthenticated
│   ├── Http/Resources/      # UserResource, ReportResource, NotificationResource
│   ├── Models/               # User, Neighborhood, Report, Notification
│   └── Services/            # DashboardService (raw-SQL aggregations), NotificationDispatcher
├── database/
│   ├── migrations/          # Matches the original schema.sql exactly
│   └── seeders/             # Reproduces the original demo dataset
├── resources/views/
│   ├── layouts/             # app.blade.php, auth.blade.php, admin.blade.php
│   ├── pages/                # public pages (reports, dashboard, notifications, login, register)
│   └── admin/                # admin dashboard, reports, users, monitoring
├── routes/web.php            # page routes + /api/* JSON endpoints (session-authenticated)
├── public/css, public/js     # original stylesheets and frontend JS, served as static assets
└── legacy/                   # the original XAMPP/PHP/MySQL implementation, kept for reference
```

## Technology stack

- **Backend:** Laravel 13, PHP 8.4, Eloquent ORM
- **Frontend:** Blade templates, the original HTML/CSS/JavaScript, Chart.js
- **Database:** MySQL/MariaDB
- **Auth:** Laravel session auth + CSRF protection

## Legacy version

The original XAMPP-based PHP/MySQL implementation this project was migrated from is preserved under [`legacy/`](legacy/) for reference. It is not wired up to run and can be safely removed once you're confident the Laravel version covers everything you need.
