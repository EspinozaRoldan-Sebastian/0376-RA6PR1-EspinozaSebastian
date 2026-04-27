<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';
requireAuth(ROLE_ADMIN);

$success = '';
$error = '';

// Accions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        die("Petició invàlida");
    }

    // Crear departament
    if (isset($_POST['create'])) {
        $name = sanitize_string($_POST['name']);
        $stmt = db()->prepare("INSERT IGNORE INTO departments (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: departments.php?success=1");
        exit;
    }

    // Eliminar departament
    if (isset($_POST['delete'])) {
        $stmt = db()->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: departments.php");
        exit;
    }
}

// Obtenir tots els departaments
$departments = db()->query("SELECT * FROM departments ORDER BY name")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="container page">
    <h1 class="page-title">Gestió de Departaments</h1>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <button onclick="document.getElementById('createModal').style.display='block'" class="btn btn-primary btn-sm">+ Nou departament</button>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Departament creat correctament</div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px;">
        <div class="table-wrapper" style="border: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom del departament</th>
                        <th style="text-align: right;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                    <tr>
                        <td><strong><?php echo e($d['name']) ?></strong></td>
                        <td style="text-align: right;">
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Segur que vols eliminar aquest departament?')">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                                <input type="hidden" name="id" value="<?php echo $d['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($departments) == 0): ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No hi ha cap departament creat encara
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Crear Departament -->
    <div id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem 1rem;">
        <div style="max-width: 450px; margin: 0 auto; background: white; border-radius: var(--radius-lg); padding: 2rem;">
            <h2>Nou Departament</h2>
            <form method="POST" style="margin-top: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('createModal').style.display='none'" class="btn btn-outline" style="flex: 1;">Cancel·lar</button>
                    <button type="submit" name="create" class="btn btn-primary" style="flex: 1;">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>