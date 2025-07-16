<?php

namespace wcf\system\condition\provider;

use wcf\system\condition\type\IContextualConditionType;
use wcf\system\condition\type\request\ActivePageRequestConditionType;
use wcf\system\condition\type\request\DayOfWeekRequestConditionType;
use wcf\system\condition\type\request\NotDayOfWeekRequestConditionType;
use wcf\system\condition\type\request\NotOnPageRequestConditionType;
use wcf\system\condition\type\request\TimeRequestConditionType;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type RequestConditionType IContextualConditionType<mixed>
 * @extends AbstractConditionProvider<RequestConditionType>
 */
final class RequestConditionProvider extends AbstractConditionProvider
{
    public function __construct()
    {
        $this->addCondition(new TimeRequestConditionType());
        $this->addCondition(new ActivePageRequestConditionType());
        $this->addCondition(new NotOnPageRequestConditionType());
        $this->addCondition(new DayOfWeekRequestConditionType());
        $this->addCondition(new NotDayOfWeekRequestConditionType());
    }
}
