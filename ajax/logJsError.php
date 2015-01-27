<?php

/**
 * Log a javascript error
 *
 * @param String $msg
 * @param String $url
 * @param Integer|String $linenumber
 */
function package_quiqqer_log_ajax_logJsError($msg, $url, $linenumber)
{
    $error  = "File: {$url}\n";
    $error .= "Line Number: $linenumber\n";
    $error .= "Error: {$msg}\n\n";

    \QUI\System\Log::addError( $error, 'js_errors' );
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_logJsError',
    array( 'msg', 'url', 'linenumber' ),
    'Permission::checkAdminUser'
);
