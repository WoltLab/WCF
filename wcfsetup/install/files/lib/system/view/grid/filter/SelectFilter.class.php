<?php

namespace wcf\system\view\grid\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\WCF;

class SelectFilter implements IGridViewFilter
{
    public function __construct(private readonly array $options) {}

    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return SelectFormField::create($id)
            ->label($label)
            ->options($this->options);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $list->getConditionBuilder()->add("$id = ?", [$value]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return WCF::getLanguage()->get($this->options[$value]);
    }
}
