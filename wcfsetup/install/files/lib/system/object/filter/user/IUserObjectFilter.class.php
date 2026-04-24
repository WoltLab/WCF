<?php

namespace wcf\system\object\filter\user;

use wcf\data\user\User;
use wcf\system\object\filter\IObjectFilter;

/**
 * @template TValueType of mixed
 * @extends IObjectFilter<TValueType>
 */
interface IUserObjectFilter extends IObjectFilter
{
    /**
     * @param TValueType $configuredValue
     */
    public function testUser(User $user, mixed $configuredValue): bool;
}
