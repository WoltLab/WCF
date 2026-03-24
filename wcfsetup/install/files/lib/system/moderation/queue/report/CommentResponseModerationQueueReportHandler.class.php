<?php

namespace wcf\system\moderation\queue\report;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentResponseModerationQueueHandler;

/**
 * An implementation of IModerationQueueReportHandler for comment responses.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentResponseModerationQueueReportHandler extends AbstractCommentResponseModerationQueueHandler implements
    IModerationQueueReportHandler
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.report';

    #[\Override]
    public function canReport(int $objectID)
    {
        if (!$this->isValid($objectID)) {
            return false;
        }

        $response = $this->getResponse($objectID);
        $comment = $this->getComment($response->commentID);
        if (!$this->getCommentManager($comment)->isAccessible($comment->objectID)) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getReportedContent(ViewableModerationQueue $queue)
    {
        return $this->getRelatedContent($queue);
    }

    #[\Override]
    public function getReportedObject(int $objectID)
    {
        return $this->getResponse($objectID);
    }
}
