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
                }
              /* ]]> */
              </script>'
        );
    }

}
