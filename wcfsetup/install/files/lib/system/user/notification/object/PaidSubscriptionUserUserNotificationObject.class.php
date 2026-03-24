<?php

namespace wcf\system\user\notification\object;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\system\request\LinkHandler;

/**
 * Represents a paid subscription user as a notification object.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @mixin   PaidSubscriptionUser
 * @extends DatabaseObjectDecorator<PaidSubscriptionUser>
 */
class PaidSubscriptionUserUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PaidSubscriptionUser::class;

    #[\Override]
    public function getAuthorID()
    {
        return 0;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getSubscription()->getTitle();
    }

    #[\Override]
    public function getURL()
    {
        return LinkHandler::getInstance()->getLink('PaidSubscriptionList', ['forceFrontend' => true]);
    }
}
