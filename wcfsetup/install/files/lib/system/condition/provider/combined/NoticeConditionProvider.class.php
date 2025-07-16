<?php

namespace wcf\system\condition\provider\combined;

use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\provider\RequestConditionProvider;
use wcf\system\condition\provider\UserConditionProvider;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-import-type RequestConditionType from RequestConditionProvider
 * @phpstan-import-type UserConditionType from UserConditionProvider
 * @extends AbstractConditionProvider<UserConditionType|RequestConditionType>
 */
final class NoticeConditionProvider extends AbstractConditionProvider
{
    public function __construct()
    {
        $this->withConditionsFrom(new UserConditionProvider());
        $this->withConditionsFrom(new RequestConditionProvider());
    }
}
