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
 * @package WoltLabSuite\Core\System\User\Notification\Object
 * @since   3.1
 *
 * @method  PaidSubscriptionUser    getDecoratedObject()
 * @mixin   PaidSubscriptionUser
 */
class PaidSubscriptionUserUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PaidSubscriptionUser::class;

    /**
     * @inheritDoc
     */
    public function getAuthorID()
    {
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getSubscription()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getURL()
    {
        return LinkHandler::getInstance()->getLink('PaidSubscriptionList', ['forceFrontend' => true]);
    }
}
