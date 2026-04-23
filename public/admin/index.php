<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuth(ROLE_ADMIN);

$user = currentUser();

// Estadístiques dashboard
$totalEmployees = db()->query("SELECT COUNT(*) FROM users WHERE role = 3 AND is_active = 1")->fetchColumn();
$totalProjects = db()->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn();
$todayHours = db()->query("SELECT COALESCE(SUM(total_hours), 0) FROM time_entries WHERE DATE(clock_in) = CURDATE()")->fetchColumn();
$activeUsers = db()->query("SELECT COUNT(DISTINCT user_id) FROM time_entries WHERE DATE(clock_in) = CURDATE() AND clock_out IS NULL")->fetchColumn();

include __DIR__ . '/header.php';
?>

<div class="container mt-3">
    <h1>Panell d'Administració</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="value"><?php echo $totalEmployees ?></div>
            <div class="label">Empleats actius</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $totalProjects ?></div>
            <div class="label">Projectes actius</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo number_format($todayHours, 1) ?></div>
            <div class="label">Hores totals avui</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $activeUsers ?></div>
            <div class="label">Usuaris fitxats ara</div>
        </div>
    </div>

    <div style="background: white; padding: 1.5rem; border-radius: 12px;">
        <h3>Benvingut al panell d'administració</h3>
        <p style="color: var(--gray-600); margin-top: 0.5rem;">
            Des d'aquí pots gestionar tots els usuaris, projectes i veure els reports generals de l'empresa.
        </p>
    </div>
</div>

</body>
</html>