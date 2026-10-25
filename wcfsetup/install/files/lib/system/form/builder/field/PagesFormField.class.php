<?php

namespace wcf\system\form\builder\field;

use wcf\data\page\PageNodeTree;
use wcf\system\WCF;

/**
 * Implementation of a form field for selecting multiple pages.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PagesFormField extends MultipleSelectionFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_multiplePagesSelectionFormField';

    private ?string $visibleEverywhereFieldId = null;
    private string $invertedLabel = '';

    public function __construct()
    {
        $this
            ->label('wcf.acp.box.visibilityException.visible')
            ->invertedLabel('wcf.acp.box.visibilityException.hidden')
            ->options(new PageNodeTree()->getNodeList(), true)
            ->filterable();
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'pageIDs';
    }

    public function getVisibleEverywhereFieldId(): ?string
    {
        return $this->visibleEverywhereFieldId;
    }

    public function visibleEverywhereFieldId(?string $visibleEverywhereFieldId): self
    {
        $this->visibleEverywhereFieldId = $visibleEverywhereFieldId;

        return $this;
    }

    public function getInvertedLabel(): string
    {
        return $this->invertedLabel;
    }

    /**
     * @param array<string, mixed> $variables
    */
    public function invertedLabel(string $languageItem, array $variables = []): self
    {
        $this->invertedLabel = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);

        return $this;
    }
}
