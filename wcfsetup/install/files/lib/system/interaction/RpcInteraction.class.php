<?php

namespace wcf\system\interaction;

use wcf\action\ApiAction;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ITitledObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an interaction that call an RPC endpoint.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class RpcInteraction extends AbstractInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $endpoint,
        protected readonly string|\Closure $languageItem,
        protected readonly InteractionConfirmationType $confirmationType = InteractionConfirmationType::None,
        protected readonly string|\Closure $confirmationMessage = '',
        ?\Closure $isAvailableCallback = null,
        protected readonly InteractionEffect $interactionEffect = InteractionEffect::ReloadItem,
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        $identifier = StringUtil::encodeHTML($this->getIdentifier());

        if (\is_string($this->languageItem)) {
            $label = WCF::getLanguage()->get($this->languageItem);
        } else {
            $label = ($this->languageItem)($object);
        }

        if (\is_string($this->confirmationMessage)) {
            $confirmationMessage = WCF::getLanguage()->getDynamicVariable($this->confirmationMessage);
        } else {
            $confirmationMessage = ($this->confirmationMessage)($object);
        }
        $confirmationMessage = StringUtil::encodeHTML($confirmationMessage);

        $endpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']) .
                \sprintf($this->endpoint, $object->getObjectID())
        );

        if ($object instanceof ITitledObject) {
            $objectName = StringUtil::encodeHTML($object->getTitle());
        } else {
            $objectName = '';

            if ($object instanceof DatabaseObjectDecorator) {
                $baseObject = $object->getDecoratedObject();

                if ($baseObject instanceof ITitledObject) {
                    $objectName = StringUtil::encodeHTML($baseObject->getTitle());
                }
            }
        }

        $additionalAttributes = '';
        foreach ($this->getAdditionalDataAttributes($object) as $name => $value) {
            $additionalAttributes .= \sprintf(
                ' %s="%s"',
                StringUtil::encodeHTML($name),
                StringUtil::encodeHTML($value)
            );
        }

        return <<<HTML
            <button
                type="button"
                data-interaction="{$identifier}"
                data-object-name="{$objectName}"
                data-endpoint="{$endpoint}"
                data-confirmation-type="{$this->confirmationType->toString()}"
                data-confirmation-message="{$confirmationMessage}"
                data-interaction-effect="{$this->interactionEffect->toString()}"{$additionalAttributes}
            >
                {$label}
            </button>
            HTML;
    }

    /**
     * Returns additional data attributes that are added to the rendered button.
     *
     * The returned names and values are HTML encoded by the caller.
     *
     * @return array<string, string>
     * @since 6.3
     */
    protected function getAdditionalDataAttributes(DatabaseObject $object): array
    {
        return [];
    }

    #[\Override]
    public function renderInitialization(string $containerId): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $containerId = StringUtil::encodeJS($containerId);

        return <<<HTML
            <script data-relocate="true">
                require(['WoltLabSuite/Core/Component/Interaction/Rpc'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
