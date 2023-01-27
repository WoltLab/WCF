<?php

/**
 * Checks if an alternative attachment storage is configured.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

if (\defined('ATTACHMENT_STORAGE') && ATTACHMENT_STORAGE) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = \sprintf(
            "Die Unterst&uuml;tzung f&uuml;r einen alternativen Speicherort von Dateianh&auml;ngen wird mit dem Upgrade entfernt. Es ist notwendig die Dateianh&auml;nge in das Standardverzeichnis '%s' zu verschieben und anschlie&szlig;end die PHP-Konstante 'ATTACHMENT_STORAGE' zu entfernen.",
            WCF_DIR . 'attachments/'
        );
    } else {
        $message = \sprintf(
            "The support for an alternative attachment storage location will be removed during the upgrade. It is required to move the attachments into the default directory '%s' and then to remove the PHP constant 'ATTACHMENT_STORAGE'.",
            WCF_DIR . 'attachments/'
        );
    }

    throw new \RuntimeException($message);
}
