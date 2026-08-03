<?php

namespace wcf\data\article;

use wcf\data\like\Like;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Article
 * @extends AbstractLikeObject<Article>
 */
class LikeableArticle extends AbstractLikeObject implements IReactionObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Article::class;

    #[\Override]
    public function getTitle(): string
    {
        return $this->getDecoratedObject()->getTitle();
    }

    #[\Override]
    public function getURL()
    {
        return $this->getDecoratedObject()->getLink();
    }

    #[\Override]
    public function getUserID()
    {
        return $this->userID;
    }

    #[\Override]
    public function getObjectID()
    {
        return $this->articleID;
    }

    #[\Override]
    public function updateLikeCounter(int $cumulativeLikes)
    {
        ArticleBuilder::forUpdate($this->getDecoratedObject())
            ->incrementReactions($cumulativeLikes - $this->getDecoratedObject()->cumulativeLikes)
            ->update();
    }

    #[\Override]
    public function getLanguageID()
    {
        return null;
    }

    #[\Override]
    public function sendNotification(Like $like)
    {
        if ($this->getDecoratedObject()->userID !== WCF::getUser()->userID) {
            $notificationObject = new LikeUserNotificationObject($like);
            UserNotificationHandler::getInstance()->fireEvent(
                'like',
                'com.woltlab.wcf.likeableArticle.notification',
                $notificationObject,
                [$this->getDecoratedObject()->userID],
                ['objectID' => $this->getDecoratedObject()->articleID]
            );
        }
    }
}
