<?php

namespace wcf\system\moderation\queue\activation;

use wcf\command\comment\PublishComment;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentCommentModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * An implementation of IModerationQueueActivationHandler for comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentCommentModerationQueueActivationHandler extends AbstractCommentCommentModerationQueueHandler implements
    IModerationQueueActivationHandler
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.activation';

    #[\Override]
    public function enableContent(ModerationQueue $queue)
    {
        if ($this->isValid($queue->objectID) && $this->getComment($queue->objectID)->isDisabled !== 0) {
            new PublishComment($this->getComment($queue->objectID))();

            ModerationQueueActivationManager::getInstance()->removeModeratedContent(
                'com.woltlab.wcf.comment.comment',
                [$queue->objectID]
            );
        }
    }

    #[\Override]
    public function getDisabledContent(ViewableModerationQueue $queue)
    {
        return $this->getRelatedContent($queue);
    }
}
