<?php

namespace wcf\system\label;

use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Groups a list of label pickers and provides helper methods to interact with them.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class LabelPickerGroup implements \Countable, \IteratorAggregate
{
    /**
     * @var LabelPicker[]
     */
    private readonly array $labelPickers;

    /**
     * Field name of the selected values.
     */
    private string $name = 'labelIDs';

    /**
     * @param LabelPicker[] $labelPickers
     */
    private function __construct(array $labelPickers)
    {
        $pickers = [];
        foreach ($labelPickers as $labelPicker) {
            // Skip label pickers without any attached labels.
            if (\count($labelPicker->labelGroup) === 0) {
                continue;
            }

            $pickers[$labelPicker->labelGroup->groupID] = $labelPicker;
        }

        $this->labelPickers = $pickers;
    }

    /**
     * Sets the selected label per label picker.
     *
     * @param int[] $labelIDs
     */
    public function setSelectedLabels(array $labelIDs): void
    {
        foreach ($this->labelPickers as $groupID => $labelPicker) {
            $labelID = $labelIDs[$groupID] ?? 0;
            $labelPicker->setSelectedValue($labelID);
        }
    }

    /**
     * Sets the selected label based on the assigned labels.
     *
     * @param Label[] $assignedLabels
     */
    public function setSelectedLabelsFromAssignedLabels(array $assignedLabels): void
    {
        // Reset all label pickers each because there may be no explicit value
        // set for one or more of them.
        foreach ($this->labelPickers as $labelPicker) {
            $labelPicker->setSelectedValue(0);
        }

        foreach ($assignedLabels as $label) {
            if (isset($this->labelPickers[$label->groupID])) {
                $this->labelPickers[$label->groupID]->setSelectedValue($label->labelID);
            }
        }
    }

    /**
     * Returns true if any label picker has a selection.
     */
    public function hasSelection(): bool
    {
        foreach ($this->labelPickers as $labelPicker) {
            if ($labelPicker->hasSelection()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an unencoded query string for `labelIDs` for use in the LinkHandler.
     */
    public function toUrlQueryString(): string
    {
        $mapping = \array_map(
            static fn (LabelPicker $labelPicker) => $labelPicker->getSelectedValue(),
            \array_filter(
                $this->labelPickers,
                static fn (LabelPicker $labelPicker) => $labelPicker->hasSelection()
            )
        );

        return \http_build_query([$this->name => $mapping], '', '&');
    }

    /**
     * Returns the list of selected label ids per label group id.
     *
     * @return int[]
     */
    public function toLabelIDs(): array
    {
        return \array_map(
            static fn (LabelPicker $labelPicker) => $labelPicker->getSelectedValue(),
            \array_filter(
                $this->labelPickers,
                static fn (LabelPicker $labelPicker) => $labelPicker->hasSelection()
            )
        );
    }

    /**
     * Applies the filter based on the selected label ids to the condition builder.
     */
    public function applyFilter(string $objectType, string $fieldName, PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.label.object',
            $objectType
        )->objectTypeID;

        foreach ($this->labelPickers as $labelPicker) {
            $selected = $labelPicker->getSelectedValue();
            if ($selected === 0) {
                continue;
            }

            if ($selected === -1) {
                $conditionBuilder->add(
                    \sprintf(
                        <<<'EOT'
                        %s NOT IN (
                            SELECT  objectID
                            FROM    wcf%d_label_object
                            WHERE   objectTypeID = ?
                                AND labelID IN (?)
                        )
                        EOT,
                        $fieldName,
                        \WCF_N,
                    ),
                    [
                        $objectTypeID,
                        $labelPicker->labelGroup->getLabelIDs(),
                    ]
                );
            } else {
                $conditionBuilder->add(
                    \sprintf(
                        <<<'EOT'
                        %s IN (
                            SELECT  objectID
                            FROM    wcf%d_label_object
                            WHERE   objectTypeID = ?
                                AND labelID = ?
                        )
                        EOT,
                        $fieldName,
                        \WCF_N,
                    ),
                    [
                        $objectTypeID,
                        $selected,
                    ]
                );
            }
        }
    }

    /**
     * Set the name of the hidden input field for the label ids.
     */
    public function setName(string $name): void
    {
        $this->name = $name;

        foreach ($this->labelPickers as $labelPicker) {
            $labelPicker->name = $name;
        }
    }

    public function count(): int
    {
        return \count($this->labelPickers);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->labelPickers);
    }

    /**
     * @param int[] $groupIDs
     */
    public static function fromGroupIDs(array $groupIDs, bool $invertible): self
    {
        $labelPickers = LabelHandler::getInstance()->getLabelPickers($groupIDs, $invertible);

        return new self($labelPickers);
    }

    /**
     * @param ViewableLabelGroup[] $viewableLabelGroups
     */
    public static function fromViewableLabelGroups(array $viewableLabelGroups, bool $invertible): self
    {
        $labelPickers = \array_map(
            static fn (ViewableLabelGroup $viewableLabelGroup) => new LabelPicker($viewableLabelGroup, $invertible),
            $viewableLabelGroups
        );

        return new self($labelPickers);
    }
}
