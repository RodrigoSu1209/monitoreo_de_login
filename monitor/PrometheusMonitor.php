<?php
// monitor/PrometheusMonitor.php

require_once __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;

class PrometheusMonitor {
    private $registry;
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Intentar usar APC si está disponible, si no, InMemory
        if (extension_loaded('apcu')) {
            $adapter = new \Prometheus\Storage\APC();
        } else {
            $adapter = new InMemory();
        }
        $this->registry = new CollectorRegistry($adapter);
        $this->registerMetrics();
    }
    
    private function registerMetrics() {
        // Contador de fallos
        $this->registry->registerCounter(
            'login',
            'failures_total',
            'Número total de intentos de login fallidos',
            ['username', 'ip']
        );
        // Contador de éxitos
        $this->registry->registerCounter(
            'login',
            'success_total',
            'Número total de intentos de login exitosos',
            ['username']
        );
        // Contador de registros
        $this->registry->registerCounter(
            'login',
            'registrations_total',
            'Número total de registros de nuevos usuarios',
            ['username']
        );
        // Gauge de sesiones activas
        $this->registry->registerGauge(
            'login',
            'active_sessions',
            'Número de sesiones activas actualmente'
        );
    }
    
    public function registerFailure($username, $ip) {
        try {
            $counter = $this->registry->getCounter('login', 'failures_total');
            $counter->inc(['username' => $username, 'ip' => $ip]);
            return true;
        } catch (Exception $e) {
            error_log("Error en registerFailure: " . $e->getMessage());
            return false;
        }
    }
    
    public function registerSuccess($username) {
        try {
            $counter = $this->registry->getCounter('login', 'success_total');
            $counter->inc(['username' => $username]);
            $gauge = $this->registry->getGauge('login', 'active_sessions');
            $gauge->inc();
            return true;
        } catch (Exception $e) {
            error_log("Error en registerSuccess: " . $e->getMessage());
            return false;
        }
    }
    
    public function registerUser($username) {
        try {
            $counter = $this->registry->getCounter('login', 'registrations_total');
            $counter->inc(['username' => $username]);
            return true;
        } catch (Exception $e) {
            error_log("Error en registerUser: " . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        try {
            $gauge = $this->registry->getGauge('login', 'active_sessions');
            $gauge->dec();
            return true;
        } catch (Exception $e) {
            error_log("Error en logout: " . $e->getMessage());
            return false;
        }
    }
    
    public function getMetrics() {
        try {
            $renderer = new RenderTextFormat();
            return $renderer->render($this->registry->getMetricFamilySamples());
        } catch (Exception $e) {
            error_log("Error en getMetrics: " . $e->getMessage());
            return "# ERROR: " . $e->getMessage();
        }
    }
}