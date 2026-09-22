<?php

namespace wcf\system\interaction\bulk;

use wcf\action\ApiAction;
use wcf\data\DatabaseObject;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a bulk interaction that calls an RPC endpoint.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkRpcInteraction extends AbstractBulkInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $endpoint,
        protected readonly string $languageItem,
        protected readonly InteractionConfirmationType $confirmationType = InteractionConfirmationType::None,
        protected readonly string $confirmationMessage = '',
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(array $objects): string
    {
        $identifier = StringUtil::encodeHTML($this->getIdentifier());
        $dataLabel = WCF::getLanguage()->get($this->languageItem);
        $label = WCF::getLanguage()->get($this->languageItem);
        $confirmationMessage = WCF::getLanguage()->getDynamicVariable($this->confirmationMessage);
        $endpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']) . $this->endpoint
        );
        $objectIDs = StringUtil::encodeHTML(
            \json_encode(
                \array_values(\array_map(fn(DatabaseObject $object) => $object->getObjectID(), $objects)),
                \JSON_THROW_ON_ERROR
            )
        );

        $additionalAttributes = '';
        foreach ($this->getAdditionalDataAttributes($objects) as $name => $value) {
            $additionalAttributes .= \sprintf(
                ' %s="%s"',
                StringUtil::encodeHTML($name),
                StringUtil::encodeHTML($value)
            );
        }

        return <<<HTML
            <button
                type="button"
                data-bulk-interaction="{$identifier}"
                data-endpoint="{$endpoint}"
                data-object-ids="{$objectIDs}"
                data-confirmation-type="{$this->confirmationType->toString()}"
                data-confirmation-message="{$confirmationMessage}"
                data-label="{$dataLabel}"{$additionalAttributes}
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
     * @param DatabaseObject[] $objects
     * @return array<string, string>
     * @since 6.3
     */
    protected function getAdditionalDataAttributes(array $objects): array
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
                require(['WoltLabSuite/Core/Component/Interaction/Bulk/Rpc'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
