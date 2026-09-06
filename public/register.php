<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';

$monitor = PrometheusMonitor::getInstance();

$error = '';
$success = '';

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $ip = getClientIP();
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Todos los campos son requeridos';
    } elseif ($password !== $confirm_password) {
        $error = 'La contraseña registrada no coincide';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener un largo mínimo de 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Formato de correo electronico invalido';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Nombre de usuario o email existentes';
            $monitor->registerFailure('registration_' . $username, $ip);
            
            $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, ip, details) VALUES (NULL, 'registration_failed', ?, ?)");
            $stmt->execute([$ip, "Intento de registro fallido para usuario: " . $username . ", email: " . $email]);
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
                $monitor->registerUser($username);
                
                $userId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, ip, details) VALUES (?, 'registration_success', ?, ?)");
                $stmt->execute([$userId, $ip, "Nuevo usuario registrado: " . $username]);
            } else {
                $error = 'Registro fallido. Por favor, intentelo nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Register</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirme su Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Registrese</button>
            </form>
            
            <p class="link">Ya tienes cuenta? <a href="login.php">Ingresa</a></p>
        </div>
    </div>
</body>
</html>