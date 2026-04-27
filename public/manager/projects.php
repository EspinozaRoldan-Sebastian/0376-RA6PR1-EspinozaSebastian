<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';

// Només Manager i Admin poden accedir
if (!hasRole(ROLE_MANAGER) && !hasRole(ROLE_ADMIN)) {
    http_response_code(403);
    die("Accés prohibit");
}

// Obtenir projectes amb hores consumides
$projects = db()->query("
    SELECT 
        p.id,
        p.name,
        p.client,
        p.budgeted_hours,
        COALESCE(SUM(te.total_hours), 0) as used_hours
    FROM projects p
    LEFT JOIN time_entries te ON p.id = te.project_id
    WHERE p.is_active = 1
    GROUP BY p.id, p.name, p.client, p.budgeted_hours
    ORDER BY p.name
")->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<div class="container page">
    <h1 class="page-title">Projectes</h1>

    <div class="card">
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Client</th>
                        <th>Hores pressupostades</th>
                        <th>Hores consumides</th>
                        <th>% Consum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <?php
                    $percent = $p['budgeted_hours'] > 0 ? ($p['used_hours'] / $p['budgeted_hours']) * 100 : 0;
                    $colorClass = '';
                    if ($percent >= 80) $colorClass = 'color: var(--danger); font-weight: 700;';
                    elseif ($percent >= 60) $colorClass = 'color: var(--warning); font-weight: 600;';
                    ?>
                    <tr>
                        <td><strong><?php echo e($p['name']) ?></strong></td>
                        <td><?php echo e($p['client']) ?></td>
                        <td><?php echo number_format($p['budgeted_hours'], 1) ?> h</td>
                        <td><?php echo number_format($p['used_hours'], 1) ?> h</td>
                        <td style="<?php echo $colorClass ?>">
                            <?php echo number_format($percent, 0) ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>