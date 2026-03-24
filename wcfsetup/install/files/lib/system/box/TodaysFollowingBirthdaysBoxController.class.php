<?php

namespace wcf\system\box;

use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Shows today's birthdays of users the active user is following.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class TodaysFollowingBirthdaysBoxController extends TodaysBirthdaysBoxController
{
    /**
     * @inheritDoc
     * @since       5.3
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.todaysFollowingBirthdays.condition';

    /**
     * @inheritDoc
     */
    protected $templateName = 'boxTodaysFollowingBirthdays';

    #[\Override]
    protected function filterUserIDs(&$userIDs)
    {
        $userIDs = \array_intersect($userIDs, UserProfileHandler::getInstance()->getFollowingUsers());
    }
}
