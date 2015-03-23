<?php

/**
 * This file contains \QUI\Log\Events
 */

namespace QUI\Log;

use QUI;

/**
 * Class Events - Main Events
 *
 * @package quiqqer/log
 * @author www.pcsg.de (Henning Leutz)
 */
class Events
{
    /**
     * Event on template get header
     * Extend the template header and register the on error event
     * @param $Template
     */
    static function onTemplateGetHeader($Template)
    {
        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/log' );

        if ( !$Package->getConfig()->get( 'log', 'logFrontendJsErrors' ) ) {
            return;
        }


        $Template->extendHeader(
            '<script type="text/javascript">
              /* <![CDATA[ */
                if ( typeof require !== "undefined" )
                {
                    require(["qui/QUI"], function(QUI)
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
                }
              /* ]]> */
              </script>'
        );
    }

}
