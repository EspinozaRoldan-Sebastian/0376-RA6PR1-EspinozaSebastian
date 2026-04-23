-- WorkTracker - Base de Dades MySQL
-- Data: 2026
-- Versió 1.0

CREATE DATABASE IF NOT EXISTS worktracker DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE worktracker;

-- Taula Usuaris
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role TINYINT NOT NULL COMMENT '1=Admin, 2=Manager, 3=Empleat',
    department VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- Taula Fitxatges (Punts entrada/sortida)
CREATE TABLE IF NOT EXISTS time_entries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    total_hours DECIMAL(5,2) NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, clock_in)
) ENGINE=InnoDB;

-- Usuari Admin per defecte: email: admin@worktracker.local / contrasenya: admin123
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Administrador', 'admin@worktracker.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Usuari Empleat Test', 'empleat@worktracker.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);