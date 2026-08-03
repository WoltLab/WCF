<?php

namespace wcf\command\l10n;

use wcf\event\l10n\L10nDefinitionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\l10n\L10nLanguageItemSync;

/**
 * Synchronizes the localized values of all registered l10n definitions with
 * their language variables. The definitions are collected via the
 * `L10nDefinitionCollecting` event and this command is invoked at the end of a
 * package installation or update.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SyncL10nLanguageItems
{
    public function __invoke(): void
    {
        $event = new L10nDefinitionCollecting();
        EventHandler::getInstance()->fire($event);

        foreach ($event->getDefinitions() as $definition) {
            L10nLanguageItemSync::sync($definition);
        }
    }
}
