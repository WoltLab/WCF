<?php

namespace wcf\system\object\filter\builder;

use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;

final class UserGroupAssignmentObjectFilterBuilder
{
    /**
     * @var list<IObjectFilter<mixed>>
     */
    private readonly array $filters;

    public function __construct()
    {
        $this->filters = [
            new UserAvatarObjectFilter(),
            new UserLanguageObjectFilter(),
        ];
    }

    /**
     * @return list<IObjectFilter<mixed>>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
