<?php

namespace wcf\system\view\grid\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TextFormField;

class TextFilter implements IGridViewFilter
{
    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return TextFormField::create($id)
            ->label($label);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $list->getConditionBuilder()->add("$id LIKE ?", ['%' . $value . '%']);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return $value;
    }
}
