<?php
// monitor/PrometheusMonitor.php

require_once __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;
use Prometheus\RenderTextFormat;

class PrometheusMonitor
{
    private static $instance = null;
    private $registry;

    private function __construct()
    {
        // Usar Redis (persistente)
        $adapter = new Redis();
        $this->registry = new CollectorRegistry($adapter);
        $this->registerMetrics();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerMetrics()
    {
        try {
            $this->registry->registerCounter(
                'login',
                'failures_total',
                'Número total de intentos de login fallidos',
                ['username', 'ip']
            );
        } catch (Exception $e) {}

        try {
            $this->registry->registerCounter(
                'login',
                'success_total',
                'Número total de intentos de login exitosos',
                ['username']
            );
        } catch (Exception $e) {}

        try {
            $this->registry->registerCounter(
                'login',
                'registrations_total',
                'Número total de registros de nuevos usuarios',
                ['username']
            );
        } catch (Exception $e) {}

        try {
            $this->registry->registerGauge(
                'login',
                'active_sessions',
                'Número de sesiones activas actualmente'
            );
        } catch (Exception $e) {}
    }

    public function registerFailure($username, $ip)
    {
        try {
            $counter = $this->registry->getCounter('login', 'failures_total');
            $counter->inc(['username' => $username, 'ip' => $ip]);
            return true;
        } catch (Exception $e) {
            error_log("Error registerFailure: " . $e->getMessage());
            return false;
        }
    }

    public function registerSuccess($username)
    {
        try {
            $counter = $this->registry->getCounter('login', 'success_total');
            $counter->inc(['username' => $username]);
            $gauge = $this->registry->getGauge('login', 'active_sessions');
            $gauge->inc();
            return true;
        } catch (Exception $e) {
            error_log("Error registerSuccess: " . $e->getMessage());
            return false;
        }
    }

    public function registerUser($username)
    {
        try {
            $counter = $this->registry->getCounter('login', 'registrations_total');
            $counter->inc(['username' => $username]);
            return true;
        } catch (Exception $e) {
            error_log("Error registerUser: " . $e->getMessage());
            return false;
        }
    }

    public function logout()
    {
        try {
            $gauge = $this->registry->getGauge('login', 'active_sessions');
            $gauge->dec();
            return true;
        } catch (Exception $e) {
            error_log("Error logout: " . $e->getMessage());
            return false;
        }
    }

    public function getMetrics()
    {
        try {
            $renderer = new RenderTextFormat();
            return $renderer->render($this->registry->getMetricFamilySamples());
        } catch (Exception $e) {
            error_log("Error getMetrics: " . $e->getMessage());
            return "# ERROR: " . $e->getMessage();
        }
    }
}