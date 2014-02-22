<?php

/**
 * This file contains QUI\Log\Logger class
 */

namespace QUI\Log;

/**
 * QUIQQER logging service
 *
 * @author www.namerobot.com (Henning Leutz)
 */

class Logger
{
    /**
     * Monolog Logger
     * @var \Monolog\Logger
     */
    static $Logger = null;

    /**
     * Write a message to the logger
     * event: onLogWrite
     *
     * @param String $message - Log message
     * @param Integer $loglevel - \QUI\System\Log::LEVEL_*
     */
    static function write($message, $loglevel=\QUI\System\Log::LEVEL_INFO)
    {
        $Logger = self::getLogger();
        $User   = \QUI::getUserBySession();

        $context = array(
            'username' => $User->getName(),
            'uid'      => $User->getId()
        );

        switch ( $loglevel )
        {
            case \QUI\System\Log::LEVEL_DEBUG:
                $Logger->addDebug( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_INFO:
                $Logger->addInfo( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_NOTICE:
                $Logger->addNotice( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_WARNING:
                $Logger->addWarning( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_ERROR:
                $Logger->addError( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_CRITICAL:
                $Logger->addCritical( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_ALERT:
                $Logger->addAlert( $message, $context );
            break;

            case \QUI\System\Log::LEVEL_EMERGENCY:
                $Logger->addEmergency( $message, $context );
            break;
        }
    }

    /**
     * Return the Logger object
     *
     * @return \Monolog\Logger
     */
    static function getLogger()
    {
        if ( self::$Logger ) {
            return self::$Logger;
        }

        $PluginManager = \QUI::getPlugins();
        $Plugin        = $PluginManager->get( 'quiqqer/log' );
        $Logger        = new \Monolog\Logger( 'QUI:Log' );

        self::$Logger = $Logger;

        try
        {
            if ( $Plugin->getSettings('browser_logs', 'firephp' ) ) {
                $Logger->pushHandler( new \Monolog\Handler\FirePHPHandler() );
            }

            if ( $Plugin->getSettings('browser_logs', 'chromephp' ) ) {
                $Logger->pushHandler( new \Monolog\Handler\ChromePHPHandler() );
            }

        // @todo more handler


        } catch ( \QUI\Exception $Exception )
        {

        }

        return $Logger;
    }
}
