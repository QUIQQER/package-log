<?php

/**
 * Log a javascript error
 *
 * @param String $errMsg
 * @param String $errUrl
 * @param Integer|String $errLinenumber
 */
function package_quiqqer_log_ajax_logJsError($errMsg, $errUrl, $errLinenumber)
{
    $User = QUI::getUserBySession();

    $error  = "\n";
    $error .= "Time: ". date( 'Y-m-d H:i:s' ) ."\n\n";
    $error .= "File: {$errUrl}\n";
    $error .= "Line Number: {$errLinenumber}\n";
    $error .= "Error: {$errMsg}\n";
    $error .= "\n";
    $error .= "Username: {$User->getName()}\n";
    $error .= "\n================================\n";

    \QUI\System\Log::addError( $error, 'js_errors' );
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_logJsError',
    array( 'errMsg', 'errUrl', 'errLinenumber' )
);