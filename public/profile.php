<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';
requireAuth();

$user = currentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        die("Petició invàlida");
    }

    // Actualitzar perfil
    if (isset($_POST['update_profile'])) {
        $name = sanitize_string($_POST['name']);
        $department = sanitize_string($_POST['department']);

        $stmt = db()->prepare("UPDATE users SET name = ?, department = ? WHERE id = ?");
        $stmt->execute([$name, $department, $user['id']]);
        
        $_SESSION['user_name'] = $name;
        $success = "Perfil actualitzat correctament";
    }

    // Canviar contrasenya
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $stmt = db()->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $dbPass = $stmt->fetchColumn();

        if (!password_verify($current, $dbPass)) {
            $error = "Contrasenya actual incorrecta";
        } elseif ($new !== $confirm) {
            $error = "Les contrasenyes no coincideixen";
        } elseif (strlen($new) < 6) {
            $error = "La contrasenya ha de tenir mínim 6 caràcters";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            $success = "Contrasenya canviada correctament";
        }
    }

    // Recarregar dades usuari
    $user = currentUser();
}

// Obtenir dades actualitzades
$stmt = db()->prepare("SELECT name, email, department, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

include __DIR__ . '/partials/header.php';
?>

<div class="container mt-3">
    <h1>El meu Perfil</h1>

    <?php if ($success): ?>
    <div style="background: #dcfce7; color: #166534; padding: 0.875rem; border-radius: 8px; margin-bottom: 1rem;">
        <?php echo e($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div style="background: #fef2f2; color: #dc2626; padding: 0.875rem; border-radius: 8px; margin-bottom: 1rem;">
        <?php echo e($error) ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 12px;">
            <h3 class="mb-3">Dades del perfil</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-input" value="<?php echo e($userData['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Correu electrònic</label>
                    <input type="email" class="form-input" value="<?php echo e($userData['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Departament</label>
                    <input type="text" name="department" class="form-input" value="<?php echo e($userData['department']) ?>">
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">Desar canvis</button>
            </form>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px;">
            <h3 class="mb-3">Canviar contrasenya</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                
                <div class="form-group">
                    <label class="form-label">Contrasenya actual</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nova contrasenya</label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar nova contrasenya</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>

                <button type="submit" name="change_password" class="btn btn-primary">Canviar contrasenya</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>