<?php

// Import QUIQQER Bootstrap
define('QUIQQER_SYSTEM', true);
$packagesDir = str_replace('quiqqer/log/bin', '', dirname(__FILE__));
require_once $packagesDir . '/header.php';

if (!QUI::getUserBySession()->getPermission('quiqqer.packages.quiqqerlog.canUse')) {
    exit;
}

$logDir = VAR_DIR . 'log/';

$requestedLogName = urldecode(filter_var($_GET['log'], FILTER_SANITIZE_STRING));
$requestedLogPath = $logDir . $requestedLogName;

if (!file_exists($requestedLogPath)
    || is_dir($requestedLogPath)
    || dirname($requestedLogPath) . '/' != $logDir
) {
    exit;
}

header("Content-Type: text/calendar; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$requestedLogName\"");

readfile($requestedLogPath);

exit;
