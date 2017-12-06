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
        // Create Log Archive Directory
        $logArchiveDir = Manager::LOG_ARCHIVE_DIR;
        if (!is_dir($logArchiveDir)) {
            mkdir($logArchiveDir);
        }

        // Setup cron for default log deletion every 180 days
        $CronManager = new QUI\Cron\Manager();
        $cronName    = QUI::getLocale()->get('quiqqer/log', 'cron.cleanup.delete.title');

        $Config             = QUI::getPackage('quiqqer/log')->getConfig();
        $isCronAlreadySetup = $Config->getValue('log_cleanup', 'isCronAlreadySetup');

        if (!$CronManager->isCronSetUp($cronName) && !$isCronAlreadySetup) {
            try {
                $CronManager->add($cronName, 0, 0, 180, 0, 0);

                $Config->setValue('log_cleanup', 'isCronAlreadySetup', 1);
                $Config->save();
            } catch (QUI\Exception $exception) {
                $msg = QUI::getLocale()->get('quiqqer/log', 'error.setup.cron');
                QUI\System\Log::addError($msg);
                QUI::getMessagesHandler()->addError($msg);
            }
        }
    }
}
