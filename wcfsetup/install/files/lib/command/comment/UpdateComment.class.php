<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\event\comment\CommentUpdated;
use wcf\system\event\EventHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Updates a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UpdateComment
{
    public function __construct(
        private readonly Comment $comment,
        private readonly HtmlInputProcessor $htmlInputProcessor,
    ) {
    }

    public function __invoke(): void
    {
        $data = [
            'message' => $this->htmlInputProcessor->getHtml(),
        ];

        $this->htmlInputProcessor->setObjectID($this->comment->getObjectID());
        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor);
        if ($this->comment->hasEmbeddedObjects != $hasEmbeddedObjects) {
            $data['hasEmbeddedObjects'] = $this->comment->hasEmbeddedObjects ? 0 : 1;
        }

        $action = new CommentAction([$this->comment], 'update', [
            'data' => $data,
        ]);
        $action->executeAction();

        $event = new CommentUpdated(new Comment($this->comment->commentID));
        EventHandler::getInstance()->fire($event);
    }
}
