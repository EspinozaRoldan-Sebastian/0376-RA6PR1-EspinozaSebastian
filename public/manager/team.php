    <?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';

// Només Manager i Admin poden accedir
if (!hasRole(ROLE_MANAGER) && !hasRole(ROLE_ADMIN)) {
    http_response_code(403);
    die("Accés prohibit");
}

$user = currentUser();

// Obtenir tots els empleats amb estat de fitxatge avui
$employees = db()->query("
    SELECT 
        u.id, 
        u.name, 
        u.email,
        CASE 
            WHEN EXISTS (SELECT 1 FROM time_entries te WHERE te.user_id = u.id AND DATE(te.clock_in) = CURDATE() AND te.clock_out IS NULL) THEN 1
            WHEN EXISTS (SELECT 1 FROM time_entries te WHERE te.user_id = u.id AND DATE(te.clock_in) = CURDATE()) THEN 2
            ELSE 0 
        END as clock_status,
        COALESCE((SELECT SUM(te.total_hours) FROM time_entries te WHERE te.user_id = u.id AND DATE(te.clock_in) = CURDATE()), 0) as today_hours
    FROM users u
    WHERE u.role = 3 AND u.is_active = 1
    ORDER BY u.name
")->fetchAll();

// Header personalitzat per pàgines dins de /manager/ amb ruta CSS correcta
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkTracker - El meu Equip</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="../dashboard.php" class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    WorkTracker
                </a>

                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                    <a href="../admin/index.php" class="btn btn-sm btn-outline" style="width: auto;">Admin</a>
                    <?php endif; ?>
                    <a href="../dashboard.php" class="btn btn-sm btn-outline" style="width: auto;">Inici</a>
                    <a href="team.php" class="btn btn-sm btn-primary" style="width: auto;">Mi Equipo</a>
                    <a href="../profile.php" class="btn btn-sm btn-outline" style="width: auto;">Perfil</a>
                    <form method="POST" action="/0376-RA6PR1-EspinozaSebastian/public/dashboard.php" style="display: inline; margin-left: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" class="btn btn-sm btn-outline" style="width: auto;">Tancar sessió</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

<div class="container page">
    <h1 class="page-title">El meu Equip</h1>

    <div class="card">
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Empleat</th>
                        <th>Correu</th>
                        <th>Estat avui</th>
                        <th>Hores avui</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><strong><?php echo e($emp['name']) ?></strong></td>
                        <td><?php echo e($emp['email']) ?></td>
                        <td>
                            <?php if ($emp['clock_status'] == 1): ?>
                                <span class="badge badge-success">✅ Fitxat ara</span>
                            <?php elseif ($emp['clock_status'] == 2): ?>
                                <span class="badge badge-warning">⏱️ Ha fitxat avui</span>
                            <?php else: ?>
                                <span class="badge badge-danger">❌ No ha fitxat</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo number_format($emp['today_hours'], 2) ?> h</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>