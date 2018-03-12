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
 *
 * @author Henning Leutz (PCSG)
 * @author Jan Wennrich (PCSG)
 */
class Events
{
    /**
     * Event on template get header
     * Extend the template header and register the on error event
     * @param QUI\Template $Template
     */
    public static function onTemplateGetHeader($Template)
    {
        $Package = \QUI::getPackageManager()->getInstalledPackage('quiqqer/log');

        if (!$Package->getConfig()->get('log', 'logFrontendJsErrors')) {
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


    /**
     * Fired when a package's config is saved.
     *
     * If the saved package is "log" and the log archiving is enabled:
     * the function checks if the required php zip extension is installed.
     * If it's not installed an error message is displayed to the user.
     *
     * @param QUI\Package\Package $Package
     *
     * @throws QUI\Exception
     */
    public static function onPackageConfigSave(QUI\Package\Package $Package)
    {
        if ($Package->getName() == "quiqqer/log") {
            $isArchivingEnabled = $Package->getConfig()->getValue('log_cleanup', 'isArchivingEnabled');
            if ($isArchivingEnabled) {
                try {
                    QUI\Archiver\Zip::check();
                } catch (QUI\Exception $exception) {
                    QUI::getMessagesHandler()->addError(QUI::getLocale()->get("quiqqer/log", "error.config.save.zip"));
                }
            }
        }
    }
}
