<?php

namespace wcf\system\object\filter\builder;

use Override;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;

final class UserGroupAssignmentObjectFilterBuilder implements IObjectFilterBuilder
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

    #[Override]
    public function getFilters(): array
    {
        return $this->filters;
    }
}
