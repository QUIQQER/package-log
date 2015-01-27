<?php

/**
 * This file contains \QUI\Log\Admin class
 */

namespace QUI\Log;

/**
 * QUIQQER logging service
 *
 * @package quiqqer/log
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

    /**
     * event : on admin load footer
     */
    static function onAdminLoadFooter()
    {
        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/log' );

        if ( $Package->getConfig()->get( 'log', 'logAdminJsErrors' ) )
        {
            echo '<script type="text/javascript">
                  /* <![CDATA[ */

                    require(["qui/QUI", "Ajax"], function(QUI, Ajax)
                    {
                        QUI.addEvent("onError", function(msg, url, linenumber)
                        {
                            Ajax.post("package_quiqqer_log_ajax_logJsError", false, {
                                "package" : "quiqqer/log",
                                msg        : msg,
                                url        : url,
                                linenumber : linenumber
                            });
                        });
                    });

                  /* ]]> */
                  </script>
            ';
        }
    }
}
