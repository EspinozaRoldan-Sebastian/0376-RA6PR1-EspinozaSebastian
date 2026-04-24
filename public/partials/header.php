<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/security.php';

if (!isset($user)) {
    $user = currentUser();
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkTracker</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <a href="profile.php" style="color: var(--gray-600); text-decoration: none; font-weight: 500;">Perfil</a>
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                    <a href="admin/index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Admin</a>
                    <?php endif; ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" style="background: none; border: none; color: var(--gray-600); cursor: pointer; font-weight: 500;">
                            Sortir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>