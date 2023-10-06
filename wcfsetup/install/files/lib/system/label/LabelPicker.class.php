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

    public readonly ViewableLabelGroup $labelGroup;

    private int $selected = 0;

    public function __construct(ViewableLabelGroup $labelGroup, bool $invertible)
    {
        $this->labelGroup = $labelGroup;
        $this->invertible = $invertible;
    }

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

    public function getSelectedValue(): int
    {
        return $this->selected;
    }

    public function hasSelection(): bool
    {
        return $this->selected !== 0;
    }

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
                    data-group-id="%d"
                    %s
                ></woltlab-core-label-picker>
            EOT,
            $this->selected,
            $this->getElementID(),
            $this->labelGroup->getTitle(),
            StringUtil::encodeHTML(JSON::encode($labels)),
            $this->labelGroup->groupID,
            $this->invertible ? 'invertible' : '',
        );
    }

    public function getElementID(): string
    {
        return "labelGroup{$this->labelGroup->groupID}";
    }
}
