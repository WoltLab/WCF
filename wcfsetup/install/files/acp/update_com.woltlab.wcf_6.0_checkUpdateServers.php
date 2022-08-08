<?php

/**
 * Checks for non-TLS update servers.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use Laminas\Diactoros\Uri;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\system\WCF;

$list = new PackageUpdateServerList();
$list->readObjects();

foreach ($list as $server) {
    $uri = new Uri($server->serverURL);

    if ($uri->getScheme() !== 'https') {
        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            $message = "Der Paketserver '{$uri}' verwendet das unverschl&uuml;sselte http-Protokoll.";
        } else {
            $message = "The package server '{$uri}' uses the unencrypted 'http' scheme.";
        }

        throw new \RuntimeException($message);
    }
    if ($uri->getPort()) {
        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            $message = "Der Paketserver '{$uri}' verwendet nicht den Standard-Port.";
        } else {
            $message = "The package server '{$uri}' uses a non-standard port.";
        }

        throw new \RuntimeException($message);
    }
}
