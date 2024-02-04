<?php

namespace wcf\page;

use wcf\system\exception\IllegalLinkException;
use wcf\system\rssFeed\RssFeed;
use wcf\system\rssFeed\RssFeedItem;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list of own user notifications in feed.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class NotificationRssFeedPage extends AbstractRssFeedPage
{
    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function getRssFeed(): RssFeed
    {
        $feed = new RssFeed();
        $channel = $this->getDefaultChannel();
        $channel->title(WCF::getLanguage()->get('wcf.user.menu.community.notification'));
        $feed->channel($channel);

        $notifications = UserNotificationHandler::getInstance()->getNotifications(20);
        if ($notifications['notifications'] !== []) {
            $channel->lastBuildDateFromTimestamp($notifications['notifications'][0]['time']);
        }

        foreach ($notifications['notifications'] as $notification) {
            $event = $notification['event'];
            \assert($event instanceof AbstractUserNotificationEvent);

            $item = new RssFeedItem();
            $item
                ->title($event->getTitle())
                ->link($event->getLink())
                ->description($event->getExcerpt())
                ->pubDateFromTimestamp($event->getTime())
                ->creator($event->getAuthor()->username)
                ->guid($event->getLink())
                ->contentEncoded($event->getFormattedMessage());
            $channel->item($item);
        }

        return $feed;
    }
}
