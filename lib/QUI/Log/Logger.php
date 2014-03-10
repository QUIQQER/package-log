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
     * which levels should be loged
     * @var Array
     */
    static $logLevels = array(
        'debug'     => true,
        'info'      => true,
        'notice'    => true,
        'warning'   => true,
        'error'     => true,
        'critical'  => true,
        'alert'     => true,
        'emergency' => true
    );

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
                if ( self::$logLevels['debug'] ) {
                    $Logger->addDebug( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_INFO:
                if ( self::$logLevels['info'] ) {
                    $Logger->addInfo( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_NOTICE:
                if ( self::$logLevels['notice'] ) {
                    $Logger->addNotice( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_WARNING:
                if ( self::$logLevels['warning'] ) {
                    $Logger->addWarning( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_ERROR:
                if ( self::$logLevels['error'] ) {
                    $Logger->addError( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_CRITICAL:
                if ( self::$logLevels['critical'] ) {
                    $Logger->addCritical( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_ALERT:
                if ( self::$logLevels['alert'] ) {
                    $Logger->addAlert( $message, $context );
                }
            break;

            case \QUI\System\Log::LEVEL_EMERGENCY:
                if ( self::$logLevels['emergency'] ) {
                    $Logger->addEmergency( $message, $context );
                }
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

        // which levels should be loged
        self::$logLevels = self::getPlugin()->getSettings( 'log_levels' );

        try
        {
            self::addChromePHPHandlerToLogger( $Logger );
            self::addFirePHPHandlerToLogger( $Logger );
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
     * Add a ChromePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addChromePHPHandlerToLogger(\Monolog\Logger $Logger)
    {
        $browser = self::getPlugin()->getSettings( 'browser_logs' );

        if ( !$browser ) {
            return;
        }

        $firephp     = self::getPlugin()->getSettings( 'browser_logs', 'chromephp' );
        $userLogedIn = self::getPlugin()->getSettings( 'browser_logs', 'userLogedIn' );

        if ( empty( $firephp ) || !$firephp ) {
            return;
        }

        if ( $userLogedIn && !\QUI::getUserBySession()->getId() ) {
            return;
        }

        $Logger->pushHandler( new \Monolog\Handler\ChromePHPHandler() );
    }

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
     * Add a FirePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addFirePHPHandlerToLogger(\Monolog\Logger $Logger)
    {
        $browser = self::getPlugin()->getSettings( 'browser_logs' );

        if ( !$browser ) {
            return;
        }

        $firephp     = self::getPlugin()->getSettings( 'browser_logs', 'firephp' );
        $userLogedIn = self::getPlugin()->getSettings( 'browser_logs', 'userLogedIn' );

        if ( empty( $firephp ) || !$firephp ) {
            return;
        }

        if ( $userLogedIn && !\QUI::getUserBySession()->getId() ) {
            return;
        }

        $Logger->pushHandler( new \Monolog\Handler\FirePHPHandler() );
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
