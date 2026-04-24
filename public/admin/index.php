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

    <!-- Alertes d'incompliment -->
    <div style="background: white; padding: 1.5rem; border-radius: 12px; margin-top: 2rem;">
        <h3 style="color: var(--danger);">⚠️ Alertes d'incompliment avui</h3>

        <?php
        // Empleats que NO han fitxat encara avui
        $missing = db()->query("
            SELECT id, name FROM users 
            WHERE role = 3 AND is_active = 1 
            AND id NOT IN (SELECT DISTINCT user_id FROM time_entries WHERE DATE(clock_in) = CURDATE())
        ")->fetchAll();

        // Empleats amb menys de 8 hores
        $underHours = db()->query("
            SELECT u.name, SUM(te.total_hours) as total 
            FROM users u
            JOIN time_entries te ON u.id = te.user_id
            WHERE u.role = 3 AND DATE(te.clock_in) = CURDATE() AND te.clock_out IS NOT NULL
            GROUP BY u.id
            HAVING total < 8
        ")->fetchAll();
        ?>

        <?php if (count($missing) > 0): ?>
        <h4 style="margin-top: 1.5rem; margin-bottom: 0.75rem; color: #dc2626;">❌ Empleats sense fitxar entrada</h4>
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <tbody>
                    <?php foreach ($missing as $emp): ?>
                    <tr style="background: #fef2f2;">
                        <td><strong style="color: #dc2626;"><?php echo htmlspecialchars($emp['name']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (count($underHours) > 0): ?>
        <h4 style="margin-top: 1.5rem; margin-bottom: 0.75rem; color: #d97706;">⚠️ Empleats amb menys de 8 hores</h4>
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <tbody>
                    <?php foreach ($underHours as $emp): ?>
                    <tr style="background: #fffbeb;">
                        <td><strong><?php echo htmlspecialchars($emp['name']) ?></strong></td>
                        <td style="color: #d97706;"><?php echo number_format($emp['total'], 1) ?> hores</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (count($missing) == 0 && count($underHours) == 0): ?>
        <div style="text-align: center; padding: 2rem; color: var(--success);">
            ✅ Tots els empleats estan al dia avui
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>