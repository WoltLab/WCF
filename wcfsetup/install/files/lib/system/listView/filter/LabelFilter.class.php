<?php

namespace wcf\system\listView\filter;

use wcf\data\DatabaseObjectList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\label\LabelFormField;

/**
 * Filter that allows to filter a list view by labels.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class LabelFilter extends AbstractFilter
{
    public function __construct(
        private readonly ViewableLabelGroup $labelGroup,
        private readonly int $objectTypeID,
        string $id,
        string $databaseColumn = ''
    ) {
        parent::__construct($id, '', $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return LabelFormField::create($this->id)
            ->objectProperty('labelIDs')
            ->labelGroup($this->labelGroup);
    }

    #[\Override]
    public function getLabel(): string
    {
        return $this->labelGroup->getTitle();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $label = $this->labelGroup->getLabel((int)$value);
        if ($label === null) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        $list->getConditionBuilder()->add(
            "{$list->getDatabaseTableAlias()}.{$list->getDatabaseTableIndexName()} IN (
                SELECT  objectID
                FROM    wcf1_label_object
                WHERE   objectTypeID = ?
                    AND labelID = ?
            )",
            [
                $this->objectTypeID,
                $label->labelID,
            ]
        );
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return $this->labelGroup->getLabel((int)$value)->getTitle();
    }
}
