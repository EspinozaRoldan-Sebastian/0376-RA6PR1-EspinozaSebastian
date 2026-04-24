<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';
requireAuth();

$user = currentUser();

// Obtenir tot l'historial de fitxatges
$entries = db()->prepare("
    SELECT te.*, p.name as project_name 
    FROM time_entries te 
    LEFT JOIN projects p ON te.project_id = p.id
    WHERE te.user_id = ?
    ORDER BY te.clock_in DESC
    LIMIT 100
");
$entries->execute([$user['id']]);
$entries = $entries->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<div class="container mt-3">
    <h1>Els meus fitxatges</h1>

    <div style="background: white; padding: 1.5rem; border-radius: 12px; margin-top: 1.5rem;">
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Sortida</th>
                        <th>Projecte</th>
                        <th>Hores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $e): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($e['clock_in'])) ?></td>
                        <td><?php echo date('H:i', strtotime($e['clock_in'])) ?></td>
                        <td><?php echo $e['clock_out'] ? date('H:i', strtotime($e['clock_out'])) : '<em>En curs</em>' ?></td>
                        <td><?php echo e($e['project_name'] ?: '-') ?></td>
                        <td><?php echo $e['total_hours'] ? number_format($e['total_hours'], 2).' h' : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>