<?php

namespace wcf\system\object\filter\builder;

use wcf\system\object\filter\IObjectFilter;

interface IObjectFilterBuilder
{
    /**
     * @return list<IObjectFilter<mixed>>
     */
    public function getFilters(): array;

    public function getObjectTypeName(): string;
}
