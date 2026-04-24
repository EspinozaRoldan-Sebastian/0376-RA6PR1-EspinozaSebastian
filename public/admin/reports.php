<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuth(ROLE_ADMIN);

// Filtres
$period = $_GET['period'] ?? 'week';

// Hores per projecte
$projectsHours = db()->query("
    SELECT p.name, p.budgeted_hours, COALESCE(SUM(te.total_hours), 0) as used_hours
    FROM projects p
    LEFT JOIN time_entries te ON p.id = te.project_id
    WHERE p.is_active = 1
    GROUP BY p.id, p.name, p.budgeted_hours
    ORDER BY used_hours DESC
")->fetchAll();

// Hores per dia de la setmana actual
$weekHours = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("monday this week +$i days"));
    $stmt = db()->prepare("SELECT COALESCE(SUM(total_hours), 0) FROM time_entries WHERE DATE(clock_in) = ?");
    $stmt->execute([$date]);
    $hours = $stmt->fetchColumn();
    $weekHours[date('D', strtotime($date))] = round($hours, 1);
}

include __DIR__ . '/header.php';
?>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1>Reports</h1>
        <div>
            <select onchange="window.location.href='?period='+this.value" class="form-input" style="width: auto; display: inline-block; padding: 0.5rem;">
                <option value="week" <?php echo $period == 'week' ? 'selected' : '' ?>>Setmana actual</option>
                <option value="month" <?php echo $period == 'month' ? 'selected' : '' ?>>Mes actual</option>
            </select>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 12px;">
            <h3 class="mb-2">Hores per Projecte</h3>
            <canvas id="projectsChart" height="250"></canvas>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: 12px;">
            <h3 class="mb-2">Hores per dia</h3>
            <canvas id="weekChart" height="250"></canvas>
        </div>
    </div>

    <div style="background: white; padding: 1.5rem; border-radius: 12px;">
        <h3 class="mb-2">Consum de hores per projecte</h3>
        <div class="table-wrapper" style="border: 0; margin-top: 1rem;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Projecte</th>
                        <th>Hores pressupostades</th>
                        <th>Hores consumides</th>
                        <th>Percentatge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectsHours as $p): ?>
                    <?php $percent = $p['budgeted_hours'] > 0 ? ($p['used_hours'] / $p['budgeted_hours']) * 100 : 0 ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['name']) ?></strong></td>
                        <td><?php echo $p['budgeted_hours'] ?> h</td>
                        <td><?php echo number_format($p['used_hours'], 1) ?> h</td>
                        <td>
                            <div style="width: 100%; height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden;">
                                <div style="width: <?php echo min($percent, 100) ?>%; height: 100%; background: <?php echo $percent > 100 ? 'var(--danger)' : 'var(--primary)' ?>;"></div>
                            </div>
                            <span style="font-size: 0.875rem; color: <?php echo $percent > 100 ? 'var(--danger)' : 'var(--gray-600)' ?>">
                                <?php echo number_format($percent, 0) ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
// Gràfic projectes
new Chart(document.getElementById('projectsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($projectsHours, 'name')) ?>,
        datasets: [{
            label: 'Hores consumides',
            data: <?php echo json_encode(array_column($projectsHours, 'used_hours')) ?>,
            backgroundColor: '#1D9E75',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// Gràfic setmana
new Chart(document.getElementById('weekChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_keys($weekHours)) ?>,
        datasets: [{
            label: 'Hores totals',
            data: <?php echo json_encode(array_values($weekHours)) ?>,
            borderColor: '#1D9E75',
            backgroundColor: 'rgba(29, 158, 117, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});
</script>

</body>
</html>
