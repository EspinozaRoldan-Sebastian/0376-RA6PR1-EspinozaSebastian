<?php
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Correu electrònic o contrasenya incorrectes";
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WorkTracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="#" class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    WorkTracker
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="login-card">
            <h1 class="text-center mb-3">Iniciar Sessió</h1>

            <?php if ($error): ?>
                <div style="color: var(--danger); padding: 0.75rem; border-radius: 8px; background: #fef2f2; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Correu electrònic</label>
                    <input type="email" name="email" class="form-input" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label">Contrasenya</label>
                    <input type="password" name="password" class="form-input" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>

            <div class="text-center mt-3" style="color: var(--gray-500); font-size: 0.875rem;">
                <p>Usuaris de prova:</p>
                <p>admin@worktracker.local / admin123</p>
                <p>empleat@worktracker.local / admin123</p>
            </div>
        </div>
    </main>
</body>
</html>