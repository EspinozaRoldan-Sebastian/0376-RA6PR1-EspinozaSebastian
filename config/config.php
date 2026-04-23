<?php
// Configuració general de l'aplicació WorkTracker
define('APP_NAME', 'WorkTracker');
define('APP_URL', 'http://localhost/worktracker');
define('APP_ENV', 'development');

// Configuració Base de Dades MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'worktracker');
define('DB_USER', 'worktracker');
define('DB_PASS', 'worktracker123');

// Configuració Sessions
define('SESSION_LIFETIME', 86400); // 24 hores

// Rols d'usuari
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);
define('ROLE_EMPLOYEE', 3);

// Colors oficials
define('PRIMARY_COLOR', '#1D9E75');

// Zona horària
date_default_timezone_set('Europe/Madrid');
?>
