<?php

namespace wcf\page;

use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list with outstanding notifications of the active user.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NotificationListPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * list of outstanding notifications
     * @var mixed[][]
     */
    public $notifications = [];

    /**
     * @inheritDoc
     */
    public function countItems()
    {
        return UserNotificationHandler::getInstance()->countAllNotifications();
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->notifications = UserNotificationHandler::getInstance()->getNotifications(
            $this->sqlLimit,
            $this->sqlOffset,
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'notifications' => $this->notifications,
        ]);
    }
}
