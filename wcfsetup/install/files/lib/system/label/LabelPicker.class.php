<?php

namespace wcf\system\label;

use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Provides helper methods to interact with the label group.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class LabelPicker
{
    /**
     * Controls the availability of the inverted selection, allowing to filter
     * for items that are not assigned any label of a label group.
     */
    public readonly bool $invertible;

    /**
     * Name of the hidden input field.
     */
    public string $name = 'labelIDs';

    public readonly ViewableLabelGroup $labelGroup;

    private string $elementID;

    private int $selected = 0;

    public function __construct(ViewableLabelGroup $labelGroup, bool $invertible)
    {
        $this->labelGroup = $labelGroup;
        $this->invertible = $invertible;
    }

    /**
     * Sets the selected label of this label picker by providing its id. The
     * value `0` indicates that no selection is to be made and  `-1` inverts the
     * selection of the labels.
     */
    public function setSelectedValue(int $selected): void
    {
        if ($selected === 0) {
            $this->selected = 0;
        } else if ($selected === -1) {
            $this->selected = $this->invertible ? -1 : 0;
        } else {
            $this->selected = $this->labelGroup->isValid($selected) ? $selected : 0;
        }
    }

    /**
     * The returned value can be `0` to indicate that no selection has been
     * made, `-1` to indicate that the selection should be inverted or the id
     * of the selected label.
     */
    public function getSelectedValue(): int
    {
        return $this->selected;
    }

    /**
     * Returns true if a label has been selected or if the selection has been
     * inverted.
     */
    public function hasSelection(): bool
    {
        return $this->selected !== 0;
    }

    /**
     * Generates the HTML element for the label picker.
     */
    public function toHtml(): string
    {
        $labels = [];
        foreach ($this->labelGroup as $label) {
            \assert($label instanceof Label);

            $labels[] = [$label->labelID, $label->render()];
        }

        return \sprintf(
            <<<'EOT'
                <woltlab-core-label-picker
                    selected="%d"
                    id="%s"
                    title="%s"
                    labels="%s"
                    name="%s"
                    data-group-id="%d"
                    %s
                ></woltlab-core-label-picker>
            EOT,
            $this->selected,
            $this->getElementID(),
            StringUtil::encodeHTML($this->labelGroup->getTitle()),
            StringUtil::encodeHTML(JSON::encode($labels)),
            StringUtil::encodeHTML($this->name),
            $this->labelGroup->groupID,
            $this->invertible ? 'invertible' : '',
        );
    }

    /**
     * Returns the unique element id of this label picker.
     */
    public function getElementID(): string
    {
        if (!isset($this->elementID)) {
            $this->elementID = \sprintf(
                '%s_labelGroup%d',
                \substr(\md5($this->name), 0, 8),
                $this->labelGroup->groupID,
            );
        }

        return $this->elementID;
    }

    /**
     * Sets the unique element id of this label picker. Must be set before
     * attempting to read the element id which is implicitly done by calling
     * `toHtml()`.
     */
    public function setElementID(string $elementID): void
    {
        if (isset($this->elementID)) {
            throw new \RuntimeException("Cannot set the element id, already set.");
        }

        $this->elementID = $elementID;
    }
}
