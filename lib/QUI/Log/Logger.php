<?php

/**
 * This file contains \QUI\Log\Logger class
 */

namespace QUI\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

/**
 * QUIQQER logging service
 *
 * @author www.namerobot.com (Henning Leutz)
 */

class Logger
{
    static function write($params)
    {
        $Package = \QUI::getPackageHandler()-getPackage( 'quiqqer/log' );

        $message  = $params['message'];
        $loglevel = $params['loglevel'];

        $Logger = new Logger( 'quiqqer_logger' );



        $Logger->pushHandler( new FirePHPHandler() );

    }
}
