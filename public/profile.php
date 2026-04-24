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

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El meu Perfil - WorkTracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="dashboard.php" class="logo">
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
                    <a href="admin/index.php" class="btn btn-sm btn-outline" style="width: auto;">Admin</a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-sm btn-outline" style="width: auto;">Inici</a>
                    <a href="my-entries.php" class="btn btn-sm btn-outline" style="width: auto;">Els meus fitxatges</a>
                    <a href="profile.php" class="btn btn-sm btn-primary" style="width: auto;">Perfil</a>
                    <form method="POST" action="/0376-RA6PR1-EspinozaSebastian/public/dashboard.php" style="display: inline; margin-left: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" class="btn btn-sm btn-outline" style="width: auto;">Tancar sessió</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="container page">
        <h1 class="page-title">El meu Perfil</h1>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo e($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo e($error) ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            
            <!-- CARD 1: DADES DEL PERFIL -->
            <div class="card">
                <h3 class="mb-3">Dades del perfil</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-input" value="<?php echo e($userData['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Correu electrònic</label>
                        <input type="email" class="form-input" value="<?php echo e($userData['email'] ?? '') ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Departament</label>
                        <input type="text" name="department" class="form-input" value="<?php echo e($userData['department'] ?? '') ?>">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">Desar canvis</button>
                </form>
            </div>

            <!-- CARD 2: CANVIAR CONTRASENYA -->
            <div class="card">
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
