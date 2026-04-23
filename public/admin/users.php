<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuth(ROLE_ADMIN);

$roles = [
    1 => 'Administrador',
    2 => 'Manager',
    3 => 'Empleat'
];

// Accions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear usuari
    if (isset($_POST['create'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['email']),
            $password,
            intval($_POST['role']),
            trim($_POST['department'])
        ]);
        header("Location: users.php?success=1");
        exit;
    }

    // Canviar estat activar/desactivar
    if (isset($_POST['toggle'])) {
        $stmt = db()->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: users.php");
        exit;
    }
}

// Obtenir tots els usuaris
$users = db()->query("SELECT id, name, email, role, department, is_active, last_login FROM users ORDER BY name")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1>Gestió d'Usuaris</h1>
        <button onclick="document.getElementById('createModal').style.display='block'" class="btn btn-primary btn-sm">+ Nou usuari</button>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div style="background: #dcfce7; color: #166534; padding: 0.875rem; border-radius: 8px; margin-bottom: 1rem;">
        Usuari creat correctament
    </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Correu</th>
                    <th>Rol</th>
                    <th>Departament</th>
                    <th>Estat</th>
                    <th style="text-align: right;">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($user['name']) ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']) ?></td>
                    <td><?php echo $roles[$user['role']] ?></td>
                    <td><?php echo htmlspecialchars($user['department'] ?: '-') ?></td>
                    <td>
                        <span class="badge <?php echo $user['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?php echo $user['is_active'] ? 'Actiu' : 'Desactivat' ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $user['id'] ?>">
                            <button type="submit" name="toggle" class="btn btn-sm btn-outline">
                                <?php echo $user['is_active'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>
                        <button class="btn btn-sm btn-outline" style="margin-left: 0.5rem;">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Crear Usuari -->
    <div id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem 1rem;">
        <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 2rem;">
            <h2>Nou Usuari</h2>
            <form method="POST" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Correu electrònic</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contrasenya</label>
                    <input type="password" name="password" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <select name="role" class="form-input" required>
                        <option value="3">Empleat</option>
                        <option value="2">Manager</option>
                        <option value="1">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Departament</label>
                    <input type="text" name="department" class="form-input">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('createModal').style.display='none'" class="btn btn-outline" style="flex: 1;">Cancel·lar</button>
                    <button type="submit" name="create" class="btn btn-primary" style="flex: 1;">Crear usuari</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>