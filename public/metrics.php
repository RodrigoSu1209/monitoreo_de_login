<?php
// public/metrics.php - Endpoint para que Prometheus recolecte métricas

// Activar visualización de errores (para depuración)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar nuestra clase de monitoreo
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';

// Obtener la instancia del monitor (Singleton)
$monitor = PrometheusMonitor::getInstance();

// Establecer el header correcto para Prometheus
header('Content-Type: text/plain; version=0.0.4; charset=utf-8');

// Devolver las métricas
echo $monitor->getMetrics();
?>