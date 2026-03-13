<?php

namespace wcf\system\object\filter;

use wcf\system\form\builder\field\SelectFormField;
use wcf\system\WCF;

final class ObjectFilterHandler
{
    /**
     * @param list<IObjectFilter> $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {}

    public function getFormFields()
    {
        $filters = [];
        foreach ($this->filters as $filter) {
            $filters[$filter->getIdentifier()] = $filter->getTitle();
        }

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $filters,
            static fn($a, $b) => $collator->compare($a, $b)
        );

        $selection = SelectFormField::create('filter')
            ->options($filters)
            ->required();
    }
}
