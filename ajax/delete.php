<?php

/**
 * System logs löschen
 *
 * @param string $file - Name of the log
 * @return array
 */
function package_quiqqer_log_ajax_delete($file)
{
    $log = VAR_DIR . 'log/' . $file;
    $log = \QUI\Utils\Security\Orthos::clearPath($log);

    \QUI\Utils\System\File::unlink($log);
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_delete',
    array('file'),
    'Permission::checkSU'
);
