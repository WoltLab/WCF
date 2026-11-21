<?php

namespace wcf\system\form\builder\field;

use wcf\data\page\PageNodeTree;
use wcf\system\WCF;
use wcf\util\HtmlString;

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
            ->choices(new PageNodeTree()->getNodeList(), true)
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
     * Sets the label that replaces the regular label while the field set with
     * `visibleEverywhereFieldId()` is enabled, and returns this field.
     *
     * The label is HTML. A string is a phrase name, resolved with `$variables`,
     * and the resolved phrase is trusted HTML; a string that is no phrase name
     * is used as is. An `HtmlString` is used as is and never resolved as a
     * phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString`
     */
    public function invertedLabel(HtmlString|string $languageItem, array $variables = []): self
    {
        if ($languageItem instanceof HtmlString) {
            if ($variables !== []) {
                throw new \InvalidArgumentException(
                    "Cannot use variables with an HTML string as inverted label of field '{$this->getId()}'."
                );
            }

            $this->invertedLabel = (string)$languageItem;
        } else {
            $this->invertedLabel = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
        }

        return $this;
    }
}
