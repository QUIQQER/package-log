<?php

/**
 * System logs
 *
 * @return Array
 */
function package_quiqqer_log_ajax_file($file)
{
    $log = VAR_DIR .'log/'. $file;

    if ( !file_exists( $log ) ) {
        return '';
    }

    return file_get_contents( $log );
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_file',
    array( 'file' ),
    'Permission::checkSU'
);
