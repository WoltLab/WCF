<?php

namespace wcf\system\interaction\bulk;

use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Represents a bulk interaction that calls a form builder action.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkFormBuilderDialogInteraction extends AbstractBulkInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $controller,
        protected readonly string | \Closure $languageItem,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(array $objects): string
    {
        $identifier = StringUtil::encodeJS($this->getIdentifier());
        $label = WCF::getLanguage()->get($this->languageItem);
        $objectIDs = \array_values(\array_map(fn(DatabaseObject $object) => $object->getObjectID(), $objects));
        $endpoint = StringUtil::encodeHTML(
            LinkHandler::getInstance()->getControllerLink($this->controller, [
                'ids' => $objectIDs
            ])
        );

        $jsonObjectIDs = StringUtil::encodeHTML(
            JSON::encode($objectIDs)
        );

        return <<<HTML
            <button
                type="button"
                data-bulk-interaction="{$identifier}"
                data-endpoint="{$endpoint}"
                data-object-ids="{$jsonObjectIDs}"
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
                require(['WoltLabSuite/Core/Component/Interaction/Bulk/FormBuilderDialog'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
