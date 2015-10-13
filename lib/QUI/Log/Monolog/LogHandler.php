<?php

/**
 * This file contains \QUI\Log\Monolog\LogHandler
 */
namespace QUI\Log\Monolog;

use QUI;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class LogHandler
 * @package QUI\Log\Monolog
 */
class LogHandler extends AbstractProcessingHandler
{
    /**
     * @param array $record
     */
    protected function write(array $record)
    {
//        $record['message'];
//        $record['context'];
//        $record['level'];
//        $record['level_name'];
//        $record['channel'];
//        $record['datetime'];
//        $record['extra'];
//        $record['formatted'];

        if (DEBUG_MODE) {
            $filename = 'debug';

        } elseif (DEVELOPMENT) {
            $filename = 'dev';

        } elseif ($record['context']
                  && isset($record['context']['filename'])
                  && $record['context']['filename']
        ) {
            $filename = $record['context']['filename']
                        . date('-Y-m-d');

        } else {
            $filename = QUI\System\Log::levelToLogName($record['level'])
                        . date('-Y-m-d');
        }

        $dir  = VAR_DIR . 'log/';
        $file = $dir . $filename . '.log';


        QUI\Utils\System\File::mkdir($dir);

        $message = "\n[{$record['datetime']->format('Y-m-d H:i:s')}] - " .
                   "{$record['level_name']} - ".
                   $record['message'];

        error_log($message, 3, $file);
    }
}
