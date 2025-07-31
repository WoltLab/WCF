<?php

/**
 * Checks the system requirements for the upgrade from WoltLab Suite 6.1.
 *
 * @author Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

use wcf\system\WCF;

$requiredPhpExtensions = \array_filter(
    [
        'fileinfo' => \extension_loaded('fileinfo'),
    ],
    static fn($value) => $value === false
);

if ($requiredPhpExtensions !== []) {
    $missingPhpExtensions = \implode(
        ", ",
        \array_map(
            static fn(string $extension) => "'{$extension}'",
            \array_keys($requiredPhpExtensions)
        )
    );

    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Die folgenden PHP-Erweiterungen werden für den Betrieb der Software benötigt: " . $missingPhpExtensions;
    } else {
        $message = "The following PHP extensions are required to run the software: " . $missingPhpExtensions;
    }

    throw new \RuntimeException($message);
}
