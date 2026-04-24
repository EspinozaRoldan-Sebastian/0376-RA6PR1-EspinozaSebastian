<?php
require_once __DIR__ . '/config.php';

// Generar token CSRF
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validar token CSRF
function csrf_validate(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validar i netejar dades d'entrada
function sanitize_string(string $value): string {
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Funció helper per sortir html segura
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Cookie Recordar-me
function set_remember_me(int $userId): void {
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    
    $expiry = time() + (86400 * 7); // 7 dies
    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
    
    // Guardar a BD
    $stmt = db()->prepare("INSERT INTO remember_tokens (user_id, selector, validator, expires) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $selector, $hashedValidator, date('Y-m-d H:i:s', $expiry)]);
    
    setcookie('remember_me', $selector.':'.$validator, $expiry, '/', '', isset($_SERVER['HTTPS']), true);
}

function check_remember_me(): void {
    if (!isset($_COOKIE['remember_me']) || isLoggedIn()) return;
    
    [$selector, $validator] = explode(':', $_COOKIE['remember_me'], 2);
    
    $stmt = db()->prepare("SELECT * FROM remember_tokens WHERE selector = ? AND expires > NOW() LIMIT 1");
    $stmt->execute([$selector]);
    $token = $stmt->fetch();
    
    if ($token && password_verify($validator, $token['validator'])) {
        // Restaurar sessió
        $stmt = db()->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$token['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
        }
    }
}
?>