<?php
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../config/auth.php';
    requireAuth(ROLE_ADMIN);
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - WorkTracker</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    WorkTracker <span style="font-size: 0.75rem; background: var(--primary-light); color: var(--primary); padding: 0.25rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">ADMIN</span>
                </a>

                <div>
                    <span style="margin-right: 1rem; color: var(--gray-600);"><?php echo htmlspecialchars(currentUser()['name']) ?></span>
                    <a href="../dashboard.php" style="color: var(--gray-600); margin-right: 1rem; text-decoration: none;">← Tornar</a>
                    <form method="POST" action="/0376-RA6PR1-EspinozaSebastian/public/dashboard.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" style="background: none; border: none; color: var(--gray-600); cursor: pointer; font-weight: 500;">
                            Sortir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="index.php" <?php echo $currentPage == 'index.php' ? 'class="active"' : '' ?>>Dashboard</a></li>
                <li><a href="projects.php" <?php echo $currentPage == 'projects.php' ? 'class="active"' : '' ?>>Projectes</a></li>
                <li><a href="users.php" <?php echo $currentPage == 'users.php' ? 'class="active"' : '' ?>>Usuaris</a></li>
                <li><a href="reports.php" <?php echo $currentPage == 'reports.php' ? 'class="active"' : '' ?>>Reports</a></li>
            </ul>
        </div>
    </nav>