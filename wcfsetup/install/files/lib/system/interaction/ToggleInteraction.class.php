<?php

namespace wcf\system\interaction;

use wcf\action\ApiAction;
use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a toggle interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class ToggleInteraction extends AbstractInteraction
{
    public function __construct(
        string $identifier,
        private readonly string $enableEndpoint,
        private readonly string $disableEndpoint,
        private readonly string $propertyName = 'isDisabled',
        private readonly bool $propertyIsDisabledState = true,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());

        $enableEndpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']) .
                \sprintf($this->enableEndpoint, $object->getObjectID())
        );
        $disableEndpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']) .
                \sprintf($this->disableEndpoint, $object->getObjectID())
        );

        $ariaLabel = WCF::getLanguage()->get('wcf.global.button.enable');
        $checked = (!$object->{$this->propertyName} && $this->propertyIsDisabledState)
            || ($object->{$this->propertyName} && !$this->propertyIsDisabledState) ? 'checked' : '';

        return <<<HTML
            <woltlab-core-toggle-button
                data-interaction="{$identifier}"
                aria-label="{$ariaLabel}"
                data-enable-endpoint="{$enableEndpoint}"
                data-disable-endpoint="{$disableEndpoint}"
                {$checked}
            ></woltlab-core-toggle-button>
            HTML;
    }

    #[\Override]
    public function renderInitialization(string $containerId): ?string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $containerId = StringUtil::encodeJS($containerId);

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Component/Interaction/Toggle'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
