<?php

namespace wcf\system\interaction\admin;

use wcf\acp\form\StyleExportForm;
use wcf\data\style\Style;
use wcf\event\interaction\admin\StyleInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\interaction\InteractionEffect;
use wcf\system\interaction\LinkInteraction;
use wcf\system\interaction\RpcInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class StyleInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('core/styles/%s', static fn(Style $object) => !$object->isDefault),
            new RpcInteraction(
                'set-as-default',
                'core/styles/%s/set-as-default',
                'wcf.acp.style.button.setAsDefault',
                isAvailableCallback: static fn(Style $object) => !$object->isDefault,
                interactionEffect: InteractionEffect::ReloadList
            ),
            new RpcInteraction(
                'copy',
                'core/styles/%s/copy',
                'wcf.acp.style.copyStyle',
                InteractionConfirmationType::Custom,
                static fn(Style $object) => WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.style.copyStyle.confirmMessage',
                    ['style' => $object]
                ),
                interactionEffect: InteractionEffect::ReloadList
            ),
            new LinkInteraction('export', StyleExportForm::class, 'wcf.acp.style.exportStyle'),
            new RpcInteraction(
                'add-dark-mode',
                'core/styles/%s/add-dark-mode',
                'wcf.acp.style.addDarkMode',
                InteractionConfirmationType::Custom,
                'wcf.acp.style.addDarkMode.question',
                static fn(Style $style) => !$style->hasDarkMode && $style->isTainted
            )
        ]);

        EventHandler::getInstance()->fire(
            new StyleInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Style::class;
    }
}
