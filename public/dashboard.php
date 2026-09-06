<?php
session_start();
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$monitor = PrometheusMonitor::getInstance();

if (isset($_GET['logout'])) {
    $monitor->logout();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Panel de Usuario</h2>
                <a href="?logout=1" class="btn btn-danger">Logout</a>
            </div>
            
            <div class="welcome">
                <h3>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                <p>Se ha registrado de manera correcta</p>
            </div>
            
            <div class="info-box">
                <h4>Información de Sesión</h4>
                <ul>
                    <li><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></li>
                    <li><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <li><strong>Login time:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>