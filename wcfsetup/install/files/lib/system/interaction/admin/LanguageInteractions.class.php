<?php

namespace wcf\system\interaction\admin;

use wcf\acp\form\LanguageExportForm;
use wcf\data\language\Language;
use wcf\event\interaction\admin\LanguageInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\InteractionEffect;
use wcf\system\interaction\LinkInteraction;
use wcf\system\interaction\RpcInteraction;

/**
 * Interaction provider for languages.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class LanguageInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new LinkInteraction("export", LanguageExportForm::class, "wcf.acp.language.export"),
            new RpcInteraction(
                "setAsDefault",
                "core/languages/%s/default",
                "wcf.acp.language.setAsDefault",
                isAvailableCallback: static fn(Language $language) => !$language->isDefault,
                interactionEffect: InteractionEffect::ReloadList
            ),
            new DeleteInteraction(
                "core/languages/%s",
                static fn(Language $language) => $language->isDeletable()
            )
        ]);

        EventHandler::getInstance()->fire(
            new LanguageInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Language::class;
    }
}
