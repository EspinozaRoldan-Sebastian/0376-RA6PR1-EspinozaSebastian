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

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                    <a href="admin/index.php" class="btn btn-sm <?php echo $currentPage == 'index.php' || str_contains($_SERVER['REQUEST_URI'], '/admin/') ? 'btn-primary' : 'btn-outline' ?>" style="width: auto;">Admin</a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-sm <?php echo $currentPage == 'dashboard.php' ? 'btn-primary' : 'btn-outline' ?>" style="width: auto;">Inici</a>
                    <a href="profile.php" class="btn btn-sm <?php echo $currentPage == 'profile.php' ? 'btn-primary' : 'btn-outline' ?>" style="width: auto;">Perfil</a>
                    <form method="POST" action="/0376-RA6PR1-EspinozaSebastian/public/dashboard.php" style="display: inline; margin-left: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token() ?>">
                        <button type="submit" name="logout" class="btn btn-sm btn-outline" style="width: auto;">Tancar sessió</button>
                    </form>
                </div>
            </div>
        </div>
    </header>