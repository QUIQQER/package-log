<?php

/**
 * This file contains QUI\Log\Setup
 */

namespace QUI\Log;

use QUI;

/**
 * Setup routine for log package
 * @package QUI\Log\Setup
 */
class Setup
{
    public static function run()
    {
        $logArchiveDir = Manager::LOG_ARCHIVE_DIR;
        if (!is_dir($logArchiveDir)) {
            mkdir($logArchiveDir);
        }
    }
}
