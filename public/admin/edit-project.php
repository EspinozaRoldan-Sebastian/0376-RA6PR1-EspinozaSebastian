<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';
requireAuth(ROLE_ADMIN);

$projectId = intval($_GET['id'] ?? 0);

if (!$projectId) {
    header("Location: projects.php");
    exit;
}

$stmt = db()->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: projects.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        die("Petició invàlida");
    }

    $name = sanitize_string($_POST['name']);
    $description = sanitize_string($_POST['description']);
    $client = sanitize_string($_POST['client']);
    $budgeted = floatval($_POST['budgeted_hours']);
    $active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = db()->prepare("UPDATE projects SET name = ?, description = ?, client = ?, budgeted_hours = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name, $description, $client, $budgeted, $active, $projectId]);

    header("Location: projects.php?success=1");
    exit;
}

include __DIR__ . '/header.php';
?>

<div class="container page">
    <h1 class="page-title">Editar Projecte</h1>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">

            <div class="form-group">
                <label class="form-label">Nom del projecte</label>
                <input type="text" name="name" class="form-input" value="<?php echo e($project['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Client</label>
                <input type="text" name="client" class="form-input" value="<?php echo e($project['client']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Descripció</label>
                <textarea name="description" class="form-input" rows="3"><?php echo e($project['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Hores pressupostades</label>
                <input type="number" name="budgeted_hours" class="form-input" step="0.5" min="0" value="<?php echo e($project['budgeted_hours']) ?>" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_active" id="is_active" <?php echo $project['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" style="margin: 0; font-weight: 500;">Projecte actiu</label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="projects.php" class="btn btn-outline" style="flex: 1;">Cancel·lar</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar canvis</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>