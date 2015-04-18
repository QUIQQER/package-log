<?php

/**
 * Return the log list
 *
 * @param        $page
 * @param        $limit
 * @param string $search
 * @param        $sortOn
 * @param        $sortBy
 *
 * @return array
 */
function package_quiqqer_log_ajax_get(
    $page,
    $limit,
    $search = '',
    $sortOn = 'mdate',
    $sortBy = 'DESC'
) {
    $LogManager = new \QUI\Log\Manager();

    if (!isset($sortOn) || empty($sortOn)) {
        $sortOn = 'mdate';
    }

    if (!isset($sortBy) || empty($sortBy)) {
        $sortBy = 'DESC';
    }

    $LogManager->setAttribute('sortOn', $sortOn);
    $LogManager->setAttribute('sortBy', $sortBy);

    $list = $LogManager->search( $search );

    return \QUI\Utils\Grid::getResult($list, $page, $limit);
}

\QUI::$Ajax->register(
    'package_quiqqer_log_ajax_get',
    array('page', 'limit', 'search', 'sortOn', 'sortBy'),
    'Permission::checkSU'
);
