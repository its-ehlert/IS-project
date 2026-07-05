# AquaWatch Nairobi

Crowdsourced water supply monitoring system for Nairobi residents. Built with HTML, CSS, JavaScript, PHP, and MySQL on XAMPP.

## Features

- **Community reporting** — Residents submit water status (Available, Low Pressure, No Water, Scheduled)
- **Live feed** — Browse and filter reports by neighbourhood and status
- **Notifications** — Subscribe to neighbourhoods and receive supply change alerts
- **Dashboard** — Weekly trends, status charts, and neighbourhood summaries
- **Admin panel** — Verify/delete reports, manage users, system monitoring

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Copy this folder to `C:\xampp\htdocs\aquawatch\`
3. Open `http://localhost/aquawatch/database/install.php` and click **Install Database**.
4. Visit `http://localhost/aquawatch/`

## Demo accounts

| Role     | Email                 | Password   |
|----------|-----------------------|------------|
| Resident | demo@aquawatch.ke     | demo123    |
| Admin    | admin@aquawatch.ke    | admin123   |

## Project structure

```
├── index.html              # Home page
├── pages/                  # Public app pages
├── admin/                  # Admin interface
├── api/                    # PHP REST endpoints
├── includes/               # PHP services (OOAD service layer)
├── config/database.php     # MySQL credentials
├── database/               # schema.sql + install.php
├── css/                    # Stylesheets
├── js/                     # Frontend + api.js client
└── uml/                    # UML diagrams
```

## Technology stack

- **Frontend:** HTML5, CSS3, JavaScript, Chart.js
- **Backend:** PHP 8+ with PDO
- **Database:** MySQL (MariaDB on XAMPP)
- **Server:** Apache via XAMPP

## UML documentation

Open `uml/diagrams.html` in a browser, or render `.puml` files with PlantUML.
