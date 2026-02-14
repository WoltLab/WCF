<?php

namespace wcf\system\gridView;

use wcf\data\DatabaseObject;
use wcf\system\interaction\InteractionEffect;
use wcf\util\StringUtil;

/**
 * Represents a row link that opens a form builder dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class FormBuilderDialogGridViewRowLink extends AbstractGridViewRowLink
{
    public function __construct(
        private readonly string $identifier,
        private readonly string $endpoint,
        private readonly InteractionEffect $interactionEffect = InteractionEffect::ReloadItem,
        ?\Closure $isAvailableCallback = null,
    ) {
        parent::__construct($isAvailableCallback);
    }

    #[\Override]
    public function render(mixed $value, DatabaseObject $row, bool $isPrimaryColumn = false): string
    {
        $identifier = StringUtil::encodeHTML($this->identifier);
        $endpoint = StringUtil::encodeHTML(
            \sprintf($this->endpoint, $row->getObjectID())
        );
        $tabindex = $isPrimaryColumn ? '0' : '-1';

        return <<<HTML
            <button
                type="button"
                data-interaction="{$identifier}"
                data-endpoint="{$endpoint}"
                data-interaction-effect="{$this->interactionEffect->toString()}"
                class="gridView__rowLink"
                tabindex="{$tabindex}"
            >
                {$value}
            </button>
            HTML;
    }

    #[\Override]
    public function renderInitialization(string $containerId): ?string
    {
        $identifier = StringUtil::encodeJS($this->identifier);
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
