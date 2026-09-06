<?php
session_start();
require_once __DIR__ . '/../config/database.php';  // Ruta ajustada
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';  // Ruta ajustada

// Obtener la instancia del monitor
$monitor = PrometheusMonitor::getInstance();

// Función para obtener la IP del cliente
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ip = getClientIP();
    
    if (empty($username) || empty($password)) {
        $error = 'No pueden quedar vacios los campos de usuario y contraseña';
        $monitor->registerFailure('empty_username', $ip);
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // ✅ LOGIN EXITOSO
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if ($monitor->registerSuccess($user['username'])) {
                $_SESSION['monitor_session_counted'] = true;
            }
            
            // Registrar en el log de auditoría
            $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, ip, details) VALUES (?, 'login_success', ?, ?)");
            $stmt->execute([$user['id'], $ip, "Login exitoso para usuario: " . $user['username']]);
            
            header('Location: dashboard.php');
            exit;
        } else {
            // ❌ LOGIN FALLIDO
            $error = 'Usuario o contraseña incorrectos';
            
            $monitor->registerFailure($username, $ip);
            
            $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, ip, details) VALUES (NULL, 'login_failed', ?, ?)");
            $stmt->execute([$ip, "Intento fallido para usuario: " . $username]);
            
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nombre de usuario o Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Ingresa</button>
            </form>
            
            <p class="link">No tienes cuenta? <a href="register.php">Registrate</a></p>
        </div>
    </div>
</body>
</html>