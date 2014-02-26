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
     * @todo more handler
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

            self::addCubeHandlerToLogger( $Logger );
            self::addRedisHandlerToLogger( $Logger );
            self::addSyslogUDPHandlerToLogger( $Logger );

        } catch ( \QUI\Exception $Exception )
        {

        }

        return $Logger;
    }

    /**
     * Return the quiqqer log plugins
     */
    static function getPlugin()
    {
        return \QUI::getPlugins()->get( 'quiqqer/log' );
    }

    /**
     * Handler
     */


    /**
     * Add a Cube handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addCubeHandlerToLogger(\Monolog\Logger $Logger)
    {
        $cube = self::getPlugin()->getSettings( 'cube' );

        if ( !$cube ) {
            return;
        }

        $server = self::getPlugin()->getSettings('cube', 'server' );

        if ( empty( $server ) ) {
            return;
        }

        try
        {
            $Handler = new \Monolog\Handler\CubeHandler( $server );

            $Logger->pushHandler( $Handler );

        } catch ( \Exception $Exception )
        {

        }
    }

    /**
     * Add a NewRelic handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addNewRelicToLogger(\Monolog\Logger $Logger)
    {
        $newRelic = self::getPlugin()->getSettings( 'newRelic' );

        if ( !$newRelic ) {
            return;
        }

        $appname = self::getPlugin()->getSettings('newRelic', 'appname' );

        if ( empty( $appname ) ) {
            return;
        }

        try
        {
            $Handler = new \Monolog\Handler\NewRelicHandler(
                \QUI\System\Log::LEVEL_INFO,
                true,
                $appname
            );

            $Logger->pushHandler( $Handler );

        } catch ( \Exception $Exception )
        {

        }
    }

    /**
     * Add a Redis handler to the logger, if settings are available
     *
     * @needle predis/predis
     * @param \Monolog\Logger $Logger
     */
    static function addRedisHandlerToLogger(\Monolog\Logger $Logger)
    {
        $redis = self::getPlugin()->getSettings( 'redis' );

        if ( !$redis ) {
            return;
        }

        $server = self::getPlugin()->getSettings('redis', 'server' );

        if ( empty( $server ) ) {
            return;
        }

        try
        {
            $Client = new \Predis\Client( $server );

            $Handler = new \Monolog\Handler\RedisHandler(
                $Client,
                $server
            );

            $Logger->pushHandler( $Handler );

        } catch ( \Exception $Exception )
        {

        }
    }

    /**
     * Add a SystelogUPD handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addSyslogUDPHandlerToLogger(\Monolog\Logger $Logger)
    {
        $syslog = self::getPlugin()->getSettings( 'syslogUdp' );

        if ( !$syslog ) {
            return;
        }

        $host = self::getPlugin()->getSettings('syslogUdp', 'host' );
        $port = self::getPlugin()->getSettings('syslogUdp', 'port' );

        if ( empty( $host ) ) {
            return;
        }


        try
        {
            $Handler = new \Monolog\Handler\SyslogUdpHandler( $host, $port );

            $Logger->pushHandler( $Handler );

        } catch ( \Exception $Exception )
        {

        }
    }
}
