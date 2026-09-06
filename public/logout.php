<?php
// public/logout.php - Cierre de sesión con monitoreo
session_start();
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';

$monitor = PrometheusMonitor::getInstance();

if (!empty($_SESSION['monitor_session_counted'])) {
	$monitor->logout();
	unset($_SESSION['monitor_session_counted']);
}

session_destroy();
header('Location: login.php');
exit;
?>