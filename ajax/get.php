<?php

/**
 * System logs
 *
 * @return Array
 */
function package_quiqqer_log_ajax_get($page, $limit, $search='', $sortOn, $sortBy)
{
    $dir   = VAR_DIR .'log/';
    $list  = array();
    $files = \QUI\Utils\System\File::readDir( $dir );

    if ( !isset( $sortOn ) || empty( $sortOn ) ) {
        $sortOn = 'mdate';
    }

    if ( !isset( $sortBy ) || empty( $sortBy ) ) {
        $sortBy = 'DESC';
    }


    rsort( $files );

    foreach ( $files as $file )
    {
        if ( $search && strpos( $file, $search ) === false ) {
            continue;
        }

        $mtime = filemtime( $dir . $file );

        $list[] = array(
            'file'  => $file,
            'mtime' => $mtime,
            'mdate' => date( 'Y-m-d H:i:s', $mtime )
        );
    }

    // sort
    if ( $sortOn == 'mdate' )
    {
        usort($list, function ($a, $b) {
            return ($a['mtime'] < $b['mtime']) ? -1 : 1;
        });

    } else if ( $sortOn == 'file' )
    {
        usort($list, function ($a, $b) {
            return ($a['file'] < $b['file']) ? -1 : 1;
        });
    }

    if ( $sortBy == 'DESC' ) {
        rsort( $list );
    }

    return \QUI\Utils\Grid::getResult( $list, $page, $limit );
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_get',
    array( 'page', 'limit', 'search', 'sortOn', 'sortBy' ),
    'Permission::checkSU'
);
