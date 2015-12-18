<?php

/**
 * This file contains \QUI\Log\Admin class
 */

namespace QUI\Log;

/**
 * QUIQQER logging service
 *
 * @package quiqqer/log
 * @author  www.pcsg.de (Henning Leutz)
 */

class Admin
{
    /**
     * event : on admin load
     */
    public static function onAdminLoad()
    {
        $Package = \QUI::getPackageManager()
                       ->getInstalledPackage('quiqqer/log');

        if ($Package->getConfig()->get('browser_logs', 'debug')) {
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
    public static function onAdminLoadFooter()
    {
        $Package = \QUI::getPackageManager()
                       ->getInstalledPackage('quiqqer/log');

        if ($Package->getConfig()->get('log', 'logAdminJsErrors')) {
            echo '<script type="text/javascript">
                  /* <![CDATA[ */

                    require(["qui/QUI", "Ajax"], function(QUI, Ajax)
                    {
                        QUI.addEvent("onError", function(msg, url, linenumber)
                        {
                            console.error(
                                "Message "+ msg +"\n"+
                                "URL "+ url +"\n"+
                                "Linenumber "+ linenumber
                            );

                            require(["Ajax"], function(Ajax)
                            {
                                if ( typeof Ajax === "undefined" ) {
                                    return;
                                }

                                if ( msg === "" &&
                                     url === "" &&
                                     linenumber === "" )
                                {
                                    return;
                                }

                                Ajax.post("package_quiqqer_log_ajax_logJsError", false, {
                                    "package" : "quiqqer/log",
                                    errMsg        : msg,
                                    errUrl        : url,
                                    errLinenumber : linenumber,
                                    browser       : navigator.userAgent.toString()
                                });
                            })
                        });
                    });

                  /* ]]> */
                  </script>
            ';
        }
    }
}
