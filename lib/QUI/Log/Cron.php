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
     * @param Array             $params
     * @param \QUI\Cron\Manager $CronManager
     *
     * @throws QUI\Exception
     */
    static function sendLogsFromLastDay($params, $CronManager)
    {
        if (!isset($params['email'])) {
            throw new QUI\Exception('Need a email parameter to send the log');
        }

        $logDir = VAR_DIR.'log/';

        $Date = new \DateTime();
        $Date->add(\DateInterval::createFromDateString('yesterday'));

        $Mailer = new QUI\Mail\Mailer();
        $LogManager = new QUI\Log\Manager();

        $body = '';
        $result = $LogManager->search($Date->format('Y-m-d').'.log');

        $Mailer->addRecipient($params['email']);
        $Mailer->setSubject('Logs from the last day');
        $Mailer->setBody($body);

        foreach ($result as $entry) {
            if (!isset($entry['file'])) {
                continue;
            }

            $file = $logDir.$entry['file'];

            if (file_exists($file)) {
                $Mailer->addAttachments($file);
            }
        }

        $Mailer->send();
    }
}