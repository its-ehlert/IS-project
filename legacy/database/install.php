<?php
/**
 * AquaWatch Nairobi — Database installer
 * Open in browser: http://localhost/aquawatch/database/install.php
 * DELETE or protect this file after installation in production.
 */

declare(strict_types=1);

$config = require __DIR__ . '/../config/database.php';
$messages = [];
$errors = [];

function runSqlFile(PDO $pdo, string $path, array &$messages): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Could not read {$path}");
    }
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
    $messages[] = 'Executed: ' . basename($path);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dsn = sprintf('mysql:host=%s;charset=%s', $config['host'], $config['charset']);
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        runSqlFile($pdo, __DIR__ . '/schema.sql', $messages);
        $pdo->exec('USE aquawatch');

        $count = (int) $pdo->query('SELECT COUNT(*) FROM neighborhoods')->fetchColumn();
        if ($count === 0) {
            seedDatabase($pdo, $messages);
        } else {
            $messages[] = 'Database already seeded — skipping sample data.';
        }

        $success = true;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
        $success = false;
    }
} else {
    $success = null;
}

function seedDatabase(PDO $pdo, array &$messages): void
{
    $neighborhoods = [
        ['Westlands', 'Nairobi West'],
        ['Kilimani', 'Nairobi Central'],
        ['Kasarani', 'Nairobi East'],
        ['Embakasi', 'Nairobi East'],
        ['Karen', 'Nairobi South'],
        ['Ruaraka', 'Nairobi North'],
        ['Pipeline', 'Nairobi East'],
        ['Dandora', 'Nairobi East'],
        ['Kibera', 'Nairobi West'],
        ['Parklands', 'Nairobi North'],
    ];

    $stmt = $pdo->prepare('INSERT INTO neighborhoods (name, area) VALUES (?, ?)');
    foreach ($neighborhoods as [$name, $area]) {
        $stmt->execute([$name, $area]);
    }
    $messages[] = 'Inserted ' . count($neighborhoods) . ' neighbourhoods.';

    $demoHash = password_hash('demo123', PASSWORD_DEFAULT);
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);

    $users = [
        ['Demo User', 'demo@aquawatch.ke', $demoHash, 'resident', 'active', 1],
        ['Jane Mwangi', 'jane.m@email.com', $demoHash, 'resident', 'active', 1],
        ['Peter Ochieng', 'peter.o@email.com', $demoHash, 'resident', 'active', 3],
        ['Grace Wanjiku', 'grace.w@email.com', $demoHash, 'resident', 'active', 2],
        ['Admin User', 'admin@aquawatch.ke', $adminHash, 'admin', 'active', null],
        ['Suspended User', 'suspended@email.com', $demoHash, 'resident', 'suspended', 8],
    ];

    $userStmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, role, status, neighborhood_id) VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($users as $u) {
        $userStmt->execute($u);
    }
    $messages[] = 'Inserted ' . count($users) . ' users (demo@aquawatch.ke / demo123, admin@aquawatch.ke / admin123).';

    $reports = [
        [2, 1, 'available', 'Strong flow since 6 AM. Tank is filling normally.', 1],
        [3, 3, 'none', 'No water since yesterday evening. Multiple households affected on Mwiki road.', 1],
        [4, 2, 'low', 'Trickle only. Pressure very low on 4th floor.', 0],
        [2, 4, 'scheduled', 'NCWSC announced supply restoration between 2–6 PM today.', 1],
        [3, 7, 'none', 'Burst pipe reported near stage. No supply in the area.', 1],
        [2, 5, 'available', 'Normal supply. No issues reported.', 1],
        [4, 9, 'low', 'Water available but only for 2 hours in the morning.', 0],
        [3, 6, 'available', 'Good pressure throughout the morning.', 1],
    ];

    $reportStmt = $pdo->prepare(
        'INSERT INTO reports (user_id, neighborhood_id, status, notes, verified, reported_at)
         VALUES (?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? HOUR))'
    );
    $hours = [2, 4, 5, 18, 30, 36, 48, 60];
    foreach ($reports as $i => $r) {
        $reportStmt->execute([$r[0], $r[1], $r[2], $r[3], $r[4], $hours[$i] ?? 1]);
    }
    $messages[] = 'Inserted sample reports.';

    $pdo->exec('INSERT INTO subscriptions (user_id, neighborhood_id) VALUES (1, 1), (1, 3)');

    $notifStmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, neighborhood_id, type, title, message, is_read, created_at)
         VALUES (?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? HOUR))'
    );
    $notifications = [
        [1, 3, 'warning', 'No water in Kasarani', '3 new reports confirm no supply in Kasarani since yesterday.', 0, 3],
        [1, 1, 'success', 'Supply restored in Westlands', 'Community reports indicate normal water flow in Westlands.', 0, 2],
        [1, 4, 'info', 'Scheduled maintenance', 'Embakasi: NCWSC announced supply between 2–6 PM today.', 1, 20],
        [1, null, 'info', 'Welcome to AquaWatch Nairobi', 'Start by reporting water status in your neighbourhood.', 1, 100],
    ];
    foreach ($notifications as $n) {
        $notifStmt->execute($n);
    }
    $messages[] = 'Inserted sample notifications and subscriptions.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Install — AquaWatch Nairobi</title>
  <style>
    body { font-family: Segoe UI, sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1.5rem; background: #f0f9ff; color: #0f172a; }
    h1 { color: #0d6e8a; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin: 1.5rem 0; }
    .ok { color: #16a34a; }
    .err { color: #dc2626; }
    ul { padding-left: 1.25rem; }
    button { background: #0d6e8a; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; cursor: pointer; }
    button:hover { background: #094d61; }
    a { color: #0d6e8a; }
    code { background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 4px; }
  </style>
</head>
<body>
  <h1>💧 AquaWatch — Database Install</h1>
  <p>This script creates the <code>aquawatch</code> MySQL database, tables, and sample data for XAMPP.</p>

  <div class="card">
    <h2>Before you start</h2>
    <ol>
      <li>Start <strong>Apache</strong> and <strong>MySQL</strong> in XAMPP.</li>
      <li>Copy this project folder to <code>C:\xampp\htdocs\aquawatch\</code></li>
      <li>Default MySQL user: <code>root</code> with no password (edit <code>config/database.php</code> if different).</li>
    </ol>
  </div>

  <?php if ($success === true): ?>
    <div class="card">
      <p class="ok"><strong>Installation successful!</strong></p>
      <ul><?php foreach ($messages as $m): ?><li><?= htmlspecialchars($m) ?></li><?php endforeach; ?></ul>
      <p><a href="../index.html">→ Open AquaWatch Nairobi</a></p>
      <p>Login: <code>demo@aquawatch.ke</code> / <code>demo123</code><br>
         Admin: <code>admin@aquawatch.ke</code> / <code>admin123</code></p>
    </div>
  <?php elseif ($success === false): ?>
    <div class="card">
      <p class="err"><strong>Installation failed</strong></p>
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <button type="submit">Install / Reset Database</button>
  </form>
</body>
</html>
