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
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Funció protegir pàgines
function requireAuth(?int $requiredRole = null): void {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }

    if ($requiredRole !== null && !hasRole($requiredRole)) {
        http_response_code(403);
        die("Accés prohibit");
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