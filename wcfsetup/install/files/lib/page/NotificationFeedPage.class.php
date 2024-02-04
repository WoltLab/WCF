<?php

namespace wcf\page;

use wcf\system\exception\IllegalLinkException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list of own user notifications in feed.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @deprecated 6.1 use `NotificationRssFeedPage` instead
 */
class NotificationFeedPage extends AbstractFeedPage
{
    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        $this->title = WCF::getLanguage()->get('wcf.user.menu.community.notification');

        $this->redirectToNewPage(NotificationRssFeedPage::class);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->items = new \ArrayIterator();

        $notifications = UserNotificationHandler::getInstance()->getNotifications(20);

        foreach ($notifications['notifications'] as $notification) {
            $this->items->append($notification['event']);
        }
    }
}
