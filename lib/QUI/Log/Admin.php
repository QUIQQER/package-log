<?php

/**
 * This file contains \QUI\Log\Admin class
 */

namespace QUI\Log;

/**
 * QUIQQER logging service
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Admin
{
    /**
     * event : on admin load
     */
    static function onAdminLoad()
    {
        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/log' );

        if ( $Package->getConfig()->get( 'browser_logs', 'debug' ) )
        {
            echo '<script type="text/javascript">
                  /* <![CDATA[ */
                    if ( typeof monitorEvents !== \'undefined\' )
                    {
                        monitorEvents( document.body, \'click\' );
                        monitorEvents( document.body, \'mousedown\' );
                        monitorEvents( document.body, \'dblclick\' );
                    }
                  /* ]]> */
                  </script>
            ';
        }
    }
}
