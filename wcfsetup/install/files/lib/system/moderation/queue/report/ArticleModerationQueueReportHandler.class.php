<?php

namespace wcf\system\moderation\queue\report;

use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\ViewableArticle;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class ArticleModerationQueueReportHandler extends AbstractModerationQueueHandler implements
    IModerationQueueReportHandler
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.report';

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.article';

    /**
     * @inheritDoc
     */
    public function canReport($objectID)
    {
        if (!$this->isValid($objectID)) {
            return false;
        }

        if (!$this->getReportedObject($objectID)->canRead()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getReportedContent(ViewableModerationQueue $queue)
    {
        WCF::getTPL()->assign([
            'article' => $this->getArticle($queue->getAffectedObject()->articleID),
        ]);

        return WCF::getTPL()->fetch('moderationArticle');
    }

    /**
     * @inheritDoc
     *
     * @return      ViewableArticle|null
     */
    public function getReportedObject($objectID)
    {
        if ($this->isValid($objectID)) {
            return $this->getArticle($objectID);
        }

        return null;
    }

    /**
     * @param $articleID
     * @return ViewableArticle|null
     */
    public function getArticle($articleID)
    {
        return ViewableArticleRuntimeCache::getInstance()->getObject($articleID);
    }

    /**
     * @inheritDoc
     */
    public function assignQueues(array $queues)
    {
        $assignments = $orphanedQueueIDs = [];

        // first cache all articles
        foreach ($queues as $queue) {
            ViewableArticleRuntimeCache::getInstance()->cacheObjectID($queue->objectID);
        }

        // now process articles
        foreach ($queues as $queue) {
            $article = ViewableArticleRuntimeCache::getInstance()->getObject($queue->objectID);

            if ($article === null) {
                $orphanedQueueIDs[] = $queue->queueID;
            } else {
                if ($article->canDelete()) {
                    $assignments[$queue->queueID] = true;
                } else {
                    $assignments[$queue->queueID] = false;
                }
            }
        }

        ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
        ModerationQueueManager::getInstance()->setAssignment($assignments);
    }

    /**
     * @inheritDoc
     */
    public function getContainerID($objectID)
    {
        if ($this->isValid($objectID)) {
            return $this->getArticle($objectID)->getCategory()->categoryID;
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        if ($this->getArticle($objectID) === null) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function populate(array $queues)
    {
        // first cache all articles
        foreach ($queues as $queue) {
            ViewableArticleRuntimeCache::getInstance()->cacheObjectID($queue->objectID);
        }

        foreach ($queues as $object) {
            $article = ViewableArticleRuntimeCache::getInstance()->getObject($object->objectID);
            if ($article !== null) {
                $object->setAffectedObject($article->getDecoratedObject());
            } else {
                $object->setIsOrphaned();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function canRemoveContent(ModerationQueue $queue)
    {
        if ($this->isValid($queue->objectID)) {
            return $this->getArticle($queue->objectID)->canDelete();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeContent(ModerationQueue $queue, $message)
    {
        if ($this->isValid($queue->objectID)) {
            (new ArticleAction([$this->getArticle($queue->objectID)->getDecoratedObject()], 'trash'))->executeAction();
        }
    }

    #[\Override]
    public function isAffectedUser(ModerationQueue $queue, $userID)
    {
        if (!parent::isAffectedUser($queue, $userID)) {
            return false;
        }
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
        $article = $this->getArticle($queue->objectID);
        if ($article === null) {
            return false;
        }
        /** @see Article::canDelete() */
        if ($userProfile->getPermission('admin.content.article.canManageArticle')) {
            return true;
        }
        return $userProfile->getPermission('admin.content.article.canManageOwnArticles')
            && $article->userID == $userProfile->userID;
    }
}
