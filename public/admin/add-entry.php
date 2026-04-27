<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';
requireAuth(ROLE_ADMIN);

$success = '';
$error = '';

$users = db()->query("SELECT id, name FROM users WHERE role = 3 AND is_active = 1 ORDER BY name")->fetchAll();
$projects = db()->query("SELECT id, name FROM projects WHERE is_active = 1 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        die("Petició invàlida");
    }

    $userId = intval($_POST['user_id']);
    $date = $_POST['date'];
    $clockIn = $_POST['clock_in'];
    $clockOut = $_POST['clock_out'];
    $projectId = isset($_POST['project_id']) && $_POST['project_id'] > 0 ? intval($_POST['project_id']) : null;

    $start = new DateTime($date . ' ' . $clockIn);
    $end = new DateTime($date . ' ' . $clockOut);
    $totalHours = $end->getTimestamp() - $start->getTimestamp();
    $totalHours = $totalHours / 3600;

    if ($totalHours <= 0) {
        $error = "L'hora de sortida ha de ser posterior a l'hora d'entrada";
    } else {
        $stmt = db()->prepare("INSERT INTO time_entries (user_id, project_id, clock_in, clock_out, total_hours) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $projectId,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s'),
            $totalHours
        ]);

        header("Location: users.php?success=1");
        exit;
    }
}

include __DIR__ . '/header.php';
?>

<div class="container page">
    <h1 class="page-title">Afegir fitxatge manual</h1>

    <?php if ($error): ?>
    <div class="alert alert-danger">
        <?php echo e($error) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">

            <div class="form-group">
                <label class="form-label">Empleat</label>
                <select name="user_id" class="form-input" required>
                    <option value="">Selecciona empleat</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id'] ?>"><?php echo e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Data</label>
                <input type="date" name="date" class="form-input" value="<?php echo date('Y-m-d') ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Hora entrada</label>
                    <input type="time" name="clock_in" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora sortida</label>
                    <input type="time" name="clock_out" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Projecte (opcional)</label>
                <select name="project_id" class="form-input">
                    <option value="">Sense projecte assignat</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?php echo $p['id'] ?>"><?php echo e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="users.php" class="btn btn-outline" style="flex: 1;">Cancel·lar</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar fitxatge</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>