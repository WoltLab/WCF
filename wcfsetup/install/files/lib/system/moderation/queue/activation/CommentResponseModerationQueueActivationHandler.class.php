<?php

namespace wcf\system\moderation\queue\activation;

use wcf\command\comment\response\PublishResponse;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentResponseModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * An implementation of IModerationQueueReportHandler for comment responses.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentResponseModerationQueueActivationHandler extends AbstractCommentResponseModerationQueueHandler implements
    IModerationQueueActivationHandler
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.activation';

    #[\Override]
    public function enableContent(ModerationQueue $queue)
    {
        if ($this->isValid($queue->objectID) && $this->getResponse($queue->objectID)->isDisabled !== 0) {
            new PublishResponse($this->getResponse($queue->objectID))();

            ModerationQueueActivationManager::getInstance()->removeModeratedContent(
                'com.woltlab.wcf.comment.response',
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
