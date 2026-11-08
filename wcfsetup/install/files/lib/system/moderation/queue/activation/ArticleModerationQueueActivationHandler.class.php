<?php

namespace wcf\system\moderation\queue\activation;

use wcf\command\article\PublishArticle;
use wcf\command\article\SoftDeleteArticle;
use wcf\data\article\Article;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\cache\runtime\ArticleRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueActivationHandler for articles submitted by
 * users who are not allowed to publish them on their own.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleModerationQueueActivationHandler extends AbstractModerationQueueHandler implements
    IModerationQueueActivationHandler
{
    /**
     * @inheritDoc
     */
    protected $className = Article::class;

    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.activation';

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.article';

    #[\Override]
    public function enableContent(ModerationQueue $queue): void
    {
        $article = $this->getArticle($queue->objectID);
        if ($article !== null && $article->publicationStatus === Article::UNPUBLISHED) {
            new PublishArticle($article)();
        }
    }

    #[\Override]
    public function getDisabledContent(ViewableModerationQueue $queue): string
    {
        return WCF::getTPL()->render('wcf', 'moderationArticle', [
            // @phpstan-ignore property.notFound
            'article' => $this->getArticle($queue->getAffectedObject()->articleID),
        ]);
    }

    public function getArticle(int $articleID): ?Article
    {
        return ArticleRuntimeCache::getInstance()->getObject($articleID);
    }

    #[\Override]
    public function assignQueues(array $queues): void
    {
        $assignments = $orphanedQueueIDs = [];

        foreach ($queues as $queue) {
            ArticleRuntimeCache::getInstance()->cacheObjectID($queue->objectID);
        }

        foreach ($queues as $queue) {
            $article = ArticleRuntimeCache::getInstance()->getObject($queue->objectID);

            if ($article === null) {
                $orphanedQueueIDs[] = $queue->queueID;
            } else {
                $assignments[$queue->queueID] = $article->canPublish();
            }
        }

        ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
        ModerationQueueManager::getInstance()->setAssignment($assignments);
    }

    #[\Override]
    public function getContainerID(int $objectID): int
    {
        if ($this->isValid($objectID)) {
            return $this->getArticle($objectID)->getCategory()->categoryID;
        }

        return 0;
    }

    #[\Override]
    public function isValid(int $objectID): bool
    {
        return $this->getArticle($objectID) !== null;
    }

    #[\Override]
    public function populate(array $queues): void
    {
        foreach ($queues as $queue) {
            ArticleRuntimeCache::getInstance()->cacheObjectID($queue->objectID);
        }

        foreach ($queues as $object) {
            $article = ArticleRuntimeCache::getInstance()->getObject($object->objectID);
            if ($article !== null) {
                $object->setAffectedObject($article);
            } else {
                $object->setIsOrphaned();
            }
        }
    }

    #[\Override]
    public function canRemoveContent(ModerationQueue $queue): bool
    {
        if ($this->isValid($queue->objectID)) {
            return $this->getArticle($queue->objectID)->canDelete();
        }

        return false;
    }

    #[\Override]
    public function removeContent(ModerationQueue $queue, string $message): void
    {
        if ($this->isValid($queue->objectID)) {
            new SoftDeleteArticle($this->getArticle($queue->objectID))();
        }
    }

    #[\Override]
    public function isAffectedUser(ModerationQueue $queue, int $userID): bool
    {
        if (!parent::isAffectedUser($queue, $userID)) {
            return false;
        }

        $article = $this->getArticle($queue->objectID);
        if ($article === null) {
            return false;
        }

        /** @see Article::canPublish() */
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if ($userProfile->hasPermission('admin.content.article.canManageArticle')) {
            return true;
        }

        return $userProfile->hasPermission('admin.content.article.canManageOwnArticles')
            && $article->userID === $userProfile->userID;
    }
}
