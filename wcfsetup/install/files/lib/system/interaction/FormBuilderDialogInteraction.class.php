<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an interaction that call a form builder action.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class FormBuilderDialogInteraction extends AbstractInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $endpoint,
        protected readonly string|\Closure $languageItem,
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

        $endpoint = StringUtil::encodeHTML(
            \sprintf($this->endpoint, $object->getObjectID())
        );

        return <<<HTML
            <button
                type="button"
                data-interaction="{$identifier}"
                data-endpoint="{$endpoint}"
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
                require(['WoltLabSuite/Core/Component/Interaction/FormBuilderDialog'], ({ setup }) => {
                    setup('{$identifier}', document.getElementById('{$containerId}'));
                });
            </script>
            HTML;
    }
}
