<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';
requireAuth(ROLE_ADMIN);

$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    header("Location: users.php");
    exit;
}

// Obtenir dades usuari
$stmt = db()->prepare("SELECT id, name, email, role, department, is_active FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php");
    exit;
}

$roles = [
    1 => 'Administrador',
    2 => 'Manager',
    3 => 'Empleat'
];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        die("Petició invàlida");
    }

    $name = sanitize_string($_POST['name']);
    $email = trim($_POST['email']);
    $role = intval($_POST['role']);
    $department = sanitize_string($_POST['department']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $newPassword = trim($_POST['password'] ?? '');

    if (!validate_email($email)) {
        $error = "Format de correu invàlid";
    } else {
        // Actualitzar dades bàsiques
        $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, role = ?, department = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role, $department, $isActive, $userId]);

        // Actualitzar contrasenya si s'ha introduït
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $error = "La contrasenya ha de tenir mínim 6 caràcters";
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $userId]);
            }
        }

        if (empty($error)) {
            header("Location: users.php?success=1");
            exit;
        }
    }

    // Recarregar dades
    $stmt = db()->prepare("SELECT id, name, email, role, department, is_active FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

include __DIR__ . '/header.php';
?>

<div class="container page">
    <h1 class="page-title">Editar Usuari</h1>

    <?php if ($error): ?>
    <div class="alert alert-danger">
        <?php echo e($error) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">

            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" class="form-input" value="<?php echo e($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Correu electrònic</label>
                <input type="email" name="email" class="form-input" value="<?php echo e($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Rol</label>
                <select name="role" class="form-input" required>
                    <?php foreach ($roles as $id => $name): ?>
                    <option value="<?php echo $id ?>" <?php echo $user['role'] == $id ? 'selected' : '' ?>><?php echo $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Departament</label>
                <input type="text" name="department" class="form-input" value="<?php echo e($user['department']) ?>">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_active" id="is_active" <?php echo $user['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" style="margin: 0; font-weight: 500;">Usuari actiu</label>
            </div>

            <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid var(--border);">

            <div class="form-group">
                <label class="form-label">Nova contrasenya <span style="color: var(--text-muted);">(deixa buit per no canviar)</span></label>
                <input type="password" name="password" class="form-input">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="users.php" class="btn btn-outline" style="flex: 1;">Cancel·lar</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar canvis</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>