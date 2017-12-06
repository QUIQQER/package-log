<?php

/**
 * this file contains \QUI\Log\Manager
 */

namespace QUI\Log;

use DusanKasan\Knapsack\Collection;
use QUI;
use QUI\Utils\System\File;

/**
 * Class Manager - log manager
 *
 * @package quiqqer/log
 * @author  www.pcsg.de (Henning Leutz)
 */
class Manager extends QUI\QDOM
{
    const LOG_DIR = VAR_DIR . 'log/';

    const LOG_ARCHIVE_DIR = self::LOG_DIR . 'archived/';

    /**
     * constructor
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        // default
        $this->setAttributes(array(
            'sortOn' => 'mdate',
            'sortNy' => 'DESC'
        ));

        $this->setAttributes($params);
    }

    /**
     * Search logs
     * If search string is empty, all logs are returned
     *
     * @param string $search
     *
     * @return array
     */
    public function search($search = '')
    {
        $dir   = self::LOG_DIR;
        $list  = array();
        $files = File::readDir($dir);

        $sortOn = $this->getAttribute('sortOn');
        $sortBy = $this->getAttribute('sortBy');

        if (empty($sortOn)) {
            $sortOn = 'mdate';
        }

        if (empty($sortBy)) {
            $sortBy = 'DESC';
        }

        if (empty($search)) {
            $search = false;
        }


        rsort($files);

        foreach ($files as $file) {
            if ($search && strpos($file, $search) === false) {
                continue;
            }

            $mtime = filemtime($dir . $file);

            $list[] = array(
                'file'  => $file,
                'mtime' => $mtime,
                'mdate' => date('Y-m-d H:i:s', $mtime)
            );
        }

        // sort
        if ($sortOn == 'mdate') {
            usort($list, function ($a, $b) {
                return ($a['mtime'] < $b['mtime']) ? -1 : 1;
            });
        } else {
            if ($sortOn == 'file') {
                usort($list, function ($a, $b) {
                    return ($a['file'] < $b['file']) ? -1 : 1;
                });
            }
        }

        if ($sortBy == 'DESC') {
            rsort($list);
        }

        return $list;
    }


    /**
     * Returns Log files created before the given amount of days
     * (Wrapper for the getLogsOlderThanSeconds()-function)
     *
     * @param int $days - Maximum for the logs in days
     * @return Collection|\DirectoryIterator
     */
    public static function getLogsOlderThanDays($days)
    {
        return self::getLogsOlderThanSeconds($days * 24 * 60 * 60);
    }

    /**
     * Returns Log files created before the given amount of seconds
     *
     * @param int $seconds - Maximum age for the log in seconds
     * @return Collection|\DirectoryIterator
     */
    public static function getLogsOlderThanSeconds($seconds)
    {
        $DirectoryIterator   = new \DirectoryIterator(self::LOG_DIR);
        $DirectoryCollection = \DusanKasan\Knapsack\Collection::from($DirectoryIterator);

        $OlderLogs = $DirectoryCollection->filter(function ($log, $key) use ($seconds) {
            /* @var $log \DirectoryIterator */

            if ($log->isDot() || !$log->isFile() || $log->getExtension() != 'log') {
                return false;
            }

            $logAge = time() - $log->getCTime();

            return ($logAge >= $seconds);
        });

        return $OlderLogs;
    }


    /**
     * Deletes all log files which are older than the given amount of days
     *
     * @param int $days
     */
    public static function deleteLogsOlderThanDays($days)
    {
        $OldLogs = Manager::getLogsOlderThanDays($days);

        foreach ($OldLogs as $OldLog) {
            unlink($OldLog->getRealPath());
        }
    }


    /**
     * Archives all log files which are older than the given amount of days
     *
     * @param int $days
     *
     * @throws QUI\Exception
     */
    public static function archiveLogsOlderThanDays($days)
    {
        $OldLogs = Manager::getLogsOlderThanDays($days);

        $oldLogsGrouped = array();

        foreach ($OldLogs as $OldLog) {
            $date                    = date('Y-m-d', $OldLog->getCTime());
            $oldLogsGrouped[$date][] = $OldLog->getRealPath();
        }

        foreach ($oldLogsGrouped as $date => $oldLogFiles) {
            $zipPath = Manager::LOG_DIR . 'archived/' . $date . '.zip';

            QUI\System\Log::write($zipPath);

            QUI\Archiver\Zip::zipFiles($oldLogFiles, $zipPath);
        }
    }


    /**
     * Returns archived log files created before the given amount of seconds
     *
     * @param int $seconds - Maximum age for the archived logs in seconds
     * @return Collection|\DirectoryIterator
     */
    public static function getArchivedLogsOlderThanSeconds($seconds)
    {
        $DirectoryIterator   = new \DirectoryIterator(self::LOG_ARCHIVE_DIR);
        $DirectoryCollection = \DusanKasan\Knapsack\Collection::from($DirectoryIterator);

        $OlderArchives = $DirectoryCollection->filter(function ($archive, $key) use ($seconds) {
            /* @var $archive \DirectoryIterator */

            if ($archive->isDot() || !$archive->isFile() || $archive->getExtension() != 'zip') {
                return false;
            }

            $archiveAge = time() - $archive->getCTime();

            return ($archiveAge >= $seconds);
        });

        return $OlderArchives;
    }


    /**
     * Returns archived log files created before the given amount of days
     * (Wrapper for the getArchivedLogsOlderThanSeconds()-function)
     *
     * @param int $days - Maximum age for the archived logs in days
     * @return Collection|\DirectoryIterator
     */
    public static function getArchivedLogsOlderThanDays($days)
    {
        return self::getArchivedLogsOlderThanSeconds($days * 24 * 60 * 60);
    }


    /**
     * Deletes all log files which are older than the given amount of days
     *
     * @param int $days
     */
    public static function deleteArchivedLogsOlderThanDays($days)
    {
        $OldArchives = Manager::getArchivedLogsOlderThanDays($days);

        foreach ($OldArchives as $OldArchive) {
            unlink($OldArchive->getRealPath());
        }
    }
}
