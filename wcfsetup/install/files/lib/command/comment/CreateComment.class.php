<?php

namespace wcf\command\comment;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentBuilder;
use wcf\event\comment\CommentCreated;
use wcf\system\event\EventHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Creates a new comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CreateComment
{
    public function __construct(
        private readonly CommentBuilder $builder,
    ) {}

    public function __invoke(): Comment
    {
        $comment = $this->builder->create();

        if ($comment->isDisabled === 0) {
            new PublishComment($comment)();
        } else {
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.comment',
                $comment->commentID
            );
        }

        EventHandler::getInstance()->fire(new CommentCreated($comment, $this->builder));

        return $comment;
    }
}
