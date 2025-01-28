<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an interaction that executes a dbo action.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 * @deprecated  6.2 DBO actions are considered outdated and should be migrated to RPC endpoints.
 */
class LegacyDboInteraction extends AbstractInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $className,
        protected readonly string $actionName,
        protected readonly string|\Closure $languageItem,
        protected readonly InteractionConfirmationType $confirmationType = InteractionConfirmationType::None,
        protected readonly string|\Closure $confirmationMessage = '',
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());

        if (\is_string($this->languageItem)) {
            $label = WCF::getLanguage()->get($this->languageItem);
        } else {
            $label = ($this->languageItem)($object);
        }

        if (\is_string($this->confirmationMessage)) {
            $confirmationMessage = WCF::getLanguage()->get($this->confirmationMessage);
        } else {
            $confirmationMessage = ($this->confirmationMessage)($object);
        }

        if ($object instanceof ITitledObject) {
            $objectName = StringUtil::encodeHTML($object->getTitle());
        } else {
            $objectName = '';
        }

        $className = StringUtil::encodeHTML($this->className);
        $actionName = StringUtil::encodeHTML($this->actionName);

        return <<<HTML
            <button
                type="button"
                data-interaction="{$identifier}"
                data-object-name="{$objectName}"
                data-class-name="{$className}"
                data-action-name="{$actionName}"
                data-confirmation-type="{$this->confirmationType->toString()}"
                data-confirmation-message="{$confirmationMessage}"
            >
                {$label}
            </button>
            HTML;
    }

    #[\Override]
    public function renderInitialization(string $containerId): ?string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $containerId = StringUtil::encodeJS($containerId);

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Component/Interaction/LegacyDboAction'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
