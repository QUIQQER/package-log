<?php

// Import QUIQQER Bootstrap
define('QUIQQER_SYSTEM', true);
$packagesDir = str_replace('quiqqer/log/bin', '', dirname(__FILE__));
require_once $packagesDir . '/header.php';

$logName = urldecode(filter_var($_GET['log'], FILTER_SANITIZE_STRING));
$logFile = VAR_DIR . 'log/' . $logName;

if (!file_exists($logFile)) {
    exit;
}

header("Content-Type: text/calendar; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$logName\"");
readfile($logFile);

exit;
