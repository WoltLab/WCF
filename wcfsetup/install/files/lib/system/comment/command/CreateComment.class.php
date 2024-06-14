<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\CommentEditor;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\event\comment\CommentCreated;
use wcf\system\event\EventHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Creates a new comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CreateComment
{
    public function __construct(
        private readonly ObjectType $objectType,
        private readonly int $objectID,
        private readonly HtmlInputProcessor $htmlInputProcessor,
        private readonly ?User $user = null,
        private readonly string $username = '',
        private readonly bool $isDisabled = false,
    ) {
    }

    public function __invoke(): Comment
    {
        $action = new CommentAction([], 'create', [
            'data' => [
                'objectTypeID' => $this->objectType->objectTypeID,
                'objectID' => $this->objectID,
                'time' => TIME_NOW,
                'userID' => $this->user ? $this->user->userID : null,
                'username' => $this->user ? $this->user->username : $this->username,
                'message' => $this->htmlInputProcessor->getHtml(),
                'responses' => 0,
                'responseIDs' => \serialize([]),
                'enableHtml' => 1,
                'isDisabled' => $this->isDisabled ? 1 : 0,
            ]
        ]);
        /** @var Comment $comment */
        $comment = $action->executeAction()['returnValues'];

        if (!$comment->isDisabled) {
            (new PublishComment($comment))();
        } else {
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.comment',
                $comment->commentID
            );
        }

        $this->htmlInputProcessor->setObjectID($comment->getObjectID());
        if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor)) {
            (new CommentEditor($comment))->update([
                'hasEmbeddedObjects' => 1,
            ]);
            $comment = new Comment($comment->getObjectID());
        }

        $event = new CommentCreated($comment);
        EventHandler::getInstance()->fire($event);

        return $comment;
    }
}
