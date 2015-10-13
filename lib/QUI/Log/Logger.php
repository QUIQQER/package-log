<?php

/**
 * This file contains QUI\Log\Logger class
 */

namespace QUI\Log;

use QUI;
use QUI\System\Log;
use Monolog;

/**
 * QUIQQER logging service
 *
 * @package quiqqer/log
 * @author  www.pcsg.de (Henning Leutz)
 * @licence For copyright and license information, please view the /README.md
 */
class Logger
{
    /**
     * log events?
     *
     * @var Bool|null
     */
    static $_logOnFireEvent = null;

    /**
     * Monolog Logger
     *
     * @var \Monolog\Logger
     */
    static $Logger = null;

    /**
     * which levels should be loged
     *
     * @var Array
     */
    static $logLevels
        = array(
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
     * event on fire event
     * log all events?
     *
     * @param Array $params
     */
    static function logOnFireEvent($params)
    {
        if (is_null(self::$_logOnFireEvent)) {
            self::$_logOnFireEvent = 0;

            if (self::getPlugin()->getSettings('log', 'logAllEvents')) {
                self::$_logOnFireEvent = 1;
            }
        }

        if (!self::$_logOnFireEvent) {
            return;
        }

        $arguments = func_get_args();

        if (isset($arguments[0])
            && isset($arguments[0]['event'])
            && $arguments[0]['event'] == 'userLoad'
        ) {
            return;
        }

        if ($arguments[0] == 'userLoad') {
            return;
        }

        $Logger = self::getLogger();
        $User   = \QUI::getUserBySession();

        $context = array(
            'username'  => $User->getName(),
            'uid'       => $User->getId(),
            'arguments' => $arguments
        );

        $arguments = func_get_args();

        if (isset($arguments[0]['event'])) {
            $event = $arguments[0]['event'];
        } else {
            $event = $arguments[0];
        }

        $Logger->addInfo('event log ' . $event, $context);
    }


    /**
     * event : on header loaded -> set error reporting
     */
    static function onHeaderLoaded()
    {
        if (self::$logLevels['debug'] || DEVELOPMENT == 1) {
            error_reporting(E_ALL);

            if (DEVELOPMENT == 1) {
                error_reporting(E_ALL ^ E_DEPRECATED);
            }

            return;
        }

        $errorlevel = error_reporting();
        $errorlevel = $errorlevel & E_ERROR;

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel & E_WARNING;
        }

        if (self::$logLevels['error']
            || self::$logLevels['critical']
            || self::$logLevels['alert']
        ) {
            $errorlevel = $errorlevel & E_PARSE;
        }

        if (self::$logLevels['notice']) {
            $errorlevel = $errorlevel & E_NOTICE;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel & E_CORE_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel & E_CORE_WARNING;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel & E_COMPILE_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel & E_COMPILE_WARNING;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel & E_USER_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel & E_USER_WARNING;
        }

        if (self::$logLevels['notice']) {
            $errorlevel = $errorlevel & E_USER_NOTICE;
        }

        if (self::$logLevels['info']) {
            $errorlevel = $errorlevel & E_STRICT;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel & E_RECOVERABLE_ERROR;
        }


        error_reporting($errorlevel);
    }

    /**
     * Write a message to the logger
     * event: onLogWrite
     *
     * @param String $message - Log message
     * @param Integer $loglevel - Log::LEVEL_*
     */
    static function write($message, $loglevel = Log::LEVEL_INFO)
    {
        $Logger = self::getLogger();
        $User   = \QUI::getUserBySession();

        $context = array(
            'username' => $User->getName(),
            'uid'      => $User->getId()
        );

        switch ($loglevel) {
            case Log::LEVEL_DEBUG:
                if (self::$logLevels['debug']) {
                    $Logger->addDebug($message, $context);
                }
                break;

            case Log::LEVEL_INFO:
                if (self::$logLevels['info']) {
                    $Logger->addInfo($message, $context);
                }
                break;

            case Log::LEVEL_NOTICE:
                if (self::$logLevels['notice']) {
                    $Logger->addNotice($message, $context);
                }
                break;

            case Log::LEVEL_WARNING:
                if (self::$logLevels['warning']) {
                    $Logger->addWarning($message, $context);
                }
                break;

            case Log::LEVEL_ERROR:
                if (self::$logLevels['error']) {
                    $Logger->addError($message, $context);
                }
                break;

            case Log::LEVEL_CRITICAL:
                if (self::$logLevels['critical']) {
                    $Logger->addCritical($message, $context);
                }
                break;

            case Log::LEVEL_ALERT:
                if (self::$logLevels['alert']) {
                    $Logger->addAlert($message, $context);
                }
                break;

            case Log::LEVEL_EMERGENCY:
                if (self::$logLevels['emergency']) {
                    $Logger->addEmergency($message, $context);
                }
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
        if (self::$Logger) {
            return self::$Logger;
        }

        $Logger = new Monolog\Logger('QUI:Log');

        self::$Logger = $Logger;

        // which levels should be loged
        self::$logLevels = self::getPlugin()->getSettings('log_levels');

        try {
            $Logger->pushHandler(new QUI\Log\Monolog\LogHandler());
        } catch (QUI\Exception $Exception) {

        }

        try {
            self::addChromePHPHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            self::addFirePHPHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            self::addBrowserPHPHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            self::addCubeHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            self::addRedisHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            self::addSyslogUDPHandlerToLogger($Logger);
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        try {
            QUI::getEvents()->fireEvent('quiqqerLogGetLogger', array($Logger));
        } catch (QUI\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        return $Logger;
    }

    /**
     * Return the quiqqer log plugins
     */
    static function getPlugin()
    {
        return \QUI::getPluginManager()->get('quiqqer/log');
    }

    /**
     * Handler
     */

    /**
     * Add a Browser php handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addBrowserPHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPlugin()->getSettings('browser_logs');

        if (!$browser) {
            return;
        }

        $browserphp   = self::getPlugin()->getSettings('browser_logs', 'browserphp');
        $userLogedIn = self::getPlugin()
            ->getSettings('browser_logs', 'userLogedIn');

        if (empty($browserphp) || !$browserphp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        $Logger->pushHandler(new Monolog\Handler\BrowserConsoleHandler());
    }

    /**
     * Add a ChromePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addChromePHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPlugin()->getSettings('browser_logs');

        if (!$browser) {
            return;
        }

        $chromephp   = self::getPlugin()->getSettings('browser_logs', 'chromephp');
        $userLogedIn = self::getPlugin()
            ->getSettings('browser_logs', 'userLogedIn');

        if (empty($chromephp) || !$chromephp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        $Logger->pushHandler(new Monolog\Handler\ChromePHPHandler());
    }

    /**
     * Add a Cube handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addCubeHandlerToLogger(Monolog\Logger $Logger)
    {
        $cube = self::getPlugin()->getSettings('cube');

        if (!$cube) {
            return;
        }

        $server = self::getPlugin()->getSettings('cube', 'server');

        if (empty($server)) {
            return;
        }

        try {
            $Handler = new Monolog\Handler\CubeHandler($server);

            $Logger->pushHandler($Handler);

        } catch (\Exception $Exception) {

        }
    }

    /**
     * Add a FirePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addFirePHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPlugin()->getSettings('browser_logs');

        if (!$browser) {
            return;
        }

        $firephp     = self::getPlugin()->getSettings('browser_logs', 'firephp');
        $userLogedIn = self::getPlugin()
            ->getSettings('browser_logs', 'userLogedIn');

        if (empty($firephp) || !$firephp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        $Logger->pushHandler(new Monolog\Handler\FirePHPHandler());
    }

    /**
     * Add a NewRelic handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addNewRelicToLogger(Monolog\Logger $Logger)
    {
        $newRelic = self::getPlugin()->getSettings('newRelic');

        if (!$newRelic) {
            return;
        }

        $appname = self::getPlugin()->getSettings('newRelic', 'appname');

        if (empty($appname)) {
            return;
        }

        try {
            $Handler = new Monolog\Handler\NewRelicHandler(
                Log::LEVEL_INFO,
                true,
                $appname
            );

            $Logger->pushHandler($Handler);

        } catch (\Exception $Exception) {

        }
    }

    /**
     * Add a Redis handler to the logger, if settings are available
     *
     * @needle predis/predis
     *
     * @param \Monolog\Logger $Logger
     */
    static function addRedisHandlerToLogger(Monolog\Logger $Logger)
    {
        $redis = self::getPlugin()->getSettings('redis');

        if (!$redis) {
            return;
        }

        $server = self::getPlugin()->getSettings('redis', 'server');

        if (empty($server)) {
            return;
        }

        try {
            $Client = new \Predis\Client($server);

            $Handler = new Monolog\Handler\RedisHandler(
                $Client,
                $server
            );

            $Logger->pushHandler($Handler);

        } catch (\Exception $Exception) {

        }
    }

    /**
     * Add a SystelogUPD handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    static function addSyslogUDPHandlerToLogger(Monolog\Logger $Logger)
    {
        $syslog = self::getPlugin()->getSettings('syslogUdp');

        if (!$syslog) {
            return;
        }

        $host = self::getPlugin()->getSettings('syslogUdp', 'host');
        $port = self::getPlugin()->getSettings('syslogUdp', 'port');

        if (empty($host)) {
            return;
        }


        try {
            $Handler = new Monolog\Handler\SyslogUdpHandler($host, $port);

            $Logger->pushHandler($Handler);

        } catch (\Exception $Exception) {

        }
    }
}
