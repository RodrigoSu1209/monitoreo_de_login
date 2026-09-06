<?php
// public/metrics.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../monitor/PrometheusMonitor.php';

$monitor = PrometheusMonitor::getInstance();

header('Content-Type: text/plain; version=0.0.4; charset=utf-8');
echo $monitor->getMetrics();