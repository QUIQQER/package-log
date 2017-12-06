<?php

/**
 * This file contains \QUI\Log\Cron
 */

namespace QUI\Log;

use QUI;

/**
 * Class Cron / Log Crons
 *
 * @package quiqqer/log
 * @author  www.pcsg.de (Henning Leutz)
 */
class Cron
{
    /**
     * Send the logs from the last day
     *
     * @param array $params
     * @param \QUI\Cron\Manager $CronManager
     *
     * @throws QUI\Exception
     */
    public static function sendLogsFromLastDay($params, $CronManager)
    {
        if (!isset($params['email'])) {
            throw new QUI\Exception('Need a email parameter to send the log');
        }

        $logDir = VAR_DIR . 'log/';

        $Date = new \DateTime();
        $Date->add(\DateInterval::createFromDateString('yesterday'));

        $Mailer     = new QUI\Mail\Mailer();
        $LogManager = new QUI\Log\Manager();

        $body   = '';
        $result = $LogManager->search($Date->format('Y-m-d') . '.log');

        $Mailer->addRecipient($params['email']);
        $Mailer->setSubject('Logs from the last day');
        $Mailer->setBody($body);

        foreach ($result as $entry) {
            if (!isset($entry['file'])) {
                continue;
            }

            $file = $logDir . $entry['file'];

            if (file_exists($file)) {
                $Mailer->addAttachments($file);
            }
        }

        $Mailer->send();
    }


    /**
     * Archive old log files
     *
     * @param $params
     * @param $CronManager
     *
     * @throws QUI\Exception
     */
    public static function archiveLogs($params, $CronManager)
    {
        $Package = QUI::getPackage('quiqqer/log');
        $Config  = $Package->getConfig();

        $minLogAgeForArchiving = $Config->getValue('log_cleanup', 'minLogAgeForArchiving');
        $isLogArchivingEnabled = $Config->getValue('log_cleanup', 'isArchivingEnabled');

        if ($isLogArchivingEnabled) {
            Manager::archiveLogsOlderThanDays($minLogAgeForArchiving);

            // Files are copied into the zip file, so now delete them
            Manager::deleteLogsOlderThanDays($minLogAgeForArchiving);
        }
    }


    /**
     * Deletes old log files (and archives)
     *
     * @param $params
     * @param $CronManager
     */
    public static function deleteLogs($params, $CronManager)
    {
        $Package = QUI::getPackage('quiqqer/log');
        $Config  = $Package->getConfig();

        $minLogAgeForDelete = $Config->getValue('log_cleanup', 'minLogAgeForDelete');

        Manager::deleteLogsOlderThanDays($minLogAgeForDelete);
    }
}
