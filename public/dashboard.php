<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';
requireAuth();

$user = currentUser();

// Comprovar si l'usuari ja ha fitxat entrada avui
$stmt = db()->prepare("SELECT * FROM time_entries WHERE user_id = ? AND DATE(clock_in) = CURDATE() AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
$stmt->execute([$user['id']]);
$activeEntry = $stmt->fetch();

// Acció fitxar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clock_in']) && !$activeEntry) {
        // Fitxar entrada
        $insert = db()->prepare("INSERT INTO time_entries (user_id, clock_in) VALUES (?, NOW())");
        $insert->execute([$user['id']]);
        header("Location: dashboard.php");
        exit;
    }

    if (isset($_POST['clock_out']) && $activeEntry) {
        // Fitxar sortida i calcular hores
        $projectId = isset($_POST['project_id']) && $_POST['project_id'] > 0 ? intval($_POST['project_id']) : null;
        $update = db()->prepare("UPDATE time_entries SET clock_out = NOW(), total_hours = TIMESTAMPDIFF(SECOND, clock_in, NOW()) / 3600, project_id = ? WHERE id = ?");
        $update->execute([$projectId, $activeEntry['id']]);
        header("Location: dashboard.php");
        exit;
    }

    if (isset($_POST['logout'])) {
        logout();
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WorkTracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    WorkTracker
                </div>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                    <a href="admin/index.php" class="btn btn-sm btn-outline" style="width: auto;">Admin</a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-sm btn-primary" style="width: auto;">Inici</a>
                    <a href="my-entries.php" class="btn btn-sm btn-outline" style="width: auto;">Els meus fitxatges</a>
                    <a href="profile.php" class="btn btn-sm btn-outline" style="width: auto;">Perfil</a>
                    <form method="POST" style="display: inline; margin-left: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" class="btn btn-sm btn-outline" style="width: auto;">Tancar sessió</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-greeting text-center">
            <h1>Hola, <?php echo htmlspecialchars($user['name']) ?> 👋</h1>
            <div class="current-time" id="currentTime"></div>

            <?php if ($activeEntry): ?>
                <div class="status-badge status-active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                    Estàs fitxat des de <?php echo date('H:i', strtotime($activeEntry['clock_in'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Estadístiques hores setmana i mes
        $weekHours = db()->prepare("SELECT COALESCE(SUM(total_hours), 0) FROM time_entries WHERE user_id = ? AND YEARWEEK(clock_in, 1) = YEARWEEK(NOW(), 1)");
        $weekHours->execute([$user['id']]);
        $weekTotal = $weekHours->fetchColumn();

        $monthHours = db()->prepare("SELECT COALESCE(SUM(total_hours), 0) FROM time_entries WHERE user_id = ? AND MONTH(clock_in) = MONTH(NOW()) AND YEAR(clock_in) = YEAR(NOW())");
        $monthHours->execute([$user['id']]);
        $monthTotal = $monthHours->fetchColumn();
        ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 2rem 0 1rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary); text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo number_format($weekTotal, 1) ?></div>
                <div style="color: var(--gray-600); font-weight: 500;">Hores aquesta setmana</div>
            </div>
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary); text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo number_format($monthTotal, 1) ?></div>
                <div style="color: var(--gray-600); font-weight: 500;">Hores aquest mes</div>
            </div>
        </div>

        <form method="POST">
            <?php if (!$activeEntry): ?>
                <button type="submit" name="clock_in" class="btn btn-clock btn-clock-in" style="width: 100%; display: block;">
                    ⏰ Entrar
                </button>
            <?php else: ?>

                <div style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
                    <label class="form-label">Selecciona el projecte on has treballat:</label>
                    <select name="project_id" class="form-input">
                        <option value="">Sense projecte assignat</option>
                        <?php
                        $projects = db()->query("SELECT id, name FROM projects WHERE is_active = 1 ORDER BY name")->fetchAll();
                        foreach ($projects as $p):
                        ?>
                        <option value="<?php echo $p['id'] ?>"><?php echo htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="clock_out" class="btn btn-clock btn-clock-out" style="width: 100%; display: block;">
                    ⏹ Sortir
                </button>
            <?php endif; ?>
        </form>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-top: 2rem;">
            <h3>Últims fitxatges d'avui</h3>

            <?php
            $stmt = db()->prepare("SELECT * FROM time_entries WHERE user_id = ? AND DATE(clock_in) = CURDATE() ORDER BY clock_in DESC");
            $stmt->execute([$user['id']]);
            $entries = $stmt->fetchAll();

            if (count($entries) > 0):
            ?>
            <div style="margin-top: 1rem;">
                <?php foreach ($entries as $entry): ?>
                <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
                    <div>
                        <strong><?php echo date('H:i', strtotime($entry['clock_in'])) ?></strong>
                        <?php if ($entry['clock_out']): ?>
                        → <strong><?php echo date('H:i', strtotime($entry['clock_out'])) ?></strong>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($entry['total_hours']): ?>
                        <?php echo number_format($entry['total_hours'], 2) ?> hores
                        <?php else: ?>
                        <em>En curs</em>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color: var(--gray-500); margin-top: 1rem;">Encara no has fet cap fitxatge avui.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Actualitzar hora en temps real
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('currentTime').textContent = timeStr;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>