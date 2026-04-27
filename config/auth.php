<?php
require_once __DIR__ . '/database.php';

// Iniciar sessió segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

// Funció Login
function login(string $email, string $password): bool {
    $stmt = db()->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Actualitzar last_login
        $update = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);

        // Guardar en sessió
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_at'] = time();

        return true;
    }
    return false;
}

// Funció comprovar si està autenticat
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Funció obtenir usuari actual
function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

// Funció comprovar rol
function hasRole(int $role): bool {
    return isLoggedIn() && $_SESSION['user_role'] == $role;
}

// Funció protegir pàgines
function requireAuth(?int $requiredRole = null): void {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }

    if ($requiredRole !== null && !hasRole($requiredRole)) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="ca">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accés prohibit - WorkTracker</title>
            <link rel="stylesheet" href="/0376-RA6PR1-EspinozaSebastian/assets/css/style.css">
        </head>
        <body style="background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
            <div class="card" style="max-width: 450px; text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔒</div>
                <h1 style="color: var(--danger); margin-bottom: 1rem;">Accés prohibit</h1>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">No tens permisos per accedir a aquesta pàgina.</p>
                <a href="/0376-RA6PR1-EspinozaSebastian/public/dashboard.php" class="btn btn-primary" style="width: auto;">Tornar a l'inici</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Funció Logout
function logout(): void {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
