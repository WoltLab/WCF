<?php

/**
 * Checks the system requirements for the upgrade from WoltLab Suite 6.0.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

use wcf\system\request\RouteHandler;
use wcf\system\WCF;

$checkForTls = function () {
    if (RouteHandler::secureConnection()) {
        return true;
    }

    // @see RouteHandler::secureContext()
    $host = $_SERVER['HTTP_HOST'];
    if ($host === '127.0.0.1' || $host === 'localhost' || \str_ends_with($host, '.localhost')) {
        return true;
    }

    return false;
};

if (!$checkForTls()) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Die Seite wird nicht über HTTPS aufgerufen. Wichtige Funktionen stehen dadurch nicht zur Verfügung, die für die korrekte Funktionsweise der Software erforderlich sind.";
    } else {
        $message = "The page is not accessed via HTTPS. Important features that are required for the proper operation of the software are therefore not available.";
    }

    throw new \RuntimeException($message);
}
