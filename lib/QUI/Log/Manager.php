<?php

/**
 * his file contains \QUI\Log\Manager
 */

namespace QUI\Log;

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
        $dir = VAR_DIR.'log/';
        $list = array();
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

            $mtime = filemtime($dir.$file);

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
}