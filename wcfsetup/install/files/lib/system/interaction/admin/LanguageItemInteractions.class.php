<?php

namespace wcf\system\interaction\admin;

use wcf\data\language\item\LanguageItem;
use wcf\event\interaction\admin\LanguageItemInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for language items.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class LanguageItemInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction(
                'core/languages/items/%s',
                static function (LanguageItem $languageItem) {
                    return $languageItem->isCustomLanguageItem;
                }
            ),
        ]);

        EventHandler::getInstance()->fire(
            new LanguageItemInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return LanguageItem::class;
    }
}
