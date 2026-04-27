<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuth(ROLE_ADMIN);

$success = '';
$error = '';

// Accions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear projecte
    if (isset($_POST['create'])) {
        $stmt = db()->prepare("INSERT INTO projects (name, description, client, budgeted_hours) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['description']),
            trim($_POST['client']),
            floatval($_POST['budgeted_hours'])
        ]);
        $success = "Projecte creat correctament";
        header("Location: projects.php?success=1");
        exit;
    }

    // Eliminar projecte
    if (isset($_POST['delete'])) {
        $stmt = db()->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: projects.php");
        exit;
    }
}

// Obtenir tots els projectes
$projects = db()->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="container page">
    <h1 class="page-title">Gestió de Projectes</h1>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <button onclick="document.getElementById('createModal').style.display='block'" class="btn btn-primary btn-sm">+ Nou projecte</button>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div style="background: #dcfce7; color: #166534; padding: 0.875rem; border-radius: 8px; margin-bottom: 1rem;">
        Projecte desat correctament
    </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Client</th>
                    <th>Hores pressupostades</th>
                    <th>Estat</th>
                    <th style="text-align: right;">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($project['name']) ?></strong></td>
                    <td><?php echo htmlspecialchars($project['client']) ?></td>
                    <td><?php echo $project['budgeted_hours'] ?> h</td>
                    <td>
                        <span class="badge <?php echo $project['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?php echo $project['is_active'] ? 'Actiu' : 'Inactiu' ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <a href="edit-project.php?id=<?php echo $project['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Segur que vols eliminar aquest projecte?')">
                            <input type="hidden" name="id" value="<?php echo $project['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-sm btn-danger" style="margin-left: 0.5rem;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Crear Projecte -->
    <div id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem 1rem;">
        <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 2rem;">
            <h2>Nou Projecte</h2>
            <form method="POST" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Nom del projecte</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Client</label>
                    <input type="text" name="client" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripció</label>
                    <textarea name="description" class="form-input" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Hores pressupostades</label>
                    <input type="number" name="budgeted_hours" class="form-input" step="0.5" min="0" required>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('createModal').style.display='none'" class="btn btn-outline" style="flex: 1;">Cancel·lar</button>
                    <button type="submit" name="create" class="btn btn-primary" style="flex: 1;">Crear projecte</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>