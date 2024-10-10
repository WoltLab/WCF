<?php

namespace wcf\system\view\grid\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;

interface IGridViewFilter
{
    public function getFormField(string $id, string $label): AbstractFormField;

    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void;

    public function renderValue(string $value): string;
}
