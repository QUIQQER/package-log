<?php

/**
 * This file contains QUI\Log\Setup
 */

namespace QUI\Log;

use QUI;

/**
 * Setup routine for log package
 * @package QUI\Log\Setup
 *
 * @author Jan Wennrich (PCSG)
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

        $CronManager = new QUI\Cron\Manager();
        $Config      = QUI::getPackage('quiqqer/log')->getConfig();

        // Setup cron for default log deletion every 180 days
        $cleanupCronName           = QUI::getLocale()->get('quiqqer/log', 'cron.cleanup.delete.title');
        $isCleanupCronAlreadySetup = $Config->getValue('log_cleanup', 'isCleanupCronAlreadySetup');

        // if locale doesn't exists, we try to import the locale.xml
        if (!QUI::getLocale()->exists('quiqqer/log', 'cron.cleanup.delete.title')) {
            try {
                // locale import
                QUI\Translator::batchImportFromPackage(QUI::getPackage('quiqqer/log'));
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeDebugException($Exception);
            }
        }


        // if cron doesn't installed, we try to execute the setup, so we can add the cron
        if (!QUI::getDataBase()->table()->exist('cron')) {
            try {
                $CronPackage = QUI::getPackage('quiqqer/cron');
                $CronPackage->setup();
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeDebugException($Exception);
            }
        }

        
        if (!$CronManager->isCronSetUp($cleanupCronName) && !$isCleanupCronAlreadySetup) {
            try {
                $CronManager->add($cleanupCronName, "0", "0", "*", "*", 1);

                $Config->setValue('log_cleanup', 'isCleanupCronAlreadySetup', 1);
                $Config->save();
            } catch (QUI\Exception $exception) {
                $msg = QUI::getLocale()->get('quiqqer/log', 'error.setup.cron.deletion');
                QUI\System\Log::addError($msg);
                QUI::getMessagesHandler()->addError($msg);
            }
        }


        // Setup cron for default log archiving every 3 days
        $archivingCronName           = QUI::getLocale()->get('quiqqer/log', 'cron.cleanup.archive.title');
        $isArchivingCronAlreadySetup = $Config->getValue('log_cleanup', 'isArchivingCronAlreadySetup');

        if (!$CronManager->isCronSetUp($archivingCronName) && !$isArchivingCronAlreadySetup) {
            try {
                $CronManager->add($archivingCronName, 0, 4, "*", "*", "*");

                $Config->setValue('log_cleanup', 'isArchivingCronAlreadySetup', 1);
                $Config->save();
            } catch (QUI\Exception $exception) {
                $msg = QUI::getLocale()->get('quiqqer/log', 'error.setup.cron.archiving');
                QUI\System\Log::addError($msg);
                QUI::getMessagesHandler()->addError($msg);
            }
        }
    }
}
