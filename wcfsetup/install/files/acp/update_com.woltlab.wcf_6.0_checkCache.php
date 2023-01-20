<?php

/**
 * Checks for Memcached as the configured cache.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

if (CACHE_SOURCE_TYPE === 'memcached') {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Die Unterst&uuml;tzung für Memcached wird mit dem Upgrade entfernt. Es ist notwendig, auf einen anderen Cache umzustellen.";
    } else {
        $message = "The support for Memcached will be removed during the upgrade. It is required to configure a different cache.";
    }

    throw new \RuntimeException($message);
}
