<?php

/**
 * Checks the increased system requirements.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$phpVersion = \PHP_VERSION;
$neededPhpVersion = '8.1.2';
if (!\version_compare($phpVersion, $neededPhpVersion, '>=')) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Ihre PHP-Version '{$phpVersion}' ist unzureichend f&uuml;r die Installation dieser Software. PHP-Version {$neededPhpVersion} oder h&ouml;her wird ben&ouml;tigt.";
    } else {
        $message = "Your PHP version '{$phpVersion}' is insufficient for installation of this software. PHP version {$neededPhpVersion} or greater is required.";
    }

    throw new \RuntimeException($message);
}

if (\PHP_INT_SIZE != 8) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Die eingesetzte PHP-Version muss 64-Bit-Ganzzahlen unterst&uuml;tzen.";
    } else {
        $message = "The PHP version must support 64-bit integers";
    }

    throw new \RuntimeException($message);
}
