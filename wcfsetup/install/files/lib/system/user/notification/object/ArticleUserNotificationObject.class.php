<?php

namespace wcf\system\user\notification\object;

use wcf\data\article\Article;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents an article user notification object.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Article
 * @extends DatabaseObjectDecorator<Article>
 */
class ArticleUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
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
    public function getAuthorID()
    {
        return $this->getDecoratedObject()->userID;
    }
}
