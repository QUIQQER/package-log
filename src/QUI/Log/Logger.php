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
 * @author  Henning Leutz (PCSG)
 * @licence For copyright and license information, please view the /README.md
 */
class Logger
{
    /**
     * log events?
     *
     * @var boolean|null
     */
    protected static $logOnFireEvent = null;

    /**
     * Monolog Logger
     *
     * @var \Monolog\Logger
     */
    public static $Logger = null;

    /**
     * which levels should be loged
     *
     * @var array
     */
    public static $logLevels = [
        'debug'     => true,
        'info'      => true,
        'notice'    => true,
        'warning'   => true,
        'error'     => true,
        'critical'  => true,
        'alert'     => true,
        'emergency' => true
    ];

    /**
     * event on fire event
     * log all events?
     *
     * @param array $params
     */
    public static function logOnFireEvent($params)
    {
        if (self::$logOnFireEvent === null) {
            self::$logOnFireEvent = 0;

            if (self::getPackage()->getConfig()->get('log', 'logAllEvents')) {
                self::$logOnFireEvent = 1;
            }
        }

        if (!self::$logOnFireEvent) {
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
        $User   = QUI::getUserBySession();

        $context = [
            'username'  => $User->getName(),
            'uid'       => $User->getId(),
            'arguments' => $arguments
        ];

        $arguments = func_get_args();

        if (isset($arguments[0]['event'])) {
            $event = $arguments[0]['event'];
        } else {
            $event = $arguments[0];
        }

        $Logger->addInfo('event log '.$event, $context);
    }


    /**
     * event : on header loaded -> set error reporting
     */
    public static function onHeaderLoaded()
    {
        if (self::$logLevels['debug'] || DEVELOPMENT == 1) {
            error_reporting(E_ALL);

            if (DEVELOPMENT == 1) {
                error_reporting(E_ALL ^ E_DEPRECATED);
            }

            return;
        }

        $errorlevel = E_ERROR;

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel | E_WARNING;
        }

        if (self::$logLevels['error']
            || self::$logLevels['critical']
            || self::$logLevels['alert']
        ) {
            $errorlevel = $errorlevel | E_PARSE;
        }

        if (self::$logLevels['notice']) {
            $errorlevel = $errorlevel | E_NOTICE;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel | E_CORE_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel | E_CORE_WARNING;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel | E_COMPILE_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel | E_COMPILE_WARNING;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel | E_USER_ERROR;
        }

        if (self::$logLevels['warning']) {
            $errorlevel = $errorlevel | E_USER_WARNING;
        }

        if (self::$logLevels['notice']) {
            $errorlevel = $errorlevel | E_USER_NOTICE;
        }

        if (self::$logLevels['info']) {
            $errorlevel = $errorlevel | E_STRICT;
        }

        if (self::$logLevels['error']) {
            $errorlevel = $errorlevel | E_RECOVERABLE_ERROR;
        }


        error_reporting($errorlevel);
    }

    /**
     * Write a message to the logger
     * event: onLogWrite
     *
     * @param string $message - Log message
     * @param integer $loglevel - Log::LEVEL_*
     */
    public static function write($message, $loglevel = Log::LEVEL_INFO)
    {
        $Logger = self::getLogger();
        $User   = QUI::getUserBySession();

        $context = [
            'username' => $User->getName(),
            'uid'      => $User->getId()
        ];

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
    public static function getLogger()
    {
        if (self::$Logger) {
            return self::$Logger;
        }

        $Logger = new Monolog\Logger('QUI:Log');

        self::$Logger = $Logger;

        // which levels should be loged
        self::$logLevels = self::getPackage()->getConfig()->get('log_levels');

        $Logger->pushHandler(new QUI\Log\Monolog\LogHandler());


        self::addGraylogToLogger($Logger);
        self::addChromePHPHandlerToLogger($Logger);
        self::addFirePHPHandlerToLogger($Logger);
        self::addBrowserPHPHandlerToLogger($Logger);
        self::addCubeHandlerToLogger($Logger);
        self::addRedisHandlerToLogger($Logger);
        self::addSyslogUDPHandlerToLogger($Logger);

        try {
            QUI::getEvents()->fireEvent('quiqqerLogGetLogger', [$Logger]);
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }

        return $Logger;
    }

    /**
     * Return the quiqqer log plugins
     *
     * @return QUI\Package\Package
     */
    public static function getPackage()
    {
        return QUI::getPackage('quiqqer/log');
    }

    /**
     * Handler
     */

    /**
     * Add a Browser php handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addBrowserPHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPackage()->getConfig()->get('browser_logs');

        if (!$browser) {
            return;
        }

        $browserphp  = self::getPackage()->getConfig()->get('browser_logs', 'browserphp');
        $userLogedIn = self::getPackage()->getConfig()->get('browser_logs', 'userLogedIn');

        if (empty($browserphp) || !$browserphp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        try {
            $Logger->pushHandler(new Monolog\Handler\BrowserConsoleHandler());
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a ChromePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addChromePHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPackage()->getConfig()->get('browser_logs');

        if (!$browser) {
            return;
        }

        $chromephp   = self::getPackage()->getConfig()->get('browser_logs', 'chromephp');
        $userLogedIn = self::getPackage()->getConfig()->get('browser_logs', 'userLogedIn');

        if (empty($chromephp) || !$chromephp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        try {
            $Logger->pushHandler(new Monolog\Handler\ChromePHPHandler());
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a Cube handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addCubeHandlerToLogger(Monolog\Logger $Logger)
    {
        $cube = self::getPackage()->getConfig()->get('cube');

        if (!$cube) {
            return;
        }

        $server = self::getPackage()->getConfig()->get('cube', 'server');

        if (empty($server)) {
            return;
        }

        try {
            $Handler = new Monolog\Handler\CubeHandler($server);
            $Logger->pushHandler($Handler);
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a FirePHP handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addFirePHPHandlerToLogger(Monolog\Logger $Logger)
    {
        $browser = self::getPackage()->getConfig()->get('browser_logs');

        if (!$browser) {
            return;
        }

        $firephp     = self::getPackage()->getConfig()->get('browser_logs', 'firephp');
        $userLogedIn = self::getPackage()->getConfig()->get('browser_logs', 'userLogedIn');

        if (empty($firephp) || !$firephp) {
            return;
        }

        if ($userLogedIn && !QUI::getUserBySession()->getId()) {
            return;
        }

        try {
            $Logger->pushHandler(new Monolog\Handler\FirePHPHandler());
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a graylog handler to the logger, if settings are available
     *
     * @param Monolog\Logger $Logger
     */
    public static function addGraylogToLogger(Monolog\Logger $Logger)
    {
        $graylog = self::getPackage()->getConfig()->get('graylog');

        if (!$graylog) {
            return;
        }

        $server = self::getPackage()->getConfig()->get('graylog', 'server');
        $port   = self::getPackage()->getConfig()->get('graylog', 'port');

        if (empty($server) || empty($port)) {
            return;
        }


        if (!class_exists('\Gelf\Publisher')) {
            $Logger->addInfo(
                '\Gelf\Publisher class is missing. Please install: "graylog2/gelf-php": "~1.2"'
            );

            return;
        }

        try {
            $Publisher = new \Gelf\Publisher(
                new \Gelf\Transport\TcpTransport(
                    self::getPackage()->getConfig()->get('graylog', 'server'),
                    self::getPackage()->getConfig()->get('graylog', 'port')
                )
            );

            $Handler = new Monolog\Handler\GelfHandler($Publisher);

            $Logger->pushHandler($Handler);
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a NewRelic handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addNewRelicToLogger(Monolog\Logger $Logger)
    {
        $newRelic = self::getPackage()->getConfig()->get('newRelic');

        if (!$newRelic) {
            return;
        }

        $appname = self::getPackage()->getConfig()->get('newRelic', 'appname');

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
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a Redis handler to the logger, if settings are available
     *
     * @needle predis/predis
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addRedisHandlerToLogger(Monolog\Logger $Logger)
    {
        $redis = self::getPackage()->getConfig()->get('redis');

        if (!$redis) {
            return;
        }

        $server = self::getPackage()->getConfig()->get('redis', 'server');

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
            $Logger->addNotice($Exception->getMessage());
        }
    }

    /**
     * Add a SystelogUPD handler to the logger, if settings are available
     *
     * @param \Monolog\Logger $Logger
     */
    public static function addSyslogUDPHandlerToLogger(Monolog\Logger $Logger)
    {
        $syslog = self::getPackage()->getConfig()->get('syslogUdp');

        if (!$syslog) {
            return;
        }

        $host = self::getPackage()->getConfig()->get('syslogUdp', 'host');
        $port = self::getPackage()->getConfig()->get('syslogUdp', 'port');

        if (empty($host)) {
            return;
        }


        try {
            $Handler = new Monolog\Handler\SyslogUdpHandler($host, $port);
            $Logger->pushHandler($Handler);
        } catch (\Exception $Exception) {
            $Logger->addNotice($Exception->getMessage());
        }
    }
}
